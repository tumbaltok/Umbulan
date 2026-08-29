<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode OTP Pemulihan Kata Sandi</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f1f5f9;
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif;
            color: #1e293b;
            -webkit-font-smoothing: antialiased;
        }
        .wrapper {
            width: 100%;
            background-color: #f1f5f9;
            padding: 40px 15px;
        }
        .container {
            max-width: 540px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.03);
            border: 1px solid #e2e8f0;
        }
        .header {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 50%, #075985 100%);
            padding: 32px 30px;
            text-align: center;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            letter-spacing: -0.02em;
        }
        .header p {
            margin: 6px 0 0 0;
            font-size: 12px;
            opacity: 0.85;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        .content {
            padding: 36px 32px;
        }
        .greeting {
            font-size: 16px;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 12px;
        }
        .text {
            font-size: 14px;
            line-height: 1.65;
            color: #475569;
            margin-bottom: 24px;
        }
        .otp-box {
            background: #f8fafc;
            border: 2px dashed #0284c7;
            border-radius: 16px;
            padding: 24px;
            text-align: center;
            margin: 28px 0;
        }
        .otp-label {
            font-size: 11px;
            font-weight: 700;
            color: #0284c7;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            margin-bottom: 8px;
        }
        .otp-code {
            font-family: 'SF Mono', Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;
            font-size: 36px;
            font-weight: 800;
            letter-spacing: 0.25em;
            color: #0f172a;
            margin: 4px 0;
        }
        .otp-expiry {
            font-size: 12px;
            color: #64748b;
            margin-top: 8px;
        }
        .warning-card {
            background-color: #fffbeb;
            border-left: 4px solid #f59e0b;
            padding: 14px 16px;
            border-radius: 8px;
            margin: 24px 0 20px 0;
        }
        .warning-card p {
            margin: 0;
            font-size: 12px;
            line-height: 1.55;
            color: #92400e;
        }
        .footer {
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 24px 30px;
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
            line-height: 1.6;
        }
        .footer a {
            color: #0284c7;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <!-- Header -->
            <div class="header">
                <h1>PT META ADHYA TIRTA UMBULAN</h1>
                <p>Sistem Keamanan & Portal Kepegawaian ERP</p>
            </div>

            <!-- Body Content -->
            <div class="content">
                <div class="greeting">
                    Halo, {{ $user->name }}!
                </div>
                <div class="text">
                    Kami menerima permintaan pengaturan ulang kata sandi untuk akun Anda pada portal ERP META Adhya Tirta Umbulan. Gunakan kode verifikasi (OTP) 6-digit di bawah ini untuk melanjutkan:
                </div>

                <!-- OTP Code Display -->
                <div class="otp-box">
                    <div class="otp-label">Kode Verifikasi (OTP)</div>
                    <div class="otp-code">{{ $otp }}</div>
                    <div class="otp-expiry">
                        ⏳ Berlaku selama <strong>{{ $expiryMinutes }} menit</strong> sejak email ini dikirimkan.
                    </div>
                </div>

                <!-- Security Warning -->
                <div class="warning-card">
                    <p>
                        <strong>Peringatan Keamanan:</strong> Jangan pernah membagikan kode OTP ini kepada siapa pun, termasuk pihak manajemen atau IT Helpdesk. Tim kami tidak akan pernah meminta kode rahasia ini.
                    </p>
                </div>

                <div class="text" style="margin-bottom: 0; font-size: 13px;">
                    Jika Anda tidak pernah meminta perubahan kata sandi ini, akun Anda tetap aman dan Anda dapat mengabaikan email ini atau segera menghubungi IT Helpdesk perusahaan.
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                &copy; {{ date('Y') }} PT META Adhya Tirta Umbulan. Hak Cipta Dilindungi.<br>
                Sistem Otomasi Notifikasi &bull; Email ini dikirimkan secara otomatis, mohon tidak membalas email ini.
            </div>
        </div>
    </div>
</body>
</html>
