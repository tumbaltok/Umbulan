<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cash Advance Request - {{ $car->nomor_car ?? sprintf('%03d', $car->id) }}</title>
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
            margin-bottom: 15px;
        }
        .header-table td {
            vertical-align: top;
            border: 1px solid #000;
            padding: 5px 6px;
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
            background-color: #fcfcfc;
        }
        .info-value {
            width: 25%;
        }
        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
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
            background-color: #f9f9f9;
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

        /* Footer Syarat & Ketentuan */
        .footer-terms {
            margin-top: 15px;
            font-size: 9px;
            color: #444;
            border-top: 1px dashed #666;
            padding-top: 6px;
        }
        .footer-terms h4 {
            margin: 0 0 3px 0;
            font-size: 9.5px;
            text-transform: uppercase;
        }
        .footer-terms ol {
            margin: 0;
            padding-left: 15px;
        }
        .footer-terms li {
            margin-bottom: 1.5px;
        }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td rowspan="2" class="company-logo" style="width: 20%; vertical-align: middle; text-align: center;">
                @if(isset($logoBase64) && $logoBase64)
                    <img src="{{ $logoBase64 }}" style="max-width: 100%; max-height: 48px; object-fit: contain;">
                @else
                    <img src="{{ public_path('images/iconfav.png') }}" style="max-width: 100%; max-height: 48px; object-fit: contain;">
                @endif
            </td>
            <td rowspan="2" style="width: 45%; vertical-align: middle;">
                <h1 class="title-main">Cash Advance Request</h1>
            </td>
            <td style="width: 35%;">
                <strong>Priority:</strong> <span style="color: red; font-weight: bold;">Urgent</span>
            </td>
        </tr>
        <tr>
            <td>
                <strong>Number:</strong>
                @if(!empty($car->nomor_car))
                    {{ $car->nomor_car }}
                @else
                    {{ sprintf('%03d', $car->id) }} / META / PAS / CAR /
                    @php
                        $bulanRomawi = [
                            '01' => 'I', '02' => 'II', '03' => 'III', '04' => 'IV',
                            '05' => 'V', '06' => 'VI', '07' => 'VII', '08' => 'VIII',
                            '09' => 'IX', '10' => 'X', '11' => 'XI', '12' => 'XII'
                        ];
                        echo $bulanRomawi[date('m')];
                    @endphp / {{ date('Y') }}
                @endif
            </td>
        </tr>
    </table>

    <table class="header-table" style="margin-top: -10px;">
        <tr>
            <td class="info-label">Date</td>
            <td class="info-value">
                {{ $car->tanggal_pengajuan ? \Carbon\Carbon::parse($car->tanggal_pengajuan)->format('d-M-y') : \Carbon\Carbon::parse($car->created_at)->format('d-M-y') }}
            </td>
            <td class="info-label">Requester Name</td>
            <td class="info-value">{{ $car->user->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">Department</td>
            <td class="info-value">{{ $car->user->job_title ?? ($car->user->station->name ?? 'Operation') }}</td>
            <td class="info-label">Title</td>
            <td class="info-value">{{ $car->user->role->role_name ?? '-' }}</td>
        </tr>
    </table>

    <p class="font-bold" style="margin-bottom: 4px;">Requested Cash Advance:</p>

    <table class="content-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 36%;">Description of Purchase</th>
                <th style="width: 11%;">Quantity</th>
                <th style="width: 14%;">Price</th>
                <th style="width: 12%;">Ongkir</th>
                <th style="width: 14%;">Total Price</th>
                <th style="width: 8%;">Note</th>
            </tr>
        </thead>
        <tbody>
            @php
                $grandTotalBiaya = 0;
                $totalOngkir = 0;
                $totalRows = $car->details->count();
            @endphp

            @foreach($car->details as $index => $detail)
                @php
                    $grandTotalBiaya += $detail->total_harga;
                    $totalOngkir += ($detail->ongkir ?? 0);
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $detail->nama_barang }}</td>
                    <td class="text-center">{{ $detail->jumlah }} {{ $detail->satuan }}</td>
                    <td class="text-right">Rp{{ number_format($detail->estimasi_harga, 0, ',', '.') }}</td>
                    <td class="text-right">
                        @if(($detail->ongkir ?? 0) > 0)
                            Rp{{ number_format($detail->ongkir, 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-right font-bold">Rp{{ number_format($detail->total_harga, 0, ',', '.') }}</td>
                    @if($loop->first)
                        <td rowspan="{{ $totalRows }}" style="vertical-align: top; font-size: 9.5px; padding: 4px;">
                            {{ $car->alasan_pembelian ?? $car->note_explanation ?? '-' }}
                        </td>
                    @endif
                </tr>
            @endforeach

            <tr>
                <td colspan="5" class="text-right font-bold" style="background-color: #f2f2f2;">Grand Total</td>
                <td class="text-right font-bold" style="background-color: #f2f2f2; color: #000;">
                    Rp{{ number_format($grandTotalBiaya, 0, ',', '.') }}
                </td>
                <td style="background-color: #f2f2f2;"></td>
            </tr>
        </tbody>
    </table>

    <table class="header-table" style="margin-top: 5px;">
        <tr>
            <td style="width: 20%; font-weight: bold; border: 1px solid #000;">Receiving Account</td>
            <td style="border: 1px solid #000; font-weight: 600;">{{ $car->receiving_account ?? '-' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; border: 1px solid #000;">Note & Explanation</td>
            <td style="border: 1px solid #000;">{{ $car->note_explanation ?? $car->alasan_pembelian ?? '-' }}</td>
        </tr>
    </table>

    <table class="approval-table">
        <tr>
            {{-- 1. PEMOHON / REQUESTER --}}
            <td>
                <div class="approval-title">Requested By</div>
                <div style="font-size: 8.5px; margin-top: 2px; color: #666;">Requester</div>
                <div class="signature-space" style="text-align: center; vertical-align: middle;">
                    @if(isset($sigRequester) && $sigRequester)
                        <img src="{{ $sigRequester }}" style="max-height: 52px; max-width: 95px; object-fit: contain;">
                    @endif
                </div>
                <div class="signer-name">{{ $car->user->name ?? '-' }}</div>
                <div>{{ $car->user->role->role_name ?? 'Karyawan' }}</div>
            </td>

            {{-- 2. SUPERVISOR / ATASAN LANGSUNG --}}
            <td>
                <div class="approval-title">Checked By</div>
                <div style="font-size: 8.5px; margin-top: 2px; color: #666;">Supervisor / Atasan 1</div>
                <div class="signature-space" style="text-align: center; vertical-align: middle;">
                    @if(isset($sigApprover1) && $sigApprover1)
                        <img src="{{ $sigApprover1 }}" style="max-height: 52px; max-width: 95px; object-fit: contain;">
                    @endif
                </div>
                <div class="signer-name">{{ $approverLevel1->name ?? '...........................' }}</div>
                <div>{{ $approverLevel1->role->role_name ?? 'Atasan Langsung' }}</div>
            </td>

            {{-- 3. MANAGER / ATASAN TAHAP 2 --}}
            <td>
                <div class="approval-title">Checked & Proceed By</div>
                <div style="font-size: 8.5px; margin-top: 2px; color: #666;">Manager / Atasan 2</div>
                <div class="signature-space" style="text-align: center; vertical-align: middle;">
                    @if(isset($sigApprover2) && $sigApprover2)
                        <img src="{{ $sigApprover2 }}" style="max-height: 52px; max-width: 95px; object-fit: contain;">
                    @endif
                </div>
                <div class="signer-name">{{ $approverLevel2->name ?? '...........................' }}</div>
                <div>{{ $approverLevel2->role->role_name ?? 'Manager' }}</div>
            </td>

            {{-- 4. DIRECTOR --}}
            <td>
                <div class="approval-title">Approved By</div>
                <div style="font-size: 8.5px; margin-top: 2px; color: #666;">Director</div>
                <div class="signature-space" style="text-align: center; vertical-align: middle;">
                    @if(isset($sigDirector) && $sigDirector)
                        <img src="{{ $sigDirector }}" style="max-height: 52px; max-width: 95px; object-fit: contain;">
                    @endif
                </div>
                <div class="signer-name">{{ $director->name ?? '...........................' }}</div>
                <div>{{ $director->role->role_name ?? 'President Director' }}</div>
            </td>
        </tr>
    </table>

    <div class="footer-terms">
        <h4>Syarat & Ketentuan:</h4>
        <ol>
            <li>Requester wajib mempertanggungjawabkan penggunaan C.A.R dengan menyerahkan Laporan penggunaan C.A.R dilengkapi dengan bukti pembayaran/bukti penyerahan atas dana C.A.R yang telah digunakan oleh Requester.</li>
            <li>Requester wajib mengembalikan ke Perusahaan jika realisasi penggunaan dana lebih kecil dari dana C.A.R yang telah diterima oleh Requester.</li>
            <li>Requester harus mengajukan C.A.R tambahan jika realisasi penggunaan dana lebih besar dari dana C.A.R yang telah diterima oleh Requester.</li>
        </ol>
    </div>

{{-- ========================================================================== --}}
{{-- LAMPIRAN DOKUMEN PENDUKUNG (NOTA / PROPOSAL / GAMBAR)                      --}}
{{-- ========================================================================== --}}
    @php
        $hasAttachment = $car->details->contains(fn($d) => !empty($d->dokumen_nota_or_proposal));
    @endphp

    @if($hasAttachment)
    <div style="page-break-before: always; margin-top: 20px;">
        <h3 style="font-size: 13px; font-weight: bold; border-bottom: 2px solid #334155; padding-bottom: 5px; margin-bottom: 15px; color: #1e293b;">
            LAMPIRAN DOKUMEN PENDUKUNG / NOTA (CAR: {{ $car->nomor_car ?? sprintf('%03d', $car->id) }})
        </h3>

        @foreach($car->details as $index => $detail)
            @if($detail->dokumen_nota_or_proposal)
                @php
                    $pathFile = storage_path('app/public/' . $detail->dokumen_nota_or_proposal);
                    $ekstensi = strtolower(pathinfo($pathFile, PATHINFO_EXTENSION));
                @endphp

                @if(file_exists($pathFile))
                    <div style="page-break-inside: avoid; margin-bottom: 20px; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px;">
                        <div style="padding-bottom: 5px; margin-bottom: 8px; border-bottom: 1px dashed #cbd5e1;">
                            <span style="font-size: 11px; font-weight: bold; color: #334155; text-transform: uppercase;">
                                {{ $index + 1 }}. {{ $detail->nama_barang }}
                            </span>
                            <span style="font-size: 10px; color: #64748b;">
                                (Qty: {{ $detail->jumlah }} {{ $detail->satuan }} | Total: Rp{{ number_format($detail->total_harga, 0, ',', '.') }})
                            </span>
                        </div>

                        @if(in_array($ekstensi, ['jpg', 'jpeg', 'png', 'webp', 'gif']))
                            <div style="text-align: center;">
                                <img src="{{ $pathFile }}" style="max-width: 100%; max-height: 380px; object-fit: contain; border-radius: 4px;">
                            </div>
                        @elseif($ekstensi === 'pdf')
                            <div style="text-align: center; padding: 12px; background-color: #f8fafc; border-radius: 6px;">
                                <p style="font-size: 11px; font-weight: bold; color: #334155; margin: 0 0 3px 0;">
                                    Dokumen Pendukung PDF: {{ basename($detail->dokumen_nota_or_proposal) }}
                                </p>
                                <p style="font-size: 9.5px; color: #64748b; margin: 0;">
                                    Berkas PDF terlampir dalam sistem dapat diunduh melalui panel riwayat.
                                </p>
                            </div>
                        @endif
                    </div>
                @endif
            @endif
        @endforeach
    </div>
    @endif
</body>
</html>
