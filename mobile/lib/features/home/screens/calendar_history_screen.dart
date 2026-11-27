import 'package:material_ui/material_ui.dart';
import 'package:provider/provider.dart';
import '../../../core/network/api_client.dart';
import '../../../core/theme/app_theme.dart';

class CalendarHistoryScreen extends StatefulWidget {
  const CalendarHistoryScreen({super.key});

  @override
  State<CalendarHistoryScreen> createState() => _CalendarHistoryScreenState();
}

class _CalendarHistoryScreenState extends State<CalendarHistoryScreen> {
  bool _loading = true;
  List<Map<String, dynamic>> _days = [];

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() => _loading = true);
    try {
      final api = context.read<ApiClient>();
      final now = DateTime.now();
      final res = await api.get('/calendar', query: {'month': now.month, 'year': now.year});
      final calData = res['data'] as Map<String, dynamic>;

      setState(() {
        _days = ((calData['days'] as List?) ?? [])
            .whereType<Map<String, dynamic>>()
            .toList();
      });
    } catch (_) {
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Color _colorForStatus(String status) {
    return switch (status) {
      'libur' => AppTheme.danger,
      'cuti' => AppTheme.warning,
      'malam' => const Color(0xFF4F46E5),
      _ => AppTheme.success,
    };
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Kalender Jadwal Kerja'),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _loadData,
              child: ListView.builder(
                padding: const EdgeInsets.all(16),
                itemCount: _days.length,
                itemBuilder: (_, idx) {
                  final d = _days[idx];
                  final st = d['status_type']?.toString() ?? 'normal_work';
                  final c = _colorForStatus(st);
                  return Card(
                    margin: const EdgeInsets.only(bottom: 8),
                    child: ListTile(
                      leading: Container(
                        width: 42,
                        height: 42,
                        alignment: Alignment.center,
                        decoration: BoxDecoration(
                          color: c.withValues(alpha: 0.14),
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: Text(
                          d['day_number']?.toString() ?? '-',
                          style: TextStyle(fontWeight: FontWeight.w800, color: c),
                        ),
                      ),
                      title: Text(
                        d['full_date']?.toString() ?? d['date']?.toString() ?? '',
                        style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13.5),
                      ),
                      subtitle: Text(
                        '${d['title'] ?? ''} • ${d['description'] ?? ''}',
                        style: const TextStyle(fontSize: 12),
                      ),
                    ),
                  );
                },
              ),
            ),
    );
  }
}
