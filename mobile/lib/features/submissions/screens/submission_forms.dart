import 'package:dio/dio.dart';
import 'package:file_picker/file_picker.dart';
import 'package:material_ui/material_ui.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import '../../../core/models/app_models.dart';
import '../../../core/network/api_client.dart';
import '../../../core/providers/auth_provider.dart';
import '../../../core/theme/app_theme.dart';
import '../../common/widgets/ui_kit.dart';

class CreateCutiScreen extends StatefulWidget {
  const CreateCutiScreen({super.key});

  @override
  State<CreateCutiScreen> createState() => _CreateCutiScreenState();
}

class _CreateCutiScreenState extends State<CreateCutiScreen> {
  List<JenisCutiModel> _jenisList = [];
  JenisCutiModel? _selectedJenis;
  SubCutiModel? _selectedSub;
  DateTime _mulai = DateTime.now().add(const Duration(days: 1));
  DateTime _selesai = DateTime.now().add(const Duration(days: 1));
  final _alasanCtrl = TextEditingController();
  PlatformFile? _dokumen;
  bool _loadingJenis = true;
  bool _submitting = false;

  // Metadata dari backend agar tampilan & kalkulasi setara Web
  int _kuotaAwal = 12;
  int _sisaSaldo = 0;
  int _cutiTerpakai = 0;
  int _pendingHari = 0;
  int _sisaCutiHaid = 0;
  int _levels = 1;
  bool _isTopLevel = false;
  String _approver1Name = 'Atasan Langsung';
  String? _approver2Name;
  String _scheduleType = 'normal';
  String _scheduleLabel = 'Normal (Senin - Jumat)';
  Map<String, String> _holidays = {};
  Set<String> _rosterOffDates = {};

  @override
  void initState() {
    super.initState();
    _alasanCtrl.addListener(() {
      if (mounted) setState(() {});
    });
    _loadJenis();
  }

  @override
  void dispose() {
    _alasanCtrl.dispose();
    super.dispose();
  }

  bool _isFemaleOnlyName(String name) {
    final lower = name.toLowerCase();
    return lower.contains('haid') ||
        lower.contains('bersalin') ||
        lower.contains('gugur') ||
        (lower.contains('melahirkan') && !lower.contains('istri'));
  }

  List<JenisCutiModel> _applyGenderFilter(List<JenisCutiModel> raw, bool isPerempuan) {
    return raw
        .where((j) => isPerempuan || !_isFemaleOnlyName(j.nameCuti))
        .map((j) {
          final subs = j.subCutis.where((s) {
            final sName = s.nameCuti.toLowerCase();
            if (!isPerempuan) return !_isFemaleOnlyName(sName);
            return !sName.contains('istri melahirkan');
          }).toList();
          return JenisCutiModel(
            id: j.id,
            kodeCuti: j.kodeCuti,
            nameCuti: j.nameCuti,
            kuotaDefault: j.kuotaDefault,
            wajibDokumen: j.wajibDokumen,
            butuhSuratDokter: j.butuhSuratDokter,
            keteranganOpsional: j.keteranganOpsional,
            subCutis: subs,
          );
        })
        .toList();
  }

  DateTime _nextActiveWorkDay(DateTime start) {
    var curr = DateTime(start.year, start.month, start.day);
    for (var i = 0; i < 14; i++) {
      if (_holidayReason(curr) == null) return curr;
      curr = curr.add(const Duration(days: 1));
    }
    return start;
  }

  Future<void> _loadJenis() async {
    final user = context.read<AuthProvider>().user;
    final isPerempuan = user?.isPerempuan ?? false;

    try {
      final res = await context.read<ApiClient>().get('/cuti/jenis');
      final rawList = (res['data'] as List? ?? [])
          .whereType<Map<String, dynamic>>()
          .map(JenisCutiModel.fromJson)
          .toList();
      final filtered = _applyGenderFilter(rawList, isPerempuan);

      final meta = (res['meta'] as Map<String, dynamic>?) ?? {};
      final leaveSummary = (meta['leave_summary'] as Map<String, dynamic>?) ?? {};
      final approvalRoute = (meta['approval_route'] as Map<String, dynamic>?) ?? {};
      final rawHolidays = meta['holidays'];
      final holidayMap = <String, String>{};
      if (rawHolidays is Map) {
        rawHolidays.forEach((k, v) {
          holidayMap[k.toString()] = v?.toString() ?? 'Hari Libur Nasional';
        });
      }
      final rosterOff = ((meta['roster_off_dates'] as List?) ?? [])
          .map((e) => e.toString())
          .toSet();

      if (!mounted) return;
      setState(() {
        _jenisList = filtered;
        if (filtered.isNotEmpty) {
          _selectedJenis = filtered.first;
          _selectedSub = filtered.first.subCutis.isNotEmpty ? filtered.first.subCutis.first : null;
        }
        _kuotaAwal = (leaveSummary['kuota_awal'] as num?)?.toInt() ?? 12;
        _sisaSaldo = (leaveSummary['sisa_saldo'] as num?)?.toInt() ?? 0;
        _cutiTerpakai = (leaveSummary['cuti_terpakai'] as num?)?.toInt() ?? 0;
        _pendingHari = (leaveSummary['pending_hari'] as num?)?.toInt() ?? 0;
        _sisaCutiHaid = (leaveSummary['sisa_cuti_haid'] as num?)?.toInt() ?? (isPerempuan ? 2 : 0);
        _levels = (approvalRoute['levels'] as num?)?.toInt() ?? 1;
        _isTopLevel = approvalRoute['is_top_level'] == true;
        _approver1Name = approvalRoute['approver_1_name']?.toString() ?? 'Atasan Langsung';
        _approver2Name = approvalRoute['approver_2_name']?.toString();
        _scheduleType = meta['schedule_type']?.toString() ?? (user?.scheduleType ?? 'normal');
        _scheduleLabel = meta['schedule_label']?.toString() ??
            (_scheduleType == 'roster' ? 'Roster / Shift' : 'Normal (Senin - Jumat)');
        _holidays = holidayMap;
        _rosterOffDates = rosterOff;

        final suggested = _nextActiveWorkDay(DateTime.now().add(const Duration(days: 1)));
        _mulai = suggested;
        _selesai = suggested;
        _loadingJenis = false;
      });
    } catch (_) {
      if (mounted) setState(() => _loadingJenis = false);
    }
  }

