<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Material Purchase Request - {{ $mpr->nomor_mpr }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #222;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        /* Header Table */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .header-table td {
            vertical-align: middle;
            border: 1px solid #000;
            padding: 6px;
        }
        .title-main {
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
            margin: 0;
            letter-spacing: 1px;
        }
        .company-logo {
            text-align: center;
        }

        /* Meta Info Table */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .info-table td {
            border: 1px solid #000;
            padding: 5px 8px;
            font-size: 10.5px;
        }
        .info-label {
            font-weight: bold;
            background-color: #f8fafc;
            width: 20%;
        }
        .info-value {
            width: 30%;
        }

        /* Items Content Table */
        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .content-table th {
            background-color: #f1f5f9;
            border: 1px solid #000;
            font-weight: bold;
            text-align: center;
            padding: 7px 5px;
            font-size: 10.5px;
        }
        .content-table td {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: middle;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }

        /* Approval Matrix Table */
        .approval-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .approval-table td {
            border: 1px solid #000;
            width: 25%;
            text-align: center;
            vertical-align: top;
            padding: 4px;
            font-size: 10px;
        }
        .approval-title {
            font-weight: bold;
            background-color: #f8fafc;
            border-bottom: 1px solid #000;
            padding: 4px 0;
        }
        .signature-space {
            height: 60px;
        }
        .signer-name {
            font-weight: bold;
            text-decoration: underline;
        }

        /* Footer Terms */
        .footer-terms {
            margin-top: 20px;
            font-size: 9px;
            color: #475569;
            border-top: 1px dashed #94a3b8;
            padding-top: 8px;
        }
        .footer-terms h4 {
            margin: 0 0 3px 0;
            font-size: 9.5px;
            text-transform: uppercase;
        }
        .footer-terms ol {
            margin: 0;
            padding-left: 14px;
        }
        .footer-terms li {
            margin-bottom: 2px;
        }
    </style>
