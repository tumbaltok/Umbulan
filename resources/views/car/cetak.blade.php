<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cash Advance Request - {{ $car->id }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .header-table td {
            vertical-align: top;
            border: 1px solid #000;
            padding: 6px;
        }
        .title-main {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
            margin: 0;
        }
        .company-logo {
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 2px;
            text-align: center;
        }
        .info-label {
            font-weight: bold;
            width: 25%;
        }
        .info-value {
            width: 25%;
        }
        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .content-table th {
            background-color: #f2f2f2;
            border: 1px solid #000;
            font-weight: bold;
            text-align: center;
            padding: 6px;
        }
        .content-table td {
            border: 1px solid #000;
            padding: 5px 6px;
            vertical-align: middle;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }

        /* Kolom Tanda Tangan / Approval */
        .approval-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .approval-table td {
            border: 1px solid #000;
            width: 20%;
            text-align: center;
            vertical-align: top;
            padding: 4px;
            font-size: 10px;
        }
        .approval-title {
            font-weight: bold;
            background-color: #f9f9f9;
            border-bottom: 1px solid #000;
            padding: 4px 0;
        }
        .signature-space {
            height: 65px;
        }
        .signer-name {
            font-weight: bold;
            text-decoration: underline;
        }

        /* Footer Syarat & Ketentuan */
        .footer-terms {
            margin-top: 25px;
            font-size: 9.5px;
            color: #444;
            border-top: 1px dashed #666;
            padding-top: 8px;
        }
        .footer-terms h4 {
            margin: 0 0 4px 0;
            font-size: 10px;
            text-transform: uppercase;
        }
        .footer-terms ol {
            margin: 0;
            padding-left: 15px;
        }
        .footer-terms li {
            margin-bottom: 2px;
        }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td rowspan="2" class="company-logo" style="width: 20%; vertical-align: middle; text-align: center;">
                <img src="{{ public_path('images/iconfav.png') }}" style="max-width: 100%; max-height: 50px; object-fit: contain;">
            </td>
            <td rowspan="2" style="width: 45%; vertical-align: middle;">
                <h1 class="title-main">Cash Advance Request</h1>
            </td>
            <td style="width: 35%;">
                <strong>Priority:</strong> <span style="color: red;">Urgent</span>
            </td>
        </tr>
        <tr>
            <td>
                <strong>Number:</strong> {{ sprintf('%03d', $car->id) }} / META / PAS / CAR /
                @php $bulanRomawi = [
                        '01' => 'I', '02' => 'II', '03' => 'III', '04' => 'IV',
                        '05' => 'V', '06' => 'VI', '07' => 'VII', '08' => 'VIII',
                        '09' => 'IX', '10' => 'X', '11' => 'XI', '12' => 'XII'
                    ];
                    echo $bulanRomawi[date('m')];
                @endphp / {{ date('Y') }}
            </td>
        </tr>
    </table>

    <table class="header-table" style="margin-top: -11px;">
        <tr>
            <td class="info-label">Date</td>
            <td class="info-value">{{ \Carbon\Carbon::parse($car->created_at)->format('d-M-y') }}</td>
            <td class="info-label">Requester Name</td>
            <td class="info-value">{{ $car->user->name ?? 'Vivin Sintia Indriani' }}</td>
        </tr>
        <tr>
            <td class="info-label">Department</td>
            <td class="info-value">{{ $car->user->job_title ?? '-' }}</td>
            <td class="info-label">Title</td>
            <td class="info-value">{{ $car->user->role->role_name ?? '-' }}</td>
        </tr>
    </table>

    <p class="font-bold" style="margin-bottom: 5px;">Requested Cash Advance:</p>

    <table class="content-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 45%;">Description of Purchase</th>
                <th style="width: 10%;">Quantity</th>
                <th style="width: 15%;">Price</th>
                <th style="width: 15%;">Total Price</th>
                <th style="width: 10%;">Note</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @foreach($car->details as $index => $detail)
                @php $grandTotal += $detail->total_harga; @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $detail->nama_barang }}</td>
                    <td class="text-center">{{ $detail->jumlah }}</td>
                    <td class="text-right">Rp{{ number_format($detail->estimasi_harga, 0, ',', '.') }}</td>
                    <td class="text-right">Rp{{ number_format($detail->total_harga, 0, ',', '.') }}</td>
                    @if($loop->first)
                        <td rowspan="{{ $car->details->count() }}" style="vertical-align: top;">
                            {{ $car->alasan_pembelian }}
                        </td>
                    @endif
                </tr>
            @endforeach

            <tr>
                <td colspan="4" class="text-right font-bold" style="background-color: #f9f9f9;">Total</td>
                <td class="text-right font-bold" style="background-color: #f9f9f9;">Rp{{ number_format($grandTotal, 0, ',', '.') }}</td>
                <td style="background-color: #f9f9f9;"></td>
            </tr>
        </tbody>
    </table>

    <table class="header-table" style="margin-top: 10px;">
        <tr>
            <td style="width: 20%; font-weight: bold; border: 1px solid #000;">Receiving Account</td>
            <td style="border: 1px solid #000;">{{ $car->receiving_account ?? '-' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; border: 1px solid #000;">Note & Explanation</td>
            <td style="border: 1px solid #000;">{{ $car->note_explanation ?? '-' }}</td>
        </tr>
    </table>

    <table class="approval-table">
        <tr>
            <td>
                <div class="approval-title">Requested By</div>
                <div style="font-size: 9px; margin-top: 2px;">Requester</div>
                <div class="signature-space"></div>
                <div class="signer-name">{{ $car->user->name }}</div>
                <div>Staff</div>
            </td>
            <td>
                <div class="approval-title">Checked By</div>
                <div style="font-size: 9px; margin-top: 2px;">Supervisor</div>
                <div class="signature-space"></div>
                <div class="signer-name">{{ 'Nama SPV' }}</div>
                <div>Supervisor Operasional</div>
            </td>
            <td>
                <div class="approval-title">Checked & Proceed By</div>
                <div style="font-size: 9px; margin-top: 2px;">Finance</div>
                <div class="signature-space"></div>
                <div class="signer-name">{{'Nama Manager'}}</div>
                <div>General Manager</div>
            </td>
            <td>
                <div class="approval-title">Approved By</div>
                <div style="font-size: 9px; margin-top: 2px;">Director</div>
                <div class="signature-space"></div>
                <div class="signer-name">...........................</div>
                <div>President Director</div>
            </td>
        </tr>
    </table>

    <div class="footer-terms">
        <h4>Syarat & Ketentuan:</h4>
        <ol>
            <li>Requester wajib mempertanggung jawabkan penggunaan C.A.R dengan menyerahkan Laporan penggunaan C.A.R dilengkapi dengan bukti pembayaran/bukti penyerahan atas dana C.A.R yang telah digunakan oleh Requester.</li>
            <li>Requester wajib mengembalikan ke Perusahaan jika realisasi penggunaan dana lebih kecil dari dana C.A.R yang telah diterima oleh Requester.</li>
            <li>Requester harus mengajukan C.A.R tambahan jika realisasi penggunaan dana lebih besar dari dana C.A.R yang telah diterima oleh Requester.</li>
        </ol>
    </div>

{{-- ========================================================================== --}}
{{-- BAGIAN BARU: DOKUMEN PENDUKUNG (NOTA / PROPOSAL)                           --}}
{{-- ========================================================================== --}}
    <div style="margin-top: 30px;">
        @foreach($car->details as $index => $detail)
            @if($detail->dokumen_nota_or_proposal)
                @php
                    $pathFile = storage_path('app/public/' . $detail->dokumen_nota_or_proposal);
                    $ekstensi = strtolower(pathinfo($pathFile, PATHINFO_EXTENSION));
                @endphp

                @if(file_exists($pathFile))
                    {{-- Menggunakan page-break-inside: avoid agar hemat kertas (border luar dihapus) --}}
                    <div style="page-break-inside: avoid; margin-bottom: 25px; padding: 15px; border-radius: 8px;">

                        {{-- Header kecil informasi lampiran (border-bottom dihapus) --}}
                        <div style="padding-bottom: 5px; margin-bottom: 10px;">
                            <span style="font-size: 11px; font-weight: bold; color: #334155; font-family: sans-serif; text-transform: uppercase;">
                                {{ $index + 1 }}. {{ $detail->nama_barang }}
                            </span>
                            <span style="font-size: 11px; color: #64748b; font-family: sans-serif;">
                                ({{ $detail->jumlah }} Qty)
                            </span>
                        </div>

                        {{-- Render jika file berupa Gambar --}}
                        @if(in_array($ekstensi, ['jpg', 'jpeg', 'png', 'webp', 'gif']))
                            <div style="text-align: center;">
                                {{-- Ukuran gambar disesuaikan (max-height dikecilkan menjadi 280px agar muat banyak) --}}
                                <img src="{{ public_path('storage/' . $detail->dokumen_nota_or_proposal) }}"
                                    style="max-width: 100%; max-height: 280px; object-fit: contain; border-radius: 4px;">
                            </div>

                        {{-- Render informasi jika file berupa PDF --}}
                        @elseif($ekstensi === 'pdf')
                            {{-- Menggunakan background netral tipis tanpa border pembungkus --}}
                            <div style="text-align: center; padding: 15px; background-color: #f8fafc; border-radius: 6px;">
                                <p style="font-family: sans-serif; font-size: 11px; font-weight: bold; color: #334155; margin: 0 0 4px 0;">
                                    Dokumen Pendukung PDF Terpisah
                                </p>
                                <p style="font-family: sans-serif; font-size: 10px; color: #64748b; margin: 0;">
                                    Silakan unduh berkas melalui tautan sistem riwayat aplikasi.
                                </p>
                            </div>
                        @endif

                    </div>
                @endif
            @endif
        @endforeach
    </div>
</body>
</html>