  String _isoDate(DateTime dt) {
    final m = dt.month.toString().padLeft(2, '0');
    final d = dt.day.toString().padLeft(2, '0');
    return '${dt.year}-$m-$d';
  }

  String? _holidayReason(DateTime dt) {
    final iso = _isoDate(dt);
    if (_scheduleType == 'roster') {
      if (_rosterOffDates.contains(iso)) return 'Jadwal OFF Roster (Libur Shift)';
      return null;
    }
    if (dt.weekday == DateTime.sunday) return 'Hari Minggu (Libur Kerja)';
    if (dt.weekday == DateTime.saturday) return 'Hari Sabtu (Libur Kerja)';
    if (_holidays.containsKey(iso)) {
      final desc = _holidays[iso];
      return desc != null && desc.isNotEmpty ? 'Libur Nasional: $desc' : 'Hari Libur Nasional';
    }
    return null;
  }

  int _hitungHariKerjaEfektif(DateTime start, DateTime end) {
    if (end.isBefore(start)) return 0;
    var count = 0;
    var curr = DateTime(start.year, start.month, start.day);
    final limit = DateTime(end.year, end.month, end.day);
    while (!curr.isAfter(limit)) {
      if (_holidayReason(curr) == null) count++;
      curr = curr.add(const Duration(days: 1));
    }
    return count;
  }

  bool get _apakahMemotongSaldoTahunan {
    if (_selectedJenis == null) return false;
    final kode = (_selectedJenis!.kodeCuti ?? '').toUpperCase().trim();
    final nama = _selectedJenis!.nameCuti.toLowerCase().trim();
    final isTahunan = kode == 'CT' || nama == 'cuti' || nama.contains('tahunan');
    if (!isTahunan) return false;

    if (_selectedSub != null) {
      final sName = _selectedSub!.nameCuti.toLowerCase();
      const pengecualian = ['haid', 'sakit', 'ibadah', 'haji', 'umroh', 'nikah', 'lahir', 'duka', 'kematian'];
      for (final p in pengecualian) {
        if (sName.contains(p)) return false;
      }
    }
    return true;
  }

  bool get _apakahWajibDokumen {
    if (_selectedSub != null) {
      if (_selectedSub!.wajibDokumen || _selectedSub!.nameCuti.toLowerCase().contains('sakit')) {
        return true;
      }
    }
    if (_selectedJenis != null && (_selectedJenis!.wajibDokumen || _selectedJenis!.butuhSuratDokter)) {
      return true;
    }
    return false;
  }

  bool get _apakahWajibAlasan {
    final nama = _selectedJenis?.nameCuti.toLowerCase().trim() ?? '';
    return nama == 'cuti' || nama.contains('tahunan') || _selectedJenis != null;
  }

