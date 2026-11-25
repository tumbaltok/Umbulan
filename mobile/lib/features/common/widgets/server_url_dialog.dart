import 'package:material_ui/material_ui.dart';
import 'package:provider/provider.dart';
import '../../../core/config/app_config.dart';
import '../../../core/providers/auth_provider.dart';

Future<void> showServerConfigDialog(BuildContext context) async {
  final auth = context.read<AuthProvider>();
  final ctrl = TextEditingController(text: auth.baseUrl);

  final saved = await showDialog<bool>(
    context: context,
    builder: (ctx) => AlertDialog(
      title: const Text('Pengaturan URL Server API'),
      content: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Masukkan alamat base URL backend Laravel (/api).',
            style: TextStyle(fontSize: 13),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: ctrl,
            keyboardType: TextInputType.url,
            decoration: const InputDecoration(
              labelText: 'API Base URL',
              hintText: 'http://192.168.1.10/api',
            ),
          ),
          const SizedBox(height: 10),
          Wrap(
            spacing: 8,
            children: [
              ActionChip(
                label: const Text('Emulator (10.0.2.2)'),
                onPressed: () => ctrl.text = 'http://10.0.2.2:8000/api',
              ),
              ActionChip(
                label: const Text('Default Platform'),
                onPressed: () => ctrl.text = AppConfig.defaultApiBaseUrl,
              ),
            ],
          ),
        ],
      ),
      actions: [
        TextButton(
          onPressed: () => Navigator.pop(ctx, false),
          child: const Text('Batal'),
        ),
        FilledButton(
          onPressed: () => Navigator.pop(ctx, true),
          child: const Text('Simpan'),
        ),
      ],
    ),
  );

  if (saved == true && ctrl.text.trim().isNotEmpty) {
    await auth.updateServerBaseUrl(ctrl.text.trim());
    if (context.mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Server API diatur ke ${auth.baseUrl}')),
      );
    }
  }
}
