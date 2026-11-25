import 'package:material_ui/material_ui.dart';
import 'package:provider/provider.dart';
import '../../../core/models/app_models.dart';
import '../../../core/network/api_client.dart';
import '../../../core/theme/app_theme.dart';

class CalendarHistoryScreen extends StatefulWidget {
  final int initialTabIndex;
  const CalendarHistoryScreen({super.key, this.initialTabIndex = 0});

  @override
  State<CalendarHistoryScreen> createState() => _CalendarHistoryScreenState();
}

class _CalendarHistoryScreenState extends State<CalendarHistoryScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabCtrl;
  bool _loading = true;
  List<Map<String, dynamic>> _days = [];
  List<AttendanceRecord> _history = [];

  @override
  void initState() {
    super.initState();
    _tabCtrl = TabController(length: 2, vsync: this, initialIndex: widget.initialTabIndex);
    _loadData();
  }

  @override
  void dispose() {
    _tabCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadData() async {
    setState(() => _loading = true);
    try {
      final api = context.read<ApiClient>();
      final now = DateTime.now();
      final results = await Future.wait([
        api.get('/calendar', query: {'month': now.month, 'year': now.year}),
        api.get('/attendance/history'),
      ]);

      final calData = results[0]['data'] as Map<String, dynamic>;
      final histData = results[1]['data'] as Map<String, dynamic>;

      setState(() {
        _days = ((calData['days'] as List?) ?? [])
            .whereType<Map<String, dynamic>>()
            .toList();
        _history = ((histData['items'] as List?) ?? [])
            .whereType<Map<String, dynamic>>()
            .map(AttendanceRecord.fromJson)
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
        title: const Text('Jadwal & Riwayat Absensi'),
        bottom: TabBar(
          controller: _tabCtrl,
          labelColor: AppTheme.primary,
          indicatorColor: AppTheme.primary,
          tabs: const [
            Tab(text: 'Kalender Bulan Ini'),
            Tab(text: 'Riwayat Kehadiran'),
          ],
        ),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : TabBarView(
              controller: _tabCtrl,
              children: [
                RefreshIndicator(
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
                RefreshIndicator(
                  onRefresh: _loadData,
                  child: _history.isEmpty
                      ? ListView(
                          children: const [
                            SizedBox(height: 100),
                            Text('Belum ada riwayat kehadiran.', textAlign: TextAlign.center),
                          ],
                        )
                      : ListView.builder(
                          padding: const EdgeInsets.all(16),
                          itemCount: _history.length,
                          itemBuilder: (_, idx) {
                            final r = _history[idx];
                            return Card(
                              margin: const EdgeInsets.only(bottom: 10),
                              child: ListTile(
                                title: Text(
                                  '${r.date} • ${r.status ?? r.shiftType ?? '-'}',
                                  style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 14),
                                ),
                                subtitle: Text(
                                  'Masuk: ${r.checkIn ?? '--:--'}  |  Pulang: ${r.checkOut ?? '--:--'}',
                                  style: const TextStyle(fontSize: 12.5),
                                ),
                                trailing: r.isLate
                                    ? const Chip(
                                        label: Text('Telat', style: TextStyle(fontSize: 11)),
                                        backgroundColor: Color(0xFFFEF3C7),
                                      )
                                    : null,
                              ),
                            );
                          },
                        ),
                ),
              ],
            ),
    );
  }
}
