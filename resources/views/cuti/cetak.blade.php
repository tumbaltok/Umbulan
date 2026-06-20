<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Formulir Permohonan Cuti/Ijin</title>
    <link rel="icon" type="image/png" href="{{ asset('images/iconfav.png') }}">
    <style>
        @page {
            size: a4 portrait;
            margin: 20px 30px;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.25;
            color: #000;
            margin: 0;
            padding: 0;
        }
        /* Header Kop Surat */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .header-table td {
            vertical-align: middle;
        }
        .company-name {
            font-weight: bold;
            font-size: 13pt;
            letter-spacing: 0.5px;
        }
        .logo-container {
            text-align: right;
        }
        .logo-container img {
            height: 55px; /* Sesuaikan ukuran logo Anda */
        }

        /* Main Border Box (Kotak Utama seperti di Gambar) */
        .main-box {
            border: 1.5px solid #000;
            width: 100%;
            border-collapse: collapse;
        }

        .title-row {
            text-align: center;
            font-weight: bold;
            font-size: 13pt;
            padding: 15px 0 25px 0;
            letter-spacing: 0.5px;
        }

        /* Form Input Styles */
        .form-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .form-table td {
            padding: 3.5px 15px;
            vertical-align: top;
        }
        .dots-line {
            border-bottom: 1px solid #000;
            display: inline-block;
            min-height: 18px;
        }

        /* Checklist Box */
        .checkbox-rect {
            width: 16px;
            height: 16px;
            border: 1.5px solid #000;
            display: inline-block;
            margin-right: 8px;
            vertical-align: middle;
            text-align: center;
            line-height: 14px;
            font-weight: bold;
            font-size: 10pt;
        }

        /* Section Catatan SDM */
        .sdm-section {
            border-top: 1.5px solid #000;
            border-bottom: 1.5px solid #000;
        }
        .sdm-table {
            width: 100%;
            border-collapse: collapse;
        }
        .sdm-table td {
            padding: 8px 15px;
            vertical-align: top;
        }
        .sdm-left {
            width: 25%;
            font-style: italic;
            font-size: 9.5pt;
        }
        .sdm-center {
            width: 45%;
        }
        .sdm-right {
            width: 30%;
            border-left: 1.5px solid #000;
            text-align: center;
            font-size: 10pt;
            position: relative;
        }
        .sdm-line {
            width: 70%;
            margin: 225px auto 0 auto;
            border-bottom: 1px solid #000;
        }

        /* Tanda Tangan Section */
        .ttd-table {
            width: 100%;
            border-collapse: collapse;
        }
        .ttd-table th {
            font-weight: normal;
            font-size: 10.5pt;
            padding: 8px 0;
            text-align: center;
            width: 33.33%;
        }
        .ttd-table td {
            text-align: center;
            padding-top: 75px;
            padding-bottom: 10px;
            font-weight: bold;
        }
        .ttd-border-right {
            border-right: 1.5px solid #000;
        }
        .ttd-line {
            display: inline-block;
            width: 75%;
            border-bottom: 1px solid #000;
            padding-bottom: 2px;
        }

        /* Footer Notes */
        .footer-notes {
            margin-top: 12px;
            font-size: 8.5pt;
            font-style: italic;
            line-height: 1.5;
        }
        .footer-notes table {
            width: 100%;
            border-collapse: collapse;
        }
        .footer-notes td {
            padding: 1px 0;
            vertical-align: top;
        }
        .footer-notes .num {
            width: 20px;
        }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td class="company-name">PT.META ADHYA TIRTA UMBULAN</td>
            <td class="logo-container">
                <img src="{{ public_path('images/iconfav.png') }}" alt="META LOGO">
            </td>
        </tr>
    </table>

    <table class="main-box">
        <tr>
            <td colspan="3" class="title-row">FORMULIR PERMOHONAN CUTI/IJIN</td>
        </tr>

        <tr>
            <td colspan="3" style="padding: 0;">
                <table class="form-table" style="width: 75%;">
                    <tr>
                        <td style="width: 25%;">Nama</td>
                        <td style="width: 3%;">:</td>
                        <td><span class="dots-line" style="width: 100%;">{{ $pengajuan->user->name }}</span></td>
                    </tr>
                    <tr>
                        <td>Bagian</td>
                        <td>:</td>
                        <td><span class="dots-line" style="width: 100%;">{{ $pengajuan->user->job_title }}</span></td>
                    </tr>
                    <tr>
                        <td>TMT Bekerja</td>
                        <td>:</td>
                        <td><span class="dots-line" style="width: 100%;">{{ $pengajuan->user->station->name }}</span></td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr>
            <td colspan="3" style="padding: 0 15px 15px 15px;">

                <div style="margin-bottom: 12px;">
                    <div>
                        <span class="checkbox-rect">@if(str_contains(strtolower($pengajuan->jenisCuti->name_cuti), 'meninggalkan pekerjaan')) v @endif</span>
                        <span class="font-weight: bold;"><strong>Ijin Meninggalkan Pekerjaan</strong></span>
                    </div>
                    <table style="width: 100%; margin-left: 28px; font-size: 10.5pt;">
                        <tr>
                            <td style="width: 18%; padding: 2px 0;">Tanggal</td>
                            <td style="width: 2%;">:</td>
                            <td style="width: 30%;"><span class="dots-line" style="width: 90%;">@if(str_contains(strtolower($pengajuan->jenisCuti->name_cuti), 'meninggalkan pekerjaan')) {{ \Carbon\Carbon::parse($pengajuan->tanggal_mulai)->format('d-m-Y') }} @endif</span></td>
                            <td style="width: 12%;">s.d tanggal</td>
                            <td><span class="dots-line" style="width: 90%;">@if(str_contains(strtolower($pengajuan->jenisCuti->name_cuti), 'meninggalkan pekerjaan')) {{ \Carbon\Carbon::parse($pengajuan->tanggal_selesai)->format('d-m-Y') }} @endif</span></td>
                        </tr>
                        <tr>
                            <td style="padding: 2px 0;">Keperluan</td>
                            <td>:</td>
                            <td colspan="3"><span class="dots-line" style="width: 96%;">@if(str_contains(strtolower($pengajuan->jenisCuti->name_cuti), 'meninggalkan pekerjaan')) {{ $pengajuan->subCuti->nama_sub_cuti }} @endif</span></td>
                        </tr>
                    </table>
                </div>

                <div style="margin-bottom: 12px;">
                    <div>
                        <span class="checkbox-rect">@if(str_contains(strtolower($pengajuan->jenisCuti->name_cuti), 'family visit')) v @endif</span>
                        <span class="font-weight: bold;"><strong>Cuti Family Visit/ Penugasan Sementara per 3 bulan</strong></span>
                    </div>
                    <table style="width: 100%; margin-left: 28px; font-size: 10.5pt;">
                        <tr>
                            <td style="width: 18%; padding: 2px 0;">Mulai tanggal</td>
                            <td style="width: 2%;">:</td>
                            <td style="width: 30%;"><span class="dots-line" style="width: 90%;">@if(str_contains(strtolower($pengajuan->jenisCuti->name_cuti), 'family visit')) {{ \Carbon\Carbon::parse($pengajuan->tanggal_mulai)->format('d-m-Y') }} @endif</span></td>
                            <td style="width: 12%;">s.d tanggal</td>
                            <td><span class="dots-line" style="width: 90%;">@if(str_contains(strtolower($pengajuan->jenisCuti->name_cuti), 'family visit')) {{ \Carbon\Carbon::parse($pengajuan->tanggal_selesai)->format('d-m-Y') }} @endif</span></td>
                        </tr>
                    </table>
                </div>

                <div style="margin-bottom: 12px;">
                    <div>
                        <span class="checkbox-rect">@if(str_contains(strtolower($pengajuan->jenisCuti->name_cuti), 'melahirkan')) v @endif</span>
                        <span class="font-weight: bold;"><strong>Cuti Melahirkan</strong></span>
                    </div>
                    <table style="width: 100%; margin-left: 28px; font-size: 10.5pt;">
                        <tr>
                            <td style="width: 18%; padding: 2px 0;">Mulai tanggal</td>
                            <td style="width: 2%;">:</td>
                            <td style="width: 30%;"><span class="dots-line" style="width: 90%;">@if(str_contains(strtolower($pengajuan->jenisCuti->name_cuti), 'melahirkan')) {{ \Carbon\Carbon::parse($pengajuan->tanggal_mulai)->format('d-m-Y') }} @endif</span></td>
                            <td style="width: 12%;">s.d tanggal</td>
                            <td><span class="dots-line" style="width: 90%;">@if(str_contains(strtolower($pengajuan->jenisCuti->name_cuti), 'melahirkan')) {{ \Carbon\Carbon::parse($pengajuan->tanggal_selesai)->format('d-m-Y') }} @endif</span></td>
                        </tr>
                    </table>
                </div>

                @php
                    // Cek jika tidak masuk kategori ijin/visit/melahirkan, berarti masuk cuti tahunan umum
                    $isCutiUmum = !str_contains(strtolower($pengajuan->jenisCuti->name_cuti), 'meninggalkan pekerjaan') &&
                                  !str_contains(strtolower($pengajuan->jenisCuti->name_cuti), 'family visit') &&
                                  !str_contains(strtolower($pengajuan->jenisCuti->name_cuti), 'melahirkan');
                @endphp
                <div>
                    <div>
                        <span class="checkbox-rect">@if($isCutiUmum) v @endif</span>
                        <span class="font-weight: bold;"><strong>Cuti</strong></span>
                    </div>
                    <table style="width: 100%; margin-left: 28px; font-size: 10.5pt;">
                        <tr>
                            <td style="width: 18%; padding: 2px 0;">Jatah cuti tahun</td>
                            <td style="width: 2%;">:</td>
                            <td colspan="3"><span class="dots-line" style="width: 35%;">@if($isCutiUmum) {{ date('Y') }} @endif</span></td>
                        </tr>
                        <tr>
                            <td style="padding: 2px 0;">Tunjangan Cuti</td>
                            <td>:</td>
                            <td colspan="3">
                                <span class="checkbox-rect"></span> Ya &nbsp;&nbsp;&nbsp;&nbsp;
                                <span class="checkbox-rect"></span> Tidak
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 2px 0;">Mulai cuti</td>
                            <td>:</td>
                            <td style="width: 30%;"><span class="dots-line" style="width: 90%;">@if($isCutiUmum) {{ \Carbon\Carbon::parse($pengajuan->tanggal_mulai)->format('d-m-Y') }} @endif</span></td>
                            <td style="width: 12%;">s.d tanggal</td>
                            <td><span class="dots-line" style="width: 90%;">@if($isCutiUmum) {{ \Carbon\Carbon::parse($pengajuan->tanggal_selesai)->format('d-m-Y') }} @endif</span></td>
                        </tr>
                        <tr>
                            <td style="padding: 2px 0;">Keperluan</td>
                            <td>:</td>
                            <td colspan="3"><span class="dots-line" style="width: 96%;">@if($isCutiUmum) {{ $pengajuan->alasan_cuti }}@endif</span></td>
                        </tr>
                        <tr>
                            <td colspan="5" style="padding-top: 15px; text-align: right; padding-right: 25px;">
                                No Telp yang bisa dihubungi : <span class="dots-line" style="width: 45%;">{{ $pengajuan->user->phone_number}}</span>
                            </td>
                        </tr>
                    </table>
                </div>

            </td>
        </tr>

        <tr class="sdm-section">
            <td colspan="3" style="padding: 0;">
                <table class="sdm-table">
                    <tr>
                        <td class="sdm-left">
                            (diisi oleh bagian SDM)<br><strong>Catatan</strong>
                        </td>
                        <td class="sdm-center">
                            <table style="width: 100%; font-size: 10pt; border-collapse: collapse;">
                                <tr>
                                    <td style="padding: 2px 0; width: 55%;">Sisa Cuti Tahun Lalu</td>
                                    <td style="width: 5%;">:</td>
                                    <td><span class="dots-line" style="width: 60px;"></span> hari</td>
                                </tr>
                                <tr>
                                    <td style="padding: 2px 0;">Sisa Cuti Tahun Berjalan</td>
                                    <td>:</td>
                                    <td><span class="dots-line" style="width: 60px;"></span> hari</td>
                                </tr>
                                <tr>
                                    <td style="padding: 2px 0;">Total Sisa Cuti</td>
                                    <td>:</td>
                                    <td><span class="dots-line" style="width: 60px;"></span> hari</td>
                                </tr>
                                <tr>
                                    <td style="padding: 2px 0;">Cuti yang Akan Diambil</td>
                                    <td>:</td>
                                    <td><span class="dots-line" style="width: 60px; text-align:center; font-weight:bold;">{{ $pengajuan->total_hari }}</span> hari</td>
                                </tr>
                                <tr>
                                    <td style="padding: 2px 0;">Outstanding</td>
                                    <td>:</td>
                                    <td><span class="dots-line" style="width: 60px;"></span> hari</td>
                                </tr>
                            </table>
                        </td>
                        <td class="sdm-right">
                            Bag. SDM
                            <div class="sdm-line"></div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr>
            <td colspan="3" style="padding: 0;">
                <table class="ttd-table">
                    <thead>
                        <tr style="border-bottom: 1.5px solid #000;">
                            <th class="ttd-border-right">Diajukan oleh :</th>
                            <th class="ttd-border-right">Menyetujui :</th>
                            <th>Mengetahui :</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="ttd-border-right">
                                <span class="ttd-line">{{ $pengajuan->user->name }}</span>
                                <div style="font-weight: normal; font-size: 9.5pt; margin-top: 4px;">Karyawan</div>
                            </td>
                            <td class="ttd-border-right">
                                <span class="ttd-line">&nbsp;</span>
                                <div style="font-weight: normal; font-size: 9.5pt; margin-top: 4px;">Atasan langsung</div>
                            </td>
                            <td>
                                <span class="ttd-line">&nbsp;</span>
                                <div style="font-weight: normal; font-size: 9.5pt; margin-top: 4px;">Manager</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    {{-- <div class="footer-notes">
        <table>
            <tr>
                <td class="num">1.</td>
                <td>Permohonan cuti harus diajukan paling lambat 7 (tujuh) hari sebelum hari cuti diambil dan terlebih dahulu mendapat persetujuan dari atasanya.</td>
            </tr>
            <tr>
                <td class="num">2.</td>
                <td>Ijin meninggalkan pekerjaan akan diperhitungkan sesuai ketentuan yang berlaku di Perusahaan.</td>
            </tr>
            <tr>
                <td class="num">3.</td>
                <td>Lembar pertama (asli) untuk Bag SDM dan lembar kedua untuk pekerja ybs.</td>
            </tr>
        </table>
    </div> --}}

</body>
</html>