  Future<void> _pickDate({required bool isStart}) async {
    final now = DateTime.now();
    final today = DateTime(now.year, now.month, now.day);
    final initial = isStart ? (_mulai.isBefore(today) ? today : _mulai) : (_selesai.isBefore(_mulai) ? _mulai : _selesai);

    final picked = await showDatePicker(
      context: context,
      initialDate: initial,
      firstDate: isStart ? today : _mulai,
      lastDate: DateTime(today.year + 2, 12, 31),
    );
    if (picked == null || !mounted) return;

    final offReason = _holidayReason(picked);
    if (offReason != null) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Tanggal ${_isoDate(picked)} adalah $offReason. Pilih hari kerja aktif.'),
          backgroundColor: AppTheme.warning,
        ),
      );
      return;
    }

    setState(() {
      if (isStart) {
        _mulai = picked;
        if (_selesai.isBefore(_mulai)) _selesai = picked;
      } else {
        _selesai = picked.isBefore(_mulai) ? _mulai : picked;
      }
      final maxDays = _selectedSub?.durasiDefault;
      if (maxDays != null && maxDays > 0) {
        final maxEnd = _mulai.add(Duration(days: maxDays - 1));
        if (_selesai.isAfter(maxEnd)) {
          _selesai = maxEnd;
        }
      }
    });
  }

  void _applyDurationPreset(int days) {
    final maxDays = _selectedSub?.durasiDefault;
    final clampedDays = (maxDays != null && maxDays > 0 && days > maxDays) ? maxDays : days;
    var targetEnd = _mulai.add(Duration(days: clampedDays - 1));
    targetEnd = _nextActiveWorkDay(targetEnd);
    setState(() => _selesai = targetEnd);
  }

  Future<void> _confirmAndSubmit() async {
    final hariEfektif = _hitungHariKerjaEfektif(_mulai, _selesai);
    if (_selectedJenis == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Pilih Jenis Cuti Utama terlebih dahulu.')),
      );
      return;
    }
    if (hariEfektif <= 0) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Rentang tanggal tidak memiliki hari kerja aktif.'),
          backgroundColor: AppTheme.danger,
        ),
      );
      return;
    }
    if (_apakahMemotongSaldoTahunan && (_sisaSaldo <= 0 || hariEfektif > _sisaSaldo)) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Sisa kuota cuti tahunan Anda tidak mencukupi.'),
          backgroundColor: AppTheme.danger,
        ),
      );
      return;
    }
    if (_apakahWajibDokumen && _dokumen == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Mohon unggah dokumen pendukung (Surat Keterangan / Medis).'),
          backgroundColor: AppTheme.danger,
        ),
      );
      return;
    }
    if (_apakahWajibAlasan && _alasanCtrl.text.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Isi alasan pengajuan cuti terlebih dahulu.')),
      );
      return;
    }

    final df = DateFormat('dd MMM yyyy');
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Konfirmasi Pengajuan Cuti', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 17)),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: AppTheme.surfaceMuted,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: AppTheme.border),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Jenis: ${_selectedJenis!.nameCuti}', style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13)),
                  if (_selectedSub != null) ...[
                    const SizedBox(height: 4),
                    Text('Sub: ${_selectedSub!.nameCuti}', style: const TextStyle(fontSize: 12.5, color: AppTheme.primary, fontWeight: FontWeight.w600)),
                  ],
                  const SizedBox(height: 4),
                  Text('Periode: ${df.format(_mulai)} s/d ${df.format(_selesai)}', style: const TextStyle(fontSize: 12.5)),
                  const SizedBox(height: 4),
                  Text('Total Efektif: $hariEfektif Hari Kerja', style: const TextStyle(fontWeight: FontWeight.w800, color: AppTheme.primary, fontSize: 13)),
                ],
              ),
            ),
            const SizedBox(height: 10),
            const Text(
              'Apakah seluruh informasi di atas sudah benar dan siap dikirimkan ke atasan?',
              style: TextStyle(fontSize: 12.5, color: AppTheme.textSecondary),
            ),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Periksa Kembali')),
          FilledButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Ya, Kirim')),
        ],
      ),
    );

    if (confirmed != true || !mounted) return;

    setState(() => _submitting = true);
    final api = context.read<ApiClient>();
    final messenger = ScaffoldMessenger.of(context);
    final navigator = Navigator.of(context);
    try {
      final isoDf = DateFormat('yyyy-MM-dd');
      final map = <String, dynamic>{
        'jenis_cuti_id': _selectedJenis!.id,
        if (_selectedSub != null) 'sub_cuti_id': _selectedSub!.id,
        'tanggal_mulai': isoDf.format(_mulai),
        'tanggal_selesai': isoDf.format(_selesai),
        'alasan_cuti': _alasanCtrl.text.trim(),
        if (_dokumen?.path != null)
          'dokumen_pendukung': await MultipartFile.fromFile(
            _dokumen!.path!,
            filename: _dokumen!.name,
          ),
      };

      await api.post('/cuti', data: FormData.fromMap(map));
      if (mounted) {
        messenger.showSnackBar(
          const SnackBar(content: Text('Pengajuan cuti berhasil dikirim!'), backgroundColor: AppTheme.success),
        );
        navigator.pop(true);
      }
    } catch (e) {
      if (mounted) {
        messenger.showSnackBar(
          SnackBar(content: Text(e.toString()), backgroundColor: AppTheme.danger),
        );
      }
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().user;
    final df = DateFormat('dd MMM yyyy');
    final totalKalender = _selesai.difference(_mulai).inDays + 1;
    final hariKerjaEfektif = _hitungHariKerjaEfektif(_mulai, _selesai);
    final liburTerlewati = (totalKalender - hariKerjaEfektif).clamp(0, totalKalender);
    final potongSaldo = _apakahMemotongSaldoTahunan;
    final saldoKurang = potongSaldo && (_sisaSaldo <= 0 || hariKerjaEfektif > _sisaSaldo);
    final persenSisa = _kuotaAwal > 0 ? (_sisaSaldo / _kuotaAwal).clamp(0.0, 1.0) : 0.0;
    final persenLabel = (_kuotaAwal > 0 ? ((_sisaSaldo / _kuotaAwal) * 100).round().clamp(0, 100) : 0);

    return Scaffold(
      appBar: AppBar(title: const Text('Ajukan Cuti & Izin Kerja')),
      body: _loadingJenis
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // 1. Kartu Kuota Cuti Tahunan (Setara Sidebar Web)
                  Card(
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                decoration: BoxDecoration(
                                  color: AppTheme.primary.withValues(alpha: 0.10),
                                  borderRadius: BorderRadius.circular(999),
                                ),
                                child: Text(
                                  'STATUS KUOTA ${DateTime.now().year}',
                                  style: const TextStyle(
                                    fontSize: 10.5,
                                    fontWeight: FontWeight.w800,
                                    letterSpacing: 0.5,
                                    color: AppTheme.primary,
                                  ),
                                ),
                              ),
                              const Spacer(),
                              const Text(
                                'Cuti Tahunan',
                                style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppTheme.textSecondary),
                              ),
                            ],
                          ),
                          const SizedBox(height: 12),
                          Row(
                            crossAxisAlignment: CrossAxisAlignment.end,
                            children: [
                              Text(
                                '$_sisaSaldo',
                                style: const TextStyle(fontSize: 32, fontWeight: FontWeight.w900, color: AppTheme.textPrimary, height: 1),
                              ),
                              const SizedBox(width: 6),
                              const Padding(
                                padding: EdgeInsets.only(bottom: 3),
                                child: Text(
                                  'Hari Kerja',
                                  style: TextStyle(fontSize: 13.5, fontWeight: FontWeight.w700, color: AppTheme.textSecondary),
                                ),
                              ),
                              const Spacer(),
                              Text(
                                '$persenLabel%',
                                style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w800, color: AppTheme.primary),
                              ),
                            ],
                          ),
                          const SizedBox(height: 8),
                          ClipRRect(
                            borderRadius: BorderRadius.circular(99),
                            child: LinearProgressIndicator(
                              value: persenSisa,
                              minHeight: 7,
                              backgroundColor: AppTheme.border,
                              valueColor: const AlwaysStoppedAnimation(AppTheme.accent),
                            ),
                          ),
                          const SizedBox(height: 12),
                          Row(
                            children: [
                              Expanded(child: _MiniQuotaBox(label: 'TOTAL', value: '$_kuotaAwal H', color: AppTheme.textPrimary)),
                              const SizedBox(width: 8),
                              Expanded(child: _MiniQuotaBox(label: 'TERPAKAI', value: '$_cutiTerpakai H', color: AppTheme.danger)),
                              const SizedBox(width: 8),
                              Expanded(child: _MiniQuotaBox(label: 'PENDING', value: '$_pendingHari H', color: AppTheme.warning)),
                            ],
                          ),
                          if (user?.isPerempuan == true) ...[
                            const SizedBox(height: 10),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                              decoration: BoxDecoration(
                                color: const Color(0xFFFFF1F2),
                                borderRadius: BorderRadius.circular(10),
                                border: Border.all(color: const Color(0xFFFECDD3)),
                              ),
                              child: Row(
                                children: [
                                  const Icon(Icons.favorite_rounded, size: 15, color: Color(0xFFE11D48)),
                                  const SizedBox(width: 8),
                                  const Expanded(
                                    child: Text(
                                      'Kuota Cuti Haid Bulan Ini',
                                      style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.w700, color: Color(0xFF881337)),
                                    ),
                                  ),
                                  Text(
                                    '$_sisaCutiHaid / 2 Hari',
                                    style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: Color(0xFFE11D48)),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 12),

                  // 2. Peringatan Saldo Kurang
                  if (saldoKurang) ...[
                    Container(
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        color: const Color(0xFFFFFBEB),
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: const Color(0xFFFCD34D)),
                      ),
                      child: Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Icon(Icons.warning_amber_rounded, color: AppTheme.warning, size: 22),
                          const SizedBox(width: 10),
                          Expanded(
                            child: Text(
                              _sisaSaldo <= 0
                                  ? 'Sisa kuota Cuti Tahunan Anda habis (0 hari). Pengajuan pemotongan kuota tidak dapat dilanjutkan.'
                                  : 'Durasi pengajuan ($hariKerjaEfektif hari kerja) melebihi sisa kuota tersedia ($_sisaSaldo hari).',
                              style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w600, color: Color(0xFF92400E)),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 12),
                  ],

                  // 3. Seksi 1: Kategori & Klasifikasi Izin
                  Card(
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          const _NumberedSectionTitle(number: '1', title: 'KATEGORI & KLASIFIKASI IZIN'),
                          const SizedBox(height: 14),
                          DropdownButtonFormField<JenisCutiModel>(
                            initialValue: _selectedJenis,
                            isExpanded: true,
                            decoration: const InputDecoration(
                              labelText: 'Jenis Cuti Utama *',
                              prefixIcon: Icon(Icons.list_alt_rounded),
                            ),
                            items: _jenisList
                                .map((j) => DropdownMenuItem(value: j, child: Text(j.nameCuti)))
                                .toList(),
                            onChanged: (v) {
                              setState(() {
                                _selectedJenis = v;
                                _selectedSub = (v != null && v.subCutis.isNotEmpty) ? v.subCutis.first : null;
                              });
                            },
                          ),
                          if (_selectedJenis != null) ...[
                            const SizedBox(height: 10),
                            Wrap(
                              spacing: 8,
                              runSpacing: 6,
                              children: [
                                _InfoPill(
                                  icon: potongSaldo ? Icons.account_balance_wallet_outlined : Icons.verified_outlined,
                                  label: potongSaldo ? 'Memotong Kuota Tahunan' : 'Izin Khusus (Bebas Kuota)',
                                  color: potongSaldo ? AppTheme.warning : AppTheme.success,
                                ),
                              ],
                            ),
                          ],
                          if (_selectedJenis != null && _selectedJenis!.subCutis.isNotEmpty) ...[
                            const SizedBox(height: 14),
                            Container(
                              padding: const EdgeInsets.all(14),
                              decoration: BoxDecoration(
                                color: const Color(0xFFF0F9FF),
                                borderRadius: BorderRadius.circular(16),
                                border: Border.all(color: const Color(0xFFBAE6FD)),
                              ),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.stretch,
                                children: [
                                  Row(
                                    children: [
                                      const Expanded(
                                        child: Text(
                                          'Detail Keperluan / Sub-Kategori *',
                                          style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w800, color: AppTheme.textPrimary),
                                        ),
                                      ),
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                        decoration: BoxDecoration(
                                          color: Colors.white,
                                          borderRadius: BorderRadius.circular(8),
                                          border: Border.all(color: const Color(0xFF7DD3FC)),
                                        ),
                                        child: Text(
                                          _selectedSub?.durasiDefault != null
                                              ? 'Maks. ${_selectedSub!.durasiDefault} Hari'
                                              : 'Sesuai Pengajuan',
                                          style: const TextStyle(fontSize: 10.5, fontWeight: FontWeight.w800, color: AppTheme.primary),
                                        ),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 10),
                                  DropdownButtonFormField<SubCutiModel>(
                                    initialValue: _selectedSub,
                                    isExpanded: true,
                                    decoration: const InputDecoration(
                                      fillColor: Colors.white,
                                      labelText: 'Pilih Sub-Kategori',
                                      prefixIcon: Icon(Icons.account_tree_outlined),
                                    ),
                                    items: _selectedJenis!.subCutis
                                        .map((s) => DropdownMenuItem(
                                              value: s,
                                              child: Text(
                                                s.durasiDefault != null ? '${s.nameCuti} (${s.durasiDefault} Hari)' : s.nameCuti,
                                                overflow: TextOverflow.ellipsis,
                                              ),
                                            ))
                                        .toList(),
                                    onChanged: (v) {
                                      setState(() {
                                        _selectedSub = v;
                                        if (v?.durasiDefault != null && v!.durasiDefault! > 0) {
                                          final maxEnd = _mulai.add(Duration(days: v.durasiDefault! - 1));
                                          if (_selesai.isAfter(maxEnd)) {
                                            _selesai = maxEnd;
                                          }
                                        }
                                      });
                                    },
                                  ),
                                  if (_selectedSub?.keteranganOpsional != null &&
                                      _selectedSub!.keteranganOpsional!.isNotEmpty) ...[
                                    const SizedBox(height: 8),
                                    Row(
                                      children: [
                                        const Icon(Icons.lightbulb_outline_rounded, size: 14, color: AppTheme.warning),
                                        const SizedBox(width: 6),
                                        Expanded(
                                          child: Text(
                                            _selectedSub!.keteranganOpsional!,
                                            style: const TextStyle(fontSize: 11.5, color: AppTheme.textSecondary),
                                          ),
                                        ),
                                      ],
                                    ),
                                  ],
                                ],
                              ),
                            ),
                          ],
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 12),

                  // 4. Seksi 2: Rentang Waktu & Durasi
                  Card(
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          const _NumberedSectionTitle(number: '2', title: 'RENTANG WAKTU & DURASI'),
                          const SizedBox(height: 10),
                          Wrap(
                            spacing: 8,
                            runSpacing: 6,
                            crossAxisAlignment: WrapCrossAlignment.center,
                            children: [
                              const Text(
                                'Preset:',
                                style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.w700, color: AppTheme.textSecondary),
                              ),
                              for (final d in const [1, 2, 3])
                                ActionChip(
                                  label: Text('$d Hari', style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w700)),
                                  backgroundColor: AppTheme.surfaceMuted,
                                  side: const BorderSide(color: AppTheme.border),
                                  onPressed: () => _applyDurationPreset(d),
                                ),
                            ],
                          ),
                          const SizedBox(height: 10),
                          Row(
                            children: [
                              Expanded(
                                child: OutlinedButton.icon(
                                  onPressed: () => _pickDate(isStart: true),
                                  icon: const Icon(Icons.date_range_rounded, size: 17),
                                  label: Text('Mulai\n${df.format(_mulai)}', textAlign: TextAlign.center, style: const TextStyle(fontSize: 12)),
                                ),
                              ),
                              const SizedBox(width: 10),
                              Expanded(
                                child: OutlinedButton.icon(
                                  onPressed: () => _pickDate(isStart: false),
                                  icon: const Icon(Icons.event_rounded, size: 17),
                                  label: Text('Selesai\n${df.format(_selesai)}', textAlign: TextAlign.center, style: const TextStyle(fontSize: 12)),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 12),
                          Container(
                            padding: const EdgeInsets.all(12),
                            decoration: BoxDecoration(
                              gradient: const LinearGradient(
                                colors: [Color(0xFFF0F9FF), Color(0xFFF8FAFC)],
                                begin: Alignment.topLeft,
                                end: Alignment.bottomRight,
                              ),
                              borderRadius: BorderRadius.circular(14),
                              border: Border.all(color: const Color(0xFFBAE6FD)),
                            ),
                            child: Row(
                              children: [
                                Container(
                                  width: 38,
                                  height: 38,
                                  decoration: BoxDecoration(
                                    color: AppTheme.primary.withValues(alpha: 0.12),
                                    borderRadius: BorderRadius.circular(10),
                                  ),
                                  child: const Icon(Icons.calculate_outlined, color: AppTheme.primary, size: 20),
                                ),
                                const SizedBox(width: 10),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Row(
                                        children: [
                                          const Text('Durasi Terhitung: ', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700)),
                                          Container(
                                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                            decoration: BoxDecoration(
                                              color: AppTheme.primary,
                                              borderRadius: BorderRadius.circular(999),
                                            ),
                                            child: Text(
                                              '$hariKerjaEfektif Hari Kerja',
                                              style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: Colors.white),
                                            ),
                                          ),
                                        ],
                                      ),
                                      const SizedBox(height: 3),
                                      Text(
                                        liburTerlewati > 0
                                            ? '$liburTerlewati hari libur otomatis dikecualikan • $_scheduleLabel'
                                            : 'Pola Jadwal: $_scheduleLabel',
                                        style: const TextStyle(fontSize: 11, color: AppTheme.textSecondary),
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 12),

                  // 5. Seksi 3: Alasan & Template Cepat
                  Card(
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          Row(
                            children: [
                              const Expanded(child: _NumberedSectionTitle(number: '3', title: 'ALASAN & PENJELASAN')),
                              Text(
                                '${_alasanCtrl.text.length} / 500',
                                style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: AppTheme.textSecondary),
                              ),
                            ],
                          ),
                          const SizedBox(height: 12),
                          TextField(
                            controller: _alasanCtrl,
                            maxLines: 3,
                            maxLength: 500,
                            decoration: const InputDecoration(
                              labelText: 'Alasan / Catatan Pengajuan *',
                              hintText: 'Tuliskan keterangan keperluan cuti secara jelas...',
                              counterText: '',
                            ),
                          ),
                          const SizedBox(height: 10),
                          Wrap(
                            spacing: 6,
                            runSpacing: 6,
                            children: [
                              _TemplateReasonChip(
                                label: 'Keperluan Keluarga',
                                onTap: () => _alasanCtrl.text = 'Keperluan urusan keluarga.',
                              ),
                              _TemplateReasonChip(
                                label: 'Kesehatan / Istirahat',
                                onTap: () => _alasanCtrl.text = 'Pemeriksaan medis / istirahat menurut anjuran dokter.',
                              ),
                              _TemplateReasonChip(
                                label: 'Acara Pernikahan',
                                onTap: () => _alasanCtrl.text = 'Menghadiri acara pernikahan keluarga inti.',
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 12),

                  // 6. Seksi 4: Lampiran Dokumen
                  Card(
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          Row(
                            children: [
                              const Expanded(child: _NumberedSectionTitle(number: '4', title: 'LAMPIRAN DOKUMEN')),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                decoration: BoxDecoration(
                                  color: (_apakahWajibDokumen ? AppTheme.danger : AppTheme.textSecondary)
                                      .withValues(alpha: 0.10),
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                child: Text(
                                  _apakahWajibDokumen ? 'WAJIB DIUNGGAH' : 'OPSIONAL',
                                  style: TextStyle(
                                    fontSize: 10,
                                    fontWeight: FontWeight.w800,
                                    color: _apakahWajibDokumen ? AppTheme.danger : AppTheme.textSecondary,
                                  ),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 12),
                          InkWell(
                            onTap: () async {
                              final files = await FilePicker.pickFiles(
                                type: FileType.custom,
                                allowedExtensions: ['pdf', 'jpg', 'jpeg', 'png'],
                              );
                              if (files.isNotEmpty) {
                                setState(() => _dokumen = files.first);
                              }
                            },
                            borderRadius: BorderRadius.circular(14),
                            child: Container(
                              padding: const EdgeInsets.all(14),
                              decoration: BoxDecoration(
                                color: AppTheme.surfaceMuted,
                                borderRadius: BorderRadius.circular(14),
                                border: Border.all(
                                  color: _dokumen != null ? AppTheme.success : AppTheme.border,
                                  width: 1.3,
                                ),
                              ),
                              child: Row(
                                children: [
                                  Container(
                                    width: 40,
                                    height: 40,
                                    decoration: BoxDecoration(
                                      color: (_dokumen != null ? AppTheme.success : AppTheme.primary)
                                          .withValues(alpha: 0.10),
                                      borderRadius: BorderRadius.circular(11),
                                    ),
                                    child: Icon(
                                      _dokumen != null ? Icons.description_rounded : Icons.cloud_upload_outlined,
                                      color: _dokumen != null ? AppTheme.success : AppTheme.primary,
                                    ),
                                  ),
                                  const SizedBox(width: 12),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          _dokumen == null ? 'Pilih Berkas (PDF / JPG / PNG)' : _dokumen!.name,
                                          maxLines: 1,
                                          overflow: TextOverflow.ellipsis,
                                          style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13),
                                        ),
                                        const SizedBox(height: 2),
                                        Text(
                                          _dokumen == null
                                              ? 'Ketuk untuk memilih surat keterangan atau foto'
                                              : 'File siap diunggah',
                                          style: TextStyle(
                                            fontSize: 11,
                                            color: _dokumen == null ? AppTheme.textSecondary : AppTheme.success,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                  if (_dokumen != null)
                                    IconButton(
                                      onPressed: () => setState(() => _dokumen = null),
                                      icon: const Icon(Icons.close_rounded, color: AppTheme.danger, size: 20),
                                    ),
                                ],
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 12),

                  // 7. Alur Persetujuan & Ringkasan Live
                  Card(
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              const Text(
                                'Alur Persetujuan & Ringkasan',
                                style: TextStyle(fontSize: 13.5, fontWeight: FontWeight.w800, color: AppTheme.textPrimary),
                              ),
                              const Spacer(),
                              Text(
                                _isTopLevel
                                    ? 'Auto-Approval'
                                    : (_levels == 2 ? '2 Tahap Verifikasi' : '1 Tahap Verifikasi'),
                                style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w700, color: AppTheme.primary),
                              ),
                            ],
                          ),
                          const Divider(height: 18),
                          _SummaryRow(label: 'Jenis Cuti', value: _selectedJenis?.nameCuti ?? '-'),
                          if (_selectedSub != null) _SummaryRow(label: 'Sub-Kategori', value: _selectedSub!.nameCuti),
                          _SummaryRow(label: 'Periode', value: '${df.format(_mulai)} – ${df.format(_selesai)}'),
                          _SummaryRow(label: 'Hari Efektif', value: '$hariKerjaEfektif Hari Kerja'),
                          _SummaryRow(
                            label: 'Efek Kuota',
                            value: potongSaldo ? '-$hariKerjaEfektif Hari (Sisa ${_sisaSaldo - hariKerjaEfektif} H)' : 'Bebas Kuota',
                          ),
                          _SummaryRow(
                            label: 'Penyetuju',
                            value: _isTopLevel
                                ? 'Disetujui Otomatis'
                                : (_levels == 2 && _approver2Name != null
                                    ? '$_approver1Name → $_approver2Name'
                                    : _approver1Name),
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 18),

                  FilledButton.icon(
                    onPressed: (_submitting || saldoKurang) ? null : _confirmAndSubmit,
                    icon: _submitting
                        ? const SizedBox(
                            width: 18,
                            height: 18,
                            child: CircularProgressIndicator(strokeWidth: 2.2, color: Colors.white),
                          )
                        : const Icon(Icons.send_rounded, size: 18),
                    label: Text(_submitting ? 'Mengirim Pengajuan...' : 'Kirim Pengajuan Cuti'),
                  ),
                ],
              ),
            ),
    );
  }
}

class _NumberedSectionTitle extends StatelessWidget {
  final String number;
  final String title;

  const _NumberedSectionTitle({required this.number, required this.title});

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Container(
          width: 24,
          height: 24,
          alignment: Alignment.center,
          decoration: const BoxDecoration(color: AppTheme.primary, shape: BoxShape.circle),
          child: Text(
            number,
            style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 12),
          ),
        ),
        const SizedBox(width: 8),
        Text(
          title,
          style: const TextStyle(
            fontSize: 11.5,
            fontWeight: FontWeight.w800,
            letterSpacing: 0.6,
            color: AppTheme.textSecondary,
          ),
        ),
      ],
    );
  }
}

