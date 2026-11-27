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
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            const Text(
              'Pilih Pola Jadwal Kerja',
              style: TextStyle(fontWeight: FontWeight.w800, fontSize: 15, color: AppTheme.textPrimary),
            ),
            const SizedBox(height: 4),
            const Text(
              'Jadwal ini digunakan untuk perhitungan kalender kerja dan validasi pengajuan cuti.',
              style: TextStyle(fontSize: 12.5, color: AppTheme.textSecondary),
            ),
            const SizedBox(height: 14),
            _OptionCard(
              title: 'Normal (Senin - Jumat)',
              subtitle: 'Jam operasional reguler 07:00 - 16:00 WIB',
              icon: Icons.business_center_outlined,
              selected: _scheduleType == 'normal',
              onTap: () => setState(() => _scheduleType = 'normal'),
            ),
            const SizedBox(height: 10),
            _OptionCard(
              title: 'Roster Shift Mingguan',
              subtitle: 'Rotasi mingguan Shift Pagi, Malam, dan Minggu OFF',
              icon: Icons.autorenew_rounded,
              selected: _scheduleType == 'roster',
              onTap: () => setState(() => _scheduleType = 'roster'),
            ),
            if (_scheduleType == 'roster') ...[
              const SizedBox(height: 18),
              const Text(
                'Shift Berjalan Minggu Ini',
                style: TextStyle(fontWeight: FontWeight.w800, fontSize: 14, color: AppTheme.textPrimary),
              ),
              const SizedBox(height: 8),
              DropdownButtonFormField<String>(
                initialValue: _shiftChoice,
                decoration: const InputDecoration(
                  labelText: 'Pilih Shift Saat Ini',
                  prefixIcon: Icon(Icons.timelapse_rounded),
                ),
                items: const [
                  DropdownMenuItem(value: 'pagi', child: Text('Shift Pagi (07:00 - 19:00)')),
                  DropdownMenuItem(value: 'malam', child: Text('Shift Malam (19:00 - 07:00)')),
                  DropdownMenuItem(value: 'libur', child: Text('Minggu Libur (OFF)')),
                ],
                onChanged: (v) => setState(() => _shiftChoice = v ?? 'pagi'),
              ),
            ],
            const Spacer(),
            FilledButton.icon(
              onPressed: _saving ? null : _save,
              icon: const Icon(Icons.check_rounded, size: 18),
              label: Text(_saving ? 'Menyimpan...' : 'Simpan Jadwal'),
            ),
            const SizedBox(height: 8),
          ],
        ),
      ),
    );
  }
}

class _OptionCard extends StatelessWidget {
  final String title;
  final String subtitle;
  final IconData icon;
  final bool selected;
  final VoidCallback onTap;

  const _OptionCard({
    required this.title,
    required this.subtitle,
    required this.icon,
    required this.selected,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(16),
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: selected ? AppTheme.primary.withValues(alpha: 0.06) : Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: selected ? AppTheme.primary : AppTheme.border,
            width: selected ? 1.8 : 1,
          ),
        ),
        child: Row(
          children: [
            Container(
              width: 42,
              height: 42,
              decoration: BoxDecoration(
                color: selected ? AppTheme.primary : AppTheme.surfaceMuted,
                borderRadius: BorderRadius.circular(12),
              ),
              child: Icon(icon, color: selected ? Colors.white : AppTheme.textSecondary, size: 21),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: TextStyle(
                      fontWeight: FontWeight.w800,
                      fontSize: 14,
                      color: selected ? AppTheme.primaryDark : AppTheme.textPrimary,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    subtitle,
                    style: const TextStyle(fontSize: 12, color: AppTheme.textSecondary),
                  ),
                ],
              ),
            ),
            Icon(
              selected ? Icons.radio_button_checked_rounded : Icons.radio_button_off_rounded,
              color: selected ? AppTheme.primary : AppTheme.textSecondary,
            ),
          ],
        ),
      ),
    );
  }
}
