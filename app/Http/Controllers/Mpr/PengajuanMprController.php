<?php

namespace App\Http\Controllers\Mpr;

use App\Http\Controllers\Controller;
use App\Models\Mpr\PengajuanMpr;
use App\Models\Mpr\PengajuanMprDetail;
use App\Models\User\User;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PengajuanMprController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $riwayatMpr = PengajuanMpr::with('items')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('mpr.mprriwayat', compact('riwayatMpr'));
    }

    public function create()
    {
        $user = Auth::user();
        if (!$user->isAccountComplete()) {
            return redirect()->route('dashboard')->with('error', 'Akses Ditolak: Anda wajib melengkapi verifikasi email, nomor WhatsApp, biometrik wajah, tanda tangan digital (TTD), dan jadwal kerja sebelum dapat membuat pengajuan.');
        }

        return view('mpr.mprcreate');
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user->isAccountComplete()) {
            return redirect()->route('dashboard')->with('error', 'Akses Ditolak: Anda wajib melengkapi verifikasi email, nomor WhatsApp, biometrik wajah, tanda tangan digital (TTD), dan jadwal kerja sebelum dapat membuat pengajuan.');
        }

        $request->validate([
            'keperluan_urgensi' => 'required|string',
            'dokumen_pendukung' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'items' => 'required|array|min:1',
            'items.*.nama_barang' => 'required|string',
            'items.*.jumlah' => 'required|numeric|min:1',
            'items.*.satuan' => 'required|string',
        ]);

        $user = Auth::user();

        $namaDokumen = null;
        if ($request->hasFile('dokumen_pendukung')) {
            $namaDokumen = $request->file('dokumen_pendukung')->store('dokumen_mpr', 'public');
        }

        $user = Auth::user();
        $isTopLevel = $user->isTopLevel();

        // Cari rule MPR dari seluruh roles yang dimiliki user
        $mprRules = [];
        $rules = [];
        foreach ($user->roles as $r) {
            if (!empty($r->approval_rules['mpr'])) {
                $mprRules = $r->approval_rules['mpr'];
                $rules = $r->approval_rules;
                break;
            }
        }
        if (empty($mprRules) && $user->role) {
            $rules = $user->role->approval_rules ?? [];
            $mprRules = $rules['mpr'] ?? [];
        }

        $levels = (int) ($mprRules['levels'] ?? ($rules['approval_levels'] ?? 1));
        $approver1RoleId = $mprRules['approver_1_role_id'] ?? ($rules['approver_level_1_role_id'] ?? null);
        $approver2RoleId = $mprRules['approver_2_role_id'] ?? ($rules['approver_level_2_role_id'] ?? null);

        if (empty($approver1RoleId) && $isTopLevel) {
            // Top Level tanpa approver otomatis approved
            $statusTahap1 = 'approved';
            $statusTahap2 = 'not_required';
            $statusAkhir  = 'approved';
        } elseif ($levels === 2 && !empty($approver2RoleId)) {
            // Alur 2 Step Berjenjang
            $statusTahap1 = 'pending';
            $statusTahap2 = 'pending';
            $statusAkhir  = 'pending';
        } else {
            // Alur 1 Step
            $statusTahap1 = 'pending';
            $statusTahap2 = 'not_required';
            $statusAkhir  = 'pending';
        }

        // Format nomor MPR: MPR/YYYY/MM/XXX
        $now = Carbon::now();
        $urutan = PengajuanMpr::whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month)
            ->count() + 1;

        $nomorMpr = 'MPR/' . $now->format('Y/m/') . sprintf('%03d', $urutan);

        DB::beginTransaction();
        try {
            $mpr = PengajuanMpr::create([
                'user_id'           => $user->id,
                'nomor_mpr'         => $nomorMpr,
                'tanggal_pengajuan' => $now->format('Y-m-d'),
                'keperluan_urgensi' => $request->keperluan_urgensi,
                'dokumen_pendukung' => $namaDokumen,
                'status_tahap_1'    => $statusTahap1,
                'approver_tahap_1_id' => $statusTahap1 === 'approved' ? $user->id : null,
                'status_tahap_2'    => $statusTahap2,
                'approver_tahap_2_id' => $statusTahap2 === 'approved' ? $user->id : null,
                'status_akhir'      => $statusAkhir,
            ]);

            foreach ($request->items as $item) {
                PengajuanMprDetail::create([
                    'pengajuan_mpr_id' => $mpr->id,
                    'nama_barang'      => $item['nama_barang'],
                    'jumlah'           => $item['jumlah'],
                    'satuan'           => $item['satuan'],
                    'estimasi_harga'   => $item['estimasi_harga'] ?? 0,
                    'keterangan_item'  => $item['keterangan_item'] ?? null,
                ]);
            }

            DB::commit();

            // KIRIM NOTIFIKASI INSTAN WHATSAPP KE ATASAN TAHAP 1
            if ($statusTahap1 === 'pending' && !empty($approver1RoleId)) {
                try {
                    $approvers = User::whereHas('roles', fn($q) => $q->where('roles.id', $approver1RoleId))
                        ->where('id', '!=', $user->id)
                        ->whereNotNull('phone_verified_at')
                        ->get();

                    $waService = app(WhatsAppService::class);
                    foreach ($approvers as $approver) {
                        $waService->sendNewSubmissionNotification('mpr', $mpr, $approver, 1);
                    }
                } catch (\Exception $waEx) {
                    Log::error('Gagal mengirim notifikasi WA MPR baru: ' . $waEx->getMessage());
                }
            }

            return redirect()->route('mpr.riwayat')->with('success', 'Pengajuan MPR berhasil dikirim!');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Gagal menyimpan pengajuan MPR: '.$e->getMessage()])->withInput();
        }
    }
}