class _MiniQuotaBox extends StatelessWidget {
  final String label;
  final String value;
  final Color color;

  const _MiniQuotaBox({required this.label, required this.value, required this.color});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 6),
      decoration: BoxDecoration(
        color: AppTheme.surfaceMuted,
        borderRadius: BorderRadius.circular(11),
        border: Border.all(color: AppTheme.border),
      ),
      child: Column(
        children: [
          Text(label, style: const TextStyle(fontSize: 9.5, fontWeight: FontWeight.w800, color: AppTheme.textSecondary)),
          const SizedBox(height: 2),
          Text(value, style: TextStyle(fontSize: 13, fontWeight: FontWeight.w800, color: color)),
        ],
      ),
    );
  }
}

class _InfoPill extends StatelessWidget {
  final IconData icon;
  final String label;
  final Color color;

  const _InfoPill({required this.icon, required this.label, required this.color});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.10),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: color.withValues(alpha: 0.25)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 13.5, color: color),
          const SizedBox(width: 5),
          Text(label, style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: color)),
        ],
      ),
    );
  }
}

class _TemplateReasonChip extends StatelessWidget {
  final String label;
  final VoidCallback onTap;

  const _TemplateReasonChip({required this.label, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(10),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
        decoration: BoxDecoration(
          color: AppTheme.surfaceMuted,
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: AppTheme.border),
        ),
        child: Text(label, style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w600, color: AppTheme.primary)),
      ),
    );
  }
}

