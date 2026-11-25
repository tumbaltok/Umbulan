import 'package:material_ui/material_ui.dart';
import 'package:provider/provider.dart';
import '../../../core/network/api_client.dart';
import '../../../core/providers/auth_provider.dart';
import '../../../core/theme/app_theme.dart';

class ScheduleScreen extends StatefulWidget {
  const ScheduleScreen({super.key});

  @override
  State<ScheduleScreen> createState() => _ScheduleScreenState();
}

class _ScheduleScreenState extends State<ScheduleScreen> {
  String _scheduleType = 'normal';
  String _shiftChoice = 'pagi';
  bool _saving = false;

  @override
  void initState() {
    super.initState();
    final current = context.read<AuthProvider>().user?.scheduleType;
    if (current == 'roster') _scheduleType = 'roster';
  }

  Future<void> _save() async {
    setState(() => _saving = true);
    final api = context.read<ApiClient>();
    final auth = context.read<AuthProvider>();
    final messenger = ScaffoldMessenger.of(context);
    final navigator = Navigator.of(context);
    try {
      await api.put('/user/schedule', data: {
        'schedule_type': _scheduleType,
        if (_scheduleType == 'roster') 'current_shift_choice': _shiftChoice,
      });
      await auth.refreshMe();
      if (mounted) {
        messenger.showSnackBar(
          const SnackBar(content: Text('Jadwal kerja berhasil diperbarui.')),
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
      appBar: AppBar(title: const Text('Pengaturan Jadwal Kerja')),
      body: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            DropdownButtonFormField<String>(
              initialValue: _scheduleType,
              decoration: const InputDecoration(labelText: 'Tipe Jadwal Kerja'),
              items: const [
                DropdownMenuItem(value: 'normal', child: Text('Normal (Senin - Jumat, 07:00 - 16:00)')),
                DropdownMenuItem(value: 'roster', child: Text('Roster Shift Mingguan')),
              ],
              onChanged: (v) => setState(() => _scheduleType = v ?? 'normal'),
            ),
            if (_scheduleType == 'roster') ...[
              const SizedBox(height: 16),
              DropdownButtonFormField<String>(
                initialValue: _shiftChoice,
                decoration: const InputDecoration(labelText: 'Shift Saat Ini'),
                items: const [
                  DropdownMenuItem(value: 'pagi', child: Text('Shift Pagi (07:00 - 19:00)')),
                  DropdownMenuItem(value: 'malam', child: Text('Shift Malam (19:00 - 07:00)')),
                  DropdownMenuItem(value: 'libur', child: Text('Minggu Libur (OFF)')),
                ],
                onChanged: (v) => setState(() => _shiftChoice = v ?? 'pagi'),
              ),
            ],
            const Spacer(),
            FilledButton(
              onPressed: _saving ? null : _save,
              child: Text(_saving ? 'Menyimpan...' : 'Simpan Jadwal'),
            ),
          ],
        ),
      ),
    );
  }
}
