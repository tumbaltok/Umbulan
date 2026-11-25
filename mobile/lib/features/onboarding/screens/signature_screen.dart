import 'dart:convert';
import 'package:material_ui/material_ui.dart';
import 'package:provider/provider.dart';
import 'package:signature/signature.dart';
import '../../../core/network/api_client.dart';
import '../../../core/providers/auth_provider.dart';
import '../../../core/theme/app_theme.dart';

class SignatureScreen extends StatefulWidget {
  const SignatureScreen({super.key});

  @override
  State<SignatureScreen> createState() => _SignatureScreenState();
}

class _SignatureScreenState extends State<SignatureScreen> {
  final SignatureController _controller = SignatureController(
    penStrokeWidth: 3,
    penColor: AppTheme.textPrimary,
    exportBackgroundColor: Colors.transparent,
  );

  bool _saving = false;

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  Future<void> _save() async {
    if (_controller.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Silakan buat goresan tanda tangan terlebih dahulu.')),
      );
      return;
    }

    setState(() => _saving = true);
    final api = context.read<ApiClient>();
    final auth = context.read<AuthProvider>();
    final messenger = ScaffoldMessenger.of(context);
    final navigator = Navigator.of(context);
    try {
      final pngBytes = await _controller.toPngBytes();
      if (pngBytes == null) throw Exception('Gagal mengonversi gambar tanda tangan.');

      final dataUri = 'data:image/png;base64,${base64Encode(pngBytes)}';
      await api.post(
        '/user/signature',
        data: {'signature_base64': dataUri},
      );
      await auth.refreshMe();

      if (mounted) {
        messenger.showSnackBar(
          const SnackBar(content: Text('Tanda tangan digital berhasil disimpan.')),
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
      if (mounted) setState(() => _saving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Tanda Tangan Digital')),
      body: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            const Text(
              'Gunakan jari Anda pada area di bawah untuk membuat tanda tangan resmi pengajuan.',
              style: TextStyle(color: AppTheme.textSecondary),
            ),
            const SizedBox(height: 16),
            Expanded(
              child: Container(
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: AppTheme.border, width: 1.5),
                ),
                child: ClipRRect(
                  borderRadius: BorderRadius.circular(16),
                  child: Signature(
                    controller: _controller,
                    backgroundColor: Colors.white,
                  ),
                ),
              ),
            ),
            const SizedBox(height: 16),
            Row(
              children: [
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: _saving ? null : () => _controller.clear(),
                    icon: const Icon(Icons.refresh),
                    label: const Text('Hapus'),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  flex: 2,
                  child: FilledButton.icon(
                    onPressed: _saving ? null : _save,
                    icon: const Icon(Icons.check),
                    label: Text(_saving ? 'Menyimpan...' : 'Simpan Tanda Tangan'),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