</head>
<body>

    <!-- KOP HEADER UTAMA -->
    <table class="header-table">
        <tr>
            <td rowspan="2" class="company-logo" style="width: 18%;">
                <img src="{{ public_path('images/iconfav.png') }}" style="max-width: 100%; max-height: 48px; object-fit: contain;">
            </td>
            <td rowspan="2" style="width: 47%;">
                <h1 class="title-main">Material Purchase Request</h1>
                <div style="text-align: center; font-size: 9px; color: #64748b; margin-top: 2px;">PT META ADHYA TIRTA UMBULAN</div>
            </td>
            <td style="width: 35%;">
                <strong>Nomor Dokumen:</strong> <br>
                <span style="font-size: 11px; font-weight: bold; color: #0284c7;">{{ $mpr->nomor_mpr }}</span>
            </td>
        </tr>
        <tr>
            <td>
                <strong>Tanggal Pengajuan:</strong> {{ \Carbon\Carbon::parse($mpr->tanggal_pengajuan)->format('d F Y') }}
            </td>
        </tr>
    </table>

    <!-- INFORMASI PEMOHON & STASIUN -->
    <table class="info-table">
        <tr>
            <td class="info-label">Nama Pemohon</td>
            <td class="info-value">{{ $mpr->user->name ?? '-' }}</td>
            <td class="info-label">Stasiun Kerja / Unit</td>
            <td class="info-value">{{ $mpr->user->station->name ?? 'Stasiun Umbulan' }}</td>
        </tr>
        <tr>
            <td class="info-label">Jabatan / Role</td>
            <td class="info-value">{{ strtoupper($mpr->user->role->role_name ?? '-') }} {{ $mpr->user->job_title }}</td>
            <td class="info-label">NIP Karyawan</td>
            <td class="info-value">{{ $mpr->user->nip ?? '-' }}</td>
        </tr>
    </table>

    <div style="margin-bottom: 6px; font-weight: bold; font-size: 11px; color: #1e293b;">
        Keperluan / Urgensi Pemakaian Material:
    </div>
    <div style="border: 1px solid #000; padding: 8px; margin-bottom: 12px; background-color: #fafafa; font-size: 10.5px; border-radius: 4px;">
        {{ $mpr->keperluan_urgensi }}
    </div>

    <!-- TABEL DETAIL RINCIAN MATERIAL -->
    <table class="content-table">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 36%;">Nama Barang / Material</th>
                <th style="width: 10%;">Qty</th>
                <th style="width: 10%;">Satuan</th>
                <th style="width: 15%;">Est. Harga Satuan</th>
                <th style="width: 15%;">Subtotal</th>
                <th style="width: 10%;">Ket. Spec</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @foreach($mpr->items as $index => $item)
                @php 
                    $subtotal = $item->jumlah * $item->estimasi_harga;
                    $grandTotal += $subtotal;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="font-bold">{{ $item->nama_barang }}</td>
                    <td class="text-center font-bold">{{ $item->jumlah }}</td>
                    <td class="text-center">{{ $item->satuan }}</td>
                    <td class="text-right">Rp {{ number_format($item->estimasi_harga, 0, ',', '.') }}</td>
                    <td class="text-right font-bold">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                    <td style="font-size: 9.5px; color: #475569;">{{ $item->keterangan_item ?? '-' }}</td>
                </tr>
            @endforeach

            <tr>
                <td colspan="5" class="text-right font-bold" style="background-color: #f8fafc;">Estimasi Grand Total Material:</td>
                <td class="text-right font-bold" style="background-color: #f8fafc; font-size: 11.5px; color: #0284c7;">
                    Rp {{ number_format($grandTotal, 0, ',', '.') }}
                </td>
                <td style="background-color: #f8fafc;"></td>
            </tr>
        </tbody>
    </table>

    <table class="approval-table">
        <tr>
            {{-- 1. PEMOHON MATERIAL --}}
            <td>
                <div class="approval-title">Diajukan Oleh</div>
                <div style="font-size: 8.5px; margin-top: 2px; color: #64748b;">Pemohon Material</div>
                <div class="signature-space" style="text-align: center; vertical-align: middle;">
                    @if(optional($mpr->user)->signature && file_exists(public_path('storage/' . $mpr->user->signature)))
                        <img src="{{ public_path('storage/' . $mpr->user->signature) }}" style="max-height: 50px; max-width: 100px; object-fit: contain;">
                    @endif
                </div>
                <div class="signer-name">{{ $mpr->user->name ?? '-' }}</div>
                <div style="font-size: 8.5px; color: #475569;">{{ $mpr->user->job_title ?? 'Karyawan' }}</div>
            </td>

            {{-- 2. DIVERIFIKASI SUPERVISOR --}}
            <td>
                <div class="approval-title">Diverifikasi Oleh</div>
                <div style="font-size: 8.5px; margin-top: 2px; color: #64748b;">Supervisor Operasional</div>
                <div class="signature-space" style="text-align: center; vertical-align: middle;">
                    @if(optional($mpr->supervisor)->signature && file_exists(public_path('storage/' . $mpr->supervisor->signature)))
                        <img src="{{ public_path('storage/' . $mpr->supervisor->signature) }}" style="max-height: 50px; max-width: 100px; object-fit: contain;">
                    @endif
                </div>
                <div class="signer-name">
                    {{ $mpr->supervisor->name ?? '...........................' }}
                </div>
                <div style="font-size: 8.5px; color: #475569;">Supervisor</div>
            </td>

            {{-- 3. DISETUJUI MANAGER --}}
            <td>
                <div class="approval-title">Disetujui Oleh</div>
                <div style="font-size: 8.5px; margin-top: 2px; color: #64748b;">Manager Department</div>
                <div class="signature-space" style="text-align: center; vertical-align: middle;">
                    @if(optional($mpr->manager)->signature && file_exists(public_path('storage/' . $mpr->manager->signature)))
                        <img src="{{ public_path('storage/' . $mpr->manager->signature) }}" style="max-height: 50px; max-width: 100px; object-fit: contain;">
                    @endif
                </div>
                <div class="signer-name">
                    {{ $mpr->manager->name ?? '...........................' }}
                </div>
                <div style="font-size: 8.5px; color: #475569;">General Manager</div>
            </td>

            {{-- 4. PROCUREMENT --}}
            <td>
                <div class="approval-title">Diketahui Oleh</div>
                <div style="font-size: 8.5px; margin-top: 2px; color: #64748b;">Procurement / Finance</div>
                <div class="signature-space"></div>
                <div class="signer-name">...........................</div>
                <div style="font-size: 8.5px; color: #475569;">Procurement Manager</div>
            </td>
        </tr>
    </table>

    <!-- SYARAT & KETENTUAN MPR -->
    <div class="footer-terms">
        <h4>Ketentuan Pengadaan Material Purchase Request (MPR):</h4>
        <ol>
            <li>Permintaan barang/material wajib melampirkan spesifikasi teknis yang jelas agar tidak terjadi kesalahan pemesanan.</li>
            <li>Barang/material yang telah diterima oleh pemohon wajib diperiksa kuantitas dan kualitasnya serta dilaporkan ke bagian Logistik/Gudang.</li>
            <li>Dokumen MPR yang telah disetujui sepenuhnya menjadi dasar penerbitan Purchase Order (PO) oleh bagian Logistik/Pengadaan.</li>
        </ol>
    </div>

    <!-- DOKUMEN PENDUKUNG UTAMA (JIKA ADA UPLOAD BERKAS) -->
    @if($mpr->dokumen_pendukung)
        @php
            $pathFile = storage_path('app/public/' . $mpr->dokumen_pendukung);
            $ekstensi = strtolower(pathinfo($pathFile, PATHINFO_EXTENSION));
        @endphp

        @if(file_exists($pathFile))
            <div style="page-break-before: always; margin-top: 20px;">
                <div style="font-size: 11px; font-weight: bold; color: #1e293b; border-bottom: 2px solid #0284c7; padding-bottom: 5px; margin-bottom: 15px;">
                    LAMPIRAN DOKUMEN PENDUKUNG MPR (NO: {{ $mpr->nomor_mpr }})
                </div>

                @if(in_array($ekstensi, ['jpg', 'jpeg', 'png', 'webp']))
                    <div style="text-align: center; padding: 10px; background-color: #f8fafc; border-radius: 6px;">
                        <img src="{{ public_path('storage/' . $mpr->dokumen_pendukung) }}" style="max-width: 100%; max-height: 550px; object-fit: contain; border-radius: 4px;">
                    </div>
                @elseif($ekstensi === 'pdf')
                    <div style="text-align: center; padding: 20px; background-color: #f8fafc; border-radius: 6px;">
                        <p style="font-size: 11px; font-weight: bold; color: #334155; margin: 0 0 4px 0;">
                            Dokumen Pendukung Berupa File PDF
                        </p>
                        <p style="font-size: 10px; color: #64748b; margin: 0;">
                            Silakan unduh atau tinjau berkas PDF langsung melalui sistem aplikasi web.
                        </p>
                    </div>
                @endif
            </div>
        @endif
    @endif

</body>
</html>