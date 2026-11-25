import 'package:material_ui/material_ui.dart';
import 'package:provider/provider.dart';
import '../../../core/network/api_client.dart';
import '../../../core/theme/app_theme.dart';

class ForgotPasswordScreen extends StatefulWidget {
  const ForgotPasswordScreen({super.key});

  @override
  State<ForgotPasswordScreen> createState() => _ForgotPasswordScreenState();
}

class _ForgotPasswordScreenState extends State<ForgotPasswordScreen> {
  final _identityCtrl = TextEditingController();
  final _otpCtrl = TextEditingController();
  final _passCtrl = TextEditingController();
  final _confirmCtrl = TextEditingController();

  bool _otpSent = false;
  bool _loading = false;

  @override
  void dispose() {
    _identityCtrl.dispose();
    _otpCtrl.dispose();
    _passCtrl.dispose();
    _confirmCtrl.dispose();
    super.dispose();
  }

  Future<void> _sendOtp() async {
    if (_identityCtrl.text.trim().isEmpty) return;
    setState(() => _loading = true);
    try {
      final res = await context.read<ApiClient>().post(
        '/forgot-password/send-otp',
        data: {'identity': _identityCtrl.text.trim()},
      );
      setState(() => _otpSent = true);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(res['message']?.toString() ?? 'OTP dikirim ke email Anda.')),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(e.toString()), backgroundColor: AppTheme.danger),
        );
      }
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _resetPassword() async {
    if (_otpCtrl.text.trim().length != 6 || _passCtrl.text.length < 8) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Isi OTP 6 digit dan kata sandi baru minimal 8 karakter.')),
      );
      return;
    }

    setState(() => _loading = true);
    try {
      final res = await context.read<ApiClient>().post(
        '/forgot-password/reset',
        data: {
          'identity': _identityCtrl.text.trim(),
          'otp': _otpCtrl.text.trim(),
          'password': _passCtrl.text,
          'password_confirmation': _confirmCtrl.text,
        },
      );
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(res['message']?.toString() ?? 'Kata sandi diperbarui.')),
        );
        Navigator.pop(context);
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(e.toString()), backgroundColor: AppTheme.danger),
        );
      }
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Pemulihan Kata Sandi')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            const Text(
              'Masukkan Email atau NIP akun Anda. Kode OTP 6 digit akan dikirimkan melalui Email Kepegawaian.',
              style: TextStyle(color: AppTheme.textSecondary),
            ),
            const SizedBox(height: 16),
            TextField(
              controller: _identityCtrl,
              enabled: !_otpSent,
              decoration: const InputDecoration(
                labelText: 'Email atau NIP',
                prefixIcon: Icon(Icons.person_search_outlined),
              ),
            ),
            const SizedBox(height: 16),
            if (!_otpSent)
              FilledButton(
                onPressed: _loading ? null : _sendOtp,
                child: _loading
                    ? const SizedBox(
                        width: 22,
                        height: 22,
                        child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                      )
                    : const Text('Kirim Kode OTP ke Email'),
              )
            else ...[
              TextField(
                controller: _otpCtrl,
                keyboardType: TextInputType.number,
                maxLength: 6,
                decoration: const InputDecoration(
                  labelText: 'Kode OTP 6 Digit',
                  prefixIcon: Icon(Icons.pin_outlined),
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: _passCtrl,
                obscureText: true,
                decoration: const InputDecoration(
                  labelText: 'Kata Sandi Baru (Min. 8 karakter)',
                  prefixIcon: Icon(Icons.lock_outline),
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: _confirmCtrl,
                obscureText: true,
                decoration: const InputDecoration(
                  labelText: 'Konfirmasi Kata Sandi Baru',
                  prefixIcon: Icon(Icons.lock_reset_outlined),
                ),
              ),
              const SizedBox(height: 20),
              FilledButton(
                onPressed: _loading ? null : _resetPassword,
                child: const Text('Simpan Kata Sandi Baru'),
              ),
              TextButton(
                onPressed: _loading ? null : _sendOtp,
                child: const Text('Kirim Ulang OTP'),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
