import 'package:material_ui/material_ui.dart';
import 'package:provider/provider.dart';
import '../../../core/providers/auth_provider.dart';
import '../../../core/theme/app_theme.dart';

class EmailOtpScreen extends StatefulWidget {
  const EmailOtpScreen({super.key});

  @override
  State<EmailOtpScreen> createState() => _EmailOtpScreenState();
}

class _EmailOtpScreenState extends State<EmailOtpScreen> {
  final _otpCtrl = TextEditingController();
  bool _resending = false;

  @override
  void dispose() {
    _otpCtrl.dispose();
    super.dispose();
  }

  Future<void> _verify() async {
    final otp = _otpCtrl.text.trim();
    if (otp.length != 6) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Masukkan 6 digit kode OTP.')),
      );
      return;
    }

    final auth = context.read<AuthProvider>();
    final ok = await auth.verifyEmailOtp(otp);
    if (!mounted) return;
    if (ok) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Email berhasil diverifikasi!')),
      );
      Navigator.pop(context, true);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(auth.error ?? 'Kode OTP salah.'),
          backgroundColor: AppTheme.danger,
        ),
      );
    }
  }

  Future<void> _resend() async {
    setState(() => _resending = true);
    try {
      final msg = await context.read<AuthProvider>().resendEmailOtp();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(msg ?? 'Kode OTP dikirim ulang.')),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(e.toString()), backgroundColor: AppTheme.danger),
        );
      }
    } finally {
      if (mounted) setState(() => _resending = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();

    return Scaffold(
      appBar: AppBar(title: const Text('Verifikasi Email')),
      body: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            const Icon(Icons.mark_email_unread_outlined, size: 56, color: AppTheme.primary),
            const SizedBox(height: 16),
            Text(
              'Masukkan 6 digit kode OTP yang dikirim ke ${auth.user?.email ?? ''}',
              textAlign: TextAlign.center,
              style: const TextStyle(color: AppTheme.textSecondary),
            ),
            const SizedBox(height: 24),
            TextField(
              controller: _otpCtrl,
              keyboardType: TextInputType.number,
              maxLength: 6,
              textAlign: TextAlign.center,
              style: const TextStyle(fontSize: 22, letterSpacing: 8, fontWeight: FontWeight.w700),
              decoration: const InputDecoration(hintText: '000000'),
            ),
            const SizedBox(height: 18),
            FilledButton(
              onPressed: auth.isBusy ? null : _verify,
              child: const Text('Verifikasi Email'),
            ),
            const SizedBox(height: 10),
            TextButton(
              onPressed: _resending ? null : _resend,
              child: Text(_resending ? 'Mengirim...' : 'Kirim Ulang Kode OTP'),
            ),
          ],
        ),
      ),
    );
  }
}
