<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Material/Service Procurement Request - {{ $mpr->nomor_mpr }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 15mm 12mm 15mm;
        }

        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 9.5pt;
            line-height: 1.3;
            color: #000;
            margin: 0;
            padding: 0;
        }

        /* Top Header Table */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .header-table td {
            vertical-align: middle;
            border: none;
            padding: 0;
        }
        .doc-title {
            font-size: 13.5pt;
            font-weight: bold;
            text-align: center;
            letter-spacing: 0.3px;
        }

        /* Metadata Table */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 9.5pt;
        }
        .meta-table td {
            border: none;
            padding: 2.5px 0;
            vertical-align: top;
        }

        /* Section Title */
        .section-title {
            text-align: center;
            font-weight: bold;
            font-size: 10.5pt;
            margin-bottom: 6px;
            letter-spacing: 0.2px;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
        }
        .items-table th {
            border: 1px solid #000;
            font-size: 9.5pt;
            font-weight: bold;
            text-align: center;
            padding: 6px 4px;
            vertical-align: middle;
        }
        .items-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            vertical-align: top;
            font-size: 9.5pt;
        }

        /* Delivery Point */
        .delivery-point {
            margin-top: 10px;
            margin-bottom: 8px;
            font-size: 9.5pt;
        }

        /* Note & Explanation Box */
        .note-box {
            border: 1px solid #000;
            padding: 6px 10px;
            font-size: 9pt;
            margin-bottom: 14px;
            line-height: 1.35;
        }

        /* Signature Outer Table */
        .signature-table {
            width: 100%;
            border: 1px solid #000;
            border-collapse: collapse;
        }
        .signature-table td {
            vertical-align: top;
        }
        .sub-header {
            font-size: 8.5pt;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .role-title {
            font-size: 8.5pt;
            margin-bottom: 2px;
        }
        .signer-name {
            font-size: 8.5pt;
            white-space: nowrap;
        }
        .sig-space {
            height: 48px;
            text-align: center;
            vertical-align: middle;
        }
        .sig-img {
            max-height: 44px;
            max-width: 110px;
            object-fit: contain;
        }
    </style>
</head>
<body>

    {{-- 1. Header dan Logo Dokumen --}}
    <table class="header-table">
        <tr>
            <td style="width: 82%; text-align: center; padding-left: 18%;">
                <div class="doc-title">Material/Service Procurement Request</div>
            </td>
            <td style="width: 18%; text-align: right;">
                @if(!empty($logoBase64))
                    <img src="{{ $logoBase64 }}" style="height: 54px; width: auto;" alt="META">
                @else
                    <div style="font-size: 14pt; font-weight: bold; color: #0284c7;">META</div>
                @endif
            </td>
        </tr>
    </table>

    {{-- 2. Metadata Pemohon MPR --}}
    <table class="meta-table">
        <tr>
            <td style="width: 17%;">Number</td>
            <td style="width: 3%;">:</td>
            <td style="width: 44%;">{{ $mpr->nomor_mpr }}</td>
            <td style="width: 12%;">Priority</td>
            <td style="width: 3%;">:</td>
            <td style="width: 21%;">{{ $mpr->priority ?? 'Normal' }}</td>
        </tr>
        <tr>
            <td>Date</td>
            <td>:</td>
            <td>{{ \Carbon\Carbon::parse($mpr->tanggal_pengajuan)->format('d-M-y') }}</td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>Requester Name</td>
            <td>:</td>
            <td>{{ $mpr->user->name ?? '-' }}</td>
            <td>Title</td>
            <td>:</td>
            <td>{{ $requesterRole }}</td>
        </tr>
        <tr>
            <td>Department</td>
            <td>:</td>
            <td>{{ $departmentName }}</td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </table>

    {{-- 3. Judul Seksi Detail Barang --}}
    <div class="section-title">Requested Material/Service</div>

    {{-- 4. Tabel Rincian Material/Jasa --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 6%;">No</th>
                <th style="width: 34%;">Item</th>
                <th style="width: 45%;">Description/Specification/Part<br>number</th>
                <th style="width: 15%;">Quantity</th>
            </tr>
        </thead>
        <tbody>
            @foreach($mpr->items as $index => $item)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $item->nama_barang }}</td>
                    <td>{{ $item->keterangan_item ?? '-' }}</td>
                    <td style="text-align: center;">
                        {{ $item->jumlah }}
                    </td>
                </tr>
            @endforeach

            @php
                $itemCount = count($mpr->items);
                $fillerHeight = max(0, 240 - ($itemCount * 45));
            @endphp
            @if($fillerHeight > 0)
                <tr>
                    <td style="height: {{ $fillerHeight }}px; border: 1px solid #000;"></td>
                    <td style="border: 1px solid #000;"></td>
                    <td style="border: 1px solid #000;"></td>
                    <td style="border: 1px solid #000;"></td>
                </tr>
            @endif
        </tbody>
    </table>

    {{-- 5. Titik Pengiriman (Delivery Point) --}}
    <div class="delivery-point">
        Delivery Point &nbsp;&nbsp;&nbsp;&nbsp; : &nbsp;&nbsp; {{ $deliveryPoint }}
    </div>

    {{-- 6. Catatan dan Penjelasan Tambahan --}}
    <div class="note-box">
        <div style="font-weight: bold; margin-bottom: 2px;">Note & Explanation :</div>
        @php
            $rawNotes = trim($mpr->keperluan_urgensi ?? '');
            $lines = preg_split('/\r\n|\r|\n/', $rawNotes);
        @endphp
        @foreach($lines as $line)
            @if(trim($line))
                <div>{{ str_starts_with(trim($line), '-') ? trim($line) : '- ' . trim($line) }}</div>
            @endif
        @endforeach
        @if(!empty($latestMprDate))
            <div>- latest MPR Issued date : {{ $latestMprDate }}</div>
        @endif
    </div>

    {{-- 7. Matriks Tanda Tangan Persetujuan --}}
    <table class="signature-table">
        {{-- Baris Atas: Requested By & Checked By --}}
        <tr>
            {{-- Requested By (Kiri) --}}
            <td style="width: 55%; border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 4px 8px 6px 8px;">
                <div class="sub-header">Requested By</div>
                <table style="width: 100%; border: none; border-collapse: collapse;">
                    <tr>
                        <td style="width: 48%; text-align: center; border: none; padding: 0;">
                            <div class="role-title">Requester</div>
                            <div class="sig-space">
                                @if(!empty($requesterSignature))
                                    <img src="{{ $requesterSignature }}" class="sig-img">
                                @endif
                            </div>
                            <div class="signer-name">( {{ $mpr->user->name ?? 'Requester' }} )</div>
                        </td>
                        <td style="width: 4%; border: none;"></td>
                        <td style="width: 48%; text-align: center; border: none; padding: 0;">
                            <div class="role-title">Operation Manager</div>
                            <div class="sig-space">
                                @if(!empty($operationManagerSignature))
                                    <img src="{{ $operationManagerSignature }}" class="sig-img">
                                @endif
                            </div>
                            <div class="signer-name">( {{ $operationManagerName }} )</div>
                        </td>
                    </tr>
                </table>
            </td>

            {{-- Checked & Proceed By (Kanan) --}}
            <td style="width: 45%; border-bottom: 1px solid #000; padding: 4px 8px 6px 8px;">
                <div class="sub-header">Checked & Proceed By</div>
                <div style="text-align: center;">
                    <div class="role-title">Procurement</div>
                    <div class="sig-space">
                        @if(!empty($procurementSignature))
                            <img src="{{ $procurementSignature }}" class="sig-img">
                        @endif
                    </div>
                    <div class="signer-name">
                        ( {{ !empty($procurementSignature) ? $procurementName : '...........................' }} )
                    </div>
                </div>
            </td>
        </tr>

        {{-- Baris Bawah: Approved By Direksi --}}
        <tr>
            <td colspan="2" style="padding: 4px 8px 6px 8px;">
                <div class="sub-header">Approved By</div>
                <table style="width: 100%; border: none; border-collapse: collapse;">
                    <tr>
                        <td style="width: 48%; text-align: center; border: none; padding: 0;">
                            <div class="role-title">Director</div>
                            <div class="sig-space">
                                @if(!empty($directorSignature))
                                    <img src="{{ $directorSignature }}" class="sig-img">
                                @endif
                            </div>
                            <div class="signer-name">
                                ( {{ !empty($directorSignature) ? $directorName : $directorName }} )
                            </div>
                        </td>
                        <td style="width: 4%; border: none;"></td>
                        <td style="width: 48%; text-align: center; border: none; padding: 0;">
                            <div class="role-title">Director/President Director</div>
                            <div class="sig-space">
                                @if(!empty($presidentDirectorSignature))
                                    <img src="{{ $presidentDirectorSignature }}" class="sig-img">
                                @endif
                            </div>
                            <div class="signer-name">
                                ( {{ !empty($presidentDirectorSignature) ? $presidentDirectorName : '...........................' }} )
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- 8. Lampiran Dokumen Pendukung --}}
    @if($mpr->dokumen_pendukung)
        @php
            $pathFile = storage_path('app/public/' . $mpr->dokumen_pendukung);
            $ekstensi = strtolower(pathinfo($pathFile, PATHINFO_EXTENSION));
        @endphp

        @if(file_exists($pathFile))
            <div style="page-break-before: always; margin-top: 15px;">
                <div style="font-size: 10pt; font-weight: bold; color: #000; border-bottom: 2px solid #000; padding-bottom: 5px; margin-bottom: 12px;">
                    LAMPIRAN DOKUMEN PENDUKUNG MPR (NO: {{ $mpr->nomor_mpr }})
                </div>

                @if(in_array($ekstensi, ['jpg', 'jpeg', 'png', 'webp']))
                    <div style="text-align: center; padding: 8px;">
                        <img src="{{ public_path('storage/' . $mpr->dokumen_pendukung) }}" style="max-width: 100%; max-height: 520px; object-fit: contain;">
                    </div>
                @elseif($ekstensi === 'pdf')
                    <div style="text-align: center; padding: 15px; background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 4px;">
                        <p style="font-size: 10pt; font-weight: bold; color: #1e293b; margin: 0 0 4px 0;">
                            Dokumen Pendukung Berupa Berkas PDF
                        </p>
                        <p style="font-size: 9pt; color: #64748b; margin: 0;">
                            Silakan tinjau berkas PDF lampiran langsung melalui sistem aplikasi web.
                        </p>
                    </div>
                @endif
            </div>
        @endif
    @endif

</body>
</html>