class _SummaryRow extends StatelessWidget {
  final String label;
  final String value;

  const _SummaryRow({required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 3.5),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 100,
            child: Text(label, style: const TextStyle(fontSize: 12, color: AppTheme.textSecondary)),
          ),
          Expanded(
            child: Text(
              value,
              textAlign: TextAlign.right,
              style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: AppTheme.textPrimary),
            ),
          ),
        ],
      ),
    );
  }
}

class CreateCarScreen extends StatefulWidget {
  const CreateCarScreen({super.key});

  @override
  State<CreateCarScreen> createState() => _CreateCarScreenState();
}

class _CreateCarScreenState extends State<CreateCarScreen> {
  final _alasanCtrl = TextEditingController();
  final _noteCtrl = TextEditingController();
  final _rekeningCtrl = TextEditingController();
  final List<_ItemRow> _items = [_ItemRow()];
  bool _submitting = false;

  @override
  void dispose() {
    _alasanCtrl.dispose();
    _noteCtrl.dispose();
    _rekeningCtrl.dispose();
    for (final item in _items) {
      item.dispose();
    }
    super.dispose();
  }

  Future<void> _submit() async {
    if (_alasanCtrl.text.trim().isEmpty || _items.any((i) => i.nama.text.trim().isEmpty)) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Isi alasan pembelian dan nama seluruh item barang.')),
      );
      return;
    }

    setState(() => _submitting = true);
    try {
      await context.read<ApiClient>().post('/car', data: {
        'alasan_pembelian': _alasanCtrl.text.trim(),
        if (_noteCtrl.text.trim().isNotEmpty) 'note_explanation': _noteCtrl.text.trim(),
        if (_rekeningCtrl.text.trim().isNotEmpty) 'receiving_account': _rekeningCtrl.text.trim(),
        'items': _items
            .map((i) => {
                  'nama_barang': i.nama.text.trim(),
                  'jumlah': int.tryParse(i.qty.text) ?? 1,
                  'satuan': i.satuan.text.trim().isEmpty ? 'PCS' : i.satuan.text.trim(),
                  'estimasi_harga': double.tryParse(i.harga.text) ?? 0,
                  'ongkir': double.tryParse(i.ongkir.text) ?? 0,
                })
            .toList(),
      });
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Pengajuan CAR berhasil dikirim!'), backgroundColor: AppTheme.success),
        );
        Navigator.pop(context, true);
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(e.toString()), backgroundColor: AppTheme.danger),
        );
      }
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  double get _totalEstimasi {
    double total = 0;
    for (final i in _items) {
      final qty = double.tryParse(i.qty.text) ?? 1;
      final harga = double.tryParse(i.harga.text) ?? 0;
      final ongkir = double.tryParse(i.ongkir.text) ?? 0;
      total += (qty * harga) + ongkir;
    }
    return total;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Pengajuan CAR')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.fromLTRB(16, 16, 16, 28),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    const UmbulanSectionHeader(
                      title: 'Informasi Kebutuhan Dana',
                      subtitle: 'Alasan pengeluaran & rekening pencairan',
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: _alasanCtrl,
                      maxLines: 2,
                      decoration: const InputDecoration(labelText: 'Alasan Pembelian / Pengeluaran *'),
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: _rekeningCtrl,
                      decoration: const InputDecoration(labelText: 'Rekening Penerima (Opsional)'),
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: _noteCtrl,
                      decoration: const InputDecoration(labelText: 'Catatan Tambahan (Opsional)'),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),
            UmbulanSectionHeader(
              title: 'Daftar Item Barang',
              subtitle: 'Estimasi Total: ${formatRupiah(_totalEstimasi)}',
              trailing: TextButton.icon(
                onPressed: () => setState(() => _items.add(_ItemRow())),
                icon: const Icon(Icons.add_circle_outline_rounded, size: 18),
                label: const Text('Tambah Item'),
              ),
            ),
            const SizedBox(height: 8),
            ..._items.asMap().entries.map((entry) {
              final idx = entry.key;
              final item = entry.value;
              return Card(
                margin: const EdgeInsets.only(bottom: 12),
                child: Padding(
                  padding: const EdgeInsets.all(14),
                  child: Column(
                    children: [
                      Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 3),
                            decoration: BoxDecoration(
                              color: AppTheme.teal.withValues(alpha: 0.10),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Text(
                              'Item #${idx + 1}',
                              style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 12, color: AppTheme.teal),
                            ),
                          ),
                          const Spacer(),
                          if (_items.length > 1)
                            IconButton(
                              onPressed: () => setState(() => _items.removeAt(idx)),
                              icon: const Icon(Icons.delete_outline, color: AppTheme.danger),
                            ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      TextField(
                        controller: item.nama,
                        decoration: const InputDecoration(labelText: 'Nama Barang *'),
                      ),
                      const SizedBox(height: 10),
                      Row(
                        children: [
                          Expanded(
                            child: TextField(
                              controller: item.qty,
                              keyboardType: TextInputType.number,
                              onChanged: (_) => setState(() {}),
                              decoration: const InputDecoration(labelText: 'Jumlah'),
                            ),
                          ),
                          const SizedBox(width: 10),
                          Expanded(
                            child: TextField(
                              controller: item.satuan,
                              decoration: const InputDecoration(labelText: 'Satuan'),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 10),
                      Row(
                        children: [
                          Expanded(
                            child: TextField(
                              controller: item.harga,
                              keyboardType: TextInputType.number,
                              onChanged: (_) => setState(() {}),
                              decoration: const InputDecoration(labelText: 'Estimasi Harga'),
                            ),
                          ),
                          const SizedBox(width: 10),
                          Expanded(
                            child: TextField(
                              controller: item.ongkir,
                              keyboardType: TextInputType.number,
                              onChanged: (_) => setState(() {}),
                              decoration: const InputDecoration(labelText: 'Ongkir'),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              );
            }),
            const SizedBox(height: 8),
            FilledButton.icon(
              style: FilledButton.styleFrom(backgroundColor: AppTheme.teal),
              onPressed: _submitting ? null : _submit,
              icon: const Icon(Icons.send_rounded, size: 18),
              label: Text(_submitting ? 'Mengirim...' : 'Kirim Pengajuan CAR'),
            ),
          ],
        ),
      ),
    );
  }
}

class CreateMprScreen extends StatefulWidget {
  const CreateMprScreen({super.key});

  @override
  State<CreateMprScreen> createState() => _CreateMprScreenState();
}

class _CreateMprScreenState extends State<CreateMprScreen> {
  String _priority = 'Normal';
  final _deptCtrl = TextEditingController(text: 'Operation');
  final _deliveryCtrl = TextEditingController(text: 'Site Umbulan');
  final _urgensiCtrl = TextEditingController();
  final List<_ItemRow> _items = [_ItemRow()];
  bool _submitting = false;

  @override
  void dispose() {
    _deptCtrl.dispose();
    _deliveryCtrl.dispose();
    _urgensiCtrl.dispose();
    for (final item in _items) {
      item.dispose();
    }
    super.dispose();
  }

  Future<void> _submit() async {
    if (_urgensiCtrl.text.trim().isEmpty || _items.any((i) => i.nama.text.trim().isEmpty)) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Isi keperluan urgensi dan nama barang.')),
      );
      return;
    }

    setState(() => _submitting = true);
    try {
      await context.read<ApiClient>().post('/mpr', data: {
        'priority': _priority,
        'department': _deptCtrl.text.trim(),
        'delivery_point': _deliveryCtrl.text.trim(),
        'keperluan_urgensi': _urgensiCtrl.text.trim(),
        'items': _items
            .map((i) => {
                  'nama_barang': i.nama.text.trim(),
                  'jumlah': int.tryParse(i.qty.text) ?? 1,
                  'satuan': i.satuan.text.trim().isEmpty ? 'PCS' : i.satuan.text.trim(),
                  'estimasi_harga': double.tryParse(i.harga.text) ?? 0,
                })
            .toList(),
      });
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Pengajuan MPR berhasil dikirim!'), backgroundColor: AppTheme.success),
        );
        Navigator.pop(context, true);
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(e.toString()), backgroundColor: AppTheme.danger),
        );
      }
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  double get _totalEstimasiMpr {
    double total = 0;
    for (final i in _items) {
      final qty = double.tryParse(i.qty.text) ?? 1;
      final harga = double.tryParse(i.harga.text) ?? 0;
      total += qty * harga;
    }
    return total;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Pengajuan MPR')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.fromLTRB(16, 16, 16, 28),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    const UmbulanSectionHeader(
                      title: 'Detail Pengadaan Material',
                      subtitle: 'Tentukan prioritas & titik pengiriman barang',
                    ),
                    const SizedBox(height: 12),
                    DropdownButtonFormField<String>(
                      initialValue: _priority,
                      decoration: const InputDecoration(labelText: 'Prioritas'),
                      items: const [
                        DropdownMenuItem(value: 'Normal', child: Text('Normal')),
                        DropdownMenuItem(value: 'Urgent', child: Text('Urgent')),
                        DropdownMenuItem(value: 'Emergency', child: Text('Emergency')),
                      ],
                      onChanged: (v) => setState(() => _priority = v ?? 'Normal'),
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: _deptCtrl,
                      decoration: const InputDecoration(labelText: 'Departemen'),
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: _deliveryCtrl,
                      decoration: const InputDecoration(labelText: 'Titik Pengiriman (Delivery Point)'),
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: _urgensiCtrl,
                      maxLines: 2,
                      decoration: const InputDecoration(labelText: 'Keperluan & Urgensi *'),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),
            UmbulanSectionHeader(
              title: 'Daftar Material',
              subtitle: 'Estimasi Total: ${formatRupiah(_totalEstimasiMpr)}',
              trailing: TextButton.icon(
                onPressed: () => setState(() => _items.add(_ItemRow())),
                icon: const Icon(Icons.add_circle_outline_rounded, size: 18),
                label: const Text('Tambah Item'),
              ),
            ),
            const SizedBox(height: 8),
            ..._items.asMap().entries.map((entry) {
              final idx = entry.key;
              final item = entry.value;
              return Card(
                margin: const EdgeInsets.only(bottom: 12),
                child: Padding(
                  padding: const EdgeInsets.all(14),
                  child: Column(
                    children: [
                      Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 3),
                            decoration: BoxDecoration(
                              color: AppTheme.indigo.withValues(alpha: 0.10),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Text(
                              'Material #${idx + 1}',
                              style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 12, color: AppTheme.indigo),
                            ),
                          ),
                          const Spacer(),
                          if (_items.length > 1)
                            IconButton(
                              onPressed: () => setState(() => _items.removeAt(idx)),
                              icon: const Icon(Icons.delete_outline, color: AppTheme.danger),
                            ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      TextField(
                        controller: item.nama,
                        decoration: const InputDecoration(labelText: 'Nama Material *'),
                      ),
                      const SizedBox(height: 10),
                      Row(
                        children: [
                          Expanded(
                            child: TextField(
                              controller: item.qty,
                              keyboardType: TextInputType.number,
                              onChanged: (_) => setState(() {}),
                              decoration: const InputDecoration(labelText: 'Jumlah'),
                            ),
                          ),
                          const SizedBox(width: 10),
                          Expanded(
                            child: TextField(
                              controller: item.satuan,
                              decoration: const InputDecoration(labelText: 'Satuan'),
                            ),
                          ),
                          const SizedBox(width: 10),
                          Expanded(
                            child: TextField(
                              controller: item.harga,
                              keyboardType: TextInputType.number,
                              onChanged: (_) => setState(() {}),
                              decoration: const InputDecoration(labelText: 'Est. Harga'),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              );
            }),
            const SizedBox(height: 8),
            FilledButton.icon(
              style: FilledButton.styleFrom(backgroundColor: AppTheme.indigo),
              onPressed: _submitting ? null : _submit,
              icon: const Icon(Icons.send_rounded, size: 18),
              label: Text(_submitting ? 'Mengirim...' : 'Kirim Pengajuan MPR'),
            ),
          ],
        ),
      ),
    );
  }
}

class _ItemRow {
  final nama = TextEditingController();
  final qty = TextEditingController(text: '1');
  final satuan = TextEditingController(text: 'PCS');
  final harga = TextEditingController(text: '0');
  final ongkir = TextEditingController(text: '0');

  void dispose() {
    nama.dispose();
    qty.dispose();
    satuan.dispose();
    harga.dispose();
    ongkir.dispose();
  }
}
