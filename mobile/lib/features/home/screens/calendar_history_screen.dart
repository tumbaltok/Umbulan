import 'package:material_ui/material_ui.dart';
import 'package:provider/provider.dart';
import '../../../core/network/api_client.dart';
import '../../../core/theme/app_theme.dart';
import '../../common/widgets/ui_kit.dart';

class CalendarHistoryScreen extends StatefulWidget {
  const CalendarHistoryScreen({super.key});

  @override
  State<CalendarHistoryScreen> createState() => _CalendarHistoryScreenState();
}

class _CalendarHistoryScreenState extends State<CalendarHistoryScreen> {
  static const _monthNames = [
    '',
    'Januari',
    'Februari',
    'Maret',
    'April',
    'Mei',
    'Juni',
    'Juli',
    'Agustus',
    'September',
    'Oktober',
    'November',
    'Desember',
  ];

  static const _dayLabels = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];

  late DateTime _focusedMonth;
  bool _loading = true;
  String? _error;
  List<Map<String, dynamic>> _days = [];
  Map<String, dynamic>? _selectedDay;

  @override
  void initState() {
    super.initState();
    final now = DateTime.now();
    _focusedMonth = DateTime(now.year, now.month, 1);
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final api = context.read<ApiClient>();
      final res = await api.get(
        '/calendar',
        query: {'month': _focusedMonth.month, 'year': _focusedMonth.year},
      );
      final calData = (res['data'] as Map<String, dynamic>?) ?? {};
      final list = ((calData['days'] as List?) ?? [])
          .whereType<Map<String, dynamic>>()
          .toList();

      final todayStr = _formatIsoDate(DateTime.now());
      Map<String, dynamic>? initialSelected;
      for (final d in list) {
        if (d['date']?.toString() == todayStr) {
          initialSelected = d;
          break;
        }
      }
      initialSelected ??= list.isNotEmpty ? list.first : null;

      if (mounted) {
        setState(() {
          _days = list;
          _selectedDay = initialSelected;
        });
      }
    } catch (e) {
      if (mounted) setState(() => _error = e.toString());
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  void _changeMonth(int delta) {
    setState(() {
      _focusedMonth = DateTime(_focusedMonth.year, _focusedMonth.month + delta, 1);
    });
    _loadData();
  }

  String _formatIsoDate(DateTime dt) {
    final m = dt.month.toString().padLeft(2, '0');
    final d = dt.day.toString().padLeft(2, '0');
    return '${dt.year}-$m-$d';
  }

  Color _colorForStatus(String status) {
    return switch (status) {
      'libur' => AppTheme.danger,
      'cuti' => AppTheme.warning,
      'malam' => AppTheme.indigo,
      _ => AppTheme.success,
    };
  }

  String _labelForStatus(String status) {
    return switch (status) {
      'libur' => 'Libur',
      'cuti' => 'Cuti',
      'malam' => 'Shift Malam',
      'pagi' => 'Shift Pagi',
      _ => 'Hari Kerja',
    };
  }

  @override
  Widget build(BuildContext context) {
    final monthTitle = '${_monthNames[_focusedMonth.month]} ${_focusedMonth.year}';
    final firstWeekdayOffset = _focusedMonth.weekday - 1;
    final todayIso = _formatIsoDate(DateTime.now());

    int workCount = 0;
    int offCount = 0;
    int leaveCount = 0;
    for (final d in _days) {
      final st = d['status_type']?.toString() ?? 'normal_work';
      if (st == 'libur') {
        offCount++;
      } else if (st == 'cuti') {
        leaveCount++;
      } else {
        workCount++;
      }
    }

    return Scaffold(
      appBar: AppBar(title: const Text('Kalender Jadwal Kerja')),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(
                  child: Padding(
                    padding: const EdgeInsets.all(24),
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text(_error!, textAlign: TextAlign.center, style: const TextStyle(color: AppTheme.danger)),
                        const SizedBox(height: 12),
                        OutlinedButton(onPressed: _loadData, child: const Text('Muat Ulang')),
                      ],
                    ),
                  ),
                )
              : RefreshIndicator(
                  onRefresh: _loadData,
                  child: ListView(
                    padding: const EdgeInsets.fromLTRB(16, 16, 16, 28),
                    children: [
                      // Kontrol Bulan
                      Card(
                        child: Padding(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                          child: Row(
                            children: [
                              IconButton(
                                onPressed: () => _changeMonth(-1),
                                icon: const Icon(Icons.chevron_left_rounded),
                                tooltip: 'Bulan Sebelumnya',
                              ),
                              Expanded(
                                child: Text(
                                  monthTitle,
                                  textAlign: TextAlign.center,
                                  style: const TextStyle(
                                    fontSize: 16,
                                    fontWeight: FontWeight.w800,
                                    color: AppTheme.textPrimary,
                                  ),
                                ),
                              ),
                              IconButton(
                                onPressed: () => _changeMonth(1),
                                icon: const Icon(Icons.chevron_right_rounded),
                                tooltip: 'Bulan Berikutnya',
                              ),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(height: 12),

                      // Ringkasan Bulan
                      Row(
                        children: [
                          Expanded(child: _StatPill(label: 'Aktif/Shift', count: workCount, color: AppTheme.success)),
                          const SizedBox(width: 8),
                          Expanded(child: _StatPill(label: 'Libur', count: offCount, color: AppTheme.danger)),
                          const SizedBox(width: 8),
                          Expanded(child: _StatPill(label: 'Cuti', count: leaveCount, color: AppTheme.warning)),
                        ],
                      ),
                      const SizedBox(height: 12),

                      if (_days.isEmpty)
                        const Card(
                          child: UmbulanEmptyState(
                            icon: Icons.calendar_today_outlined,
                            title: 'Data Kalender Kosong',
                            subtitle: 'Tidak ada jadwal yang tersedia untuk bulan ini.',
                          ),
                        )
                      else ...[
                        // Grid 7 Kolom Kalender
                        Card(
                          child: Padding(
                            padding: const EdgeInsets.all(14),
                            child: Column(
                              children: [
                                Row(
                                  children: _dayLabels
                                      .map(
                                        (lbl) => Expanded(
                                          child: Center(
                                            child: Text(
                                              lbl,
                                              style: TextStyle(
                                                fontSize: 11,
                                                fontWeight: FontWeight.w700,
                                                color: lbl == 'Min' ? AppTheme.danger : AppTheme.textSecondary,
                                              ),
                                            ),
                                          ),
                                        ),
                                      )
                                      .toList(),
                                ),
                                const SizedBox(height: 10),
                                GridView.builder(
                                  shrinkWrap: true,
                                  physics: const NeverScrollableScrollPhysics(),
                                  gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                                    crossAxisCount: 7,
                                    mainAxisSpacing: 6,
                                    crossAxisSpacing: 6,
                                    childAspectRatio: 1,
                                  ),
                                  itemCount: firstWeekdayOffset + _days.length,
                                  itemBuilder: (_, idx) {
                                    if (idx < firstWeekdayOffset) {
                                      return const SizedBox.shrink();
                                    }
                                    final day = _days[idx - firstWeekdayOffset];
                                    final st = day['status_type']?.toString() ?? 'normal_work';
                                    final color = _colorForStatus(st);
                                    final isSelected = _selectedDay?['date'] == day['date'];
                                    final isToday = day['date']?.toString() == todayIso;

                                    return InkWell(
                                      onTap: () => setState(() => _selectedDay = day),
                                      borderRadius: BorderRadius.circular(11),
                                      child: Container(
                                        decoration: BoxDecoration(
                                          color: isSelected ? color : color.withValues(alpha: 0.12),
                                          borderRadius: BorderRadius.circular(11),
                                          border: isToday && !isSelected
                                              ? Border.all(color: AppTheme.primary, width: 1.8)
                                              : (isSelected ? Border.all(color: color, width: 1.5) : null),
                                        ),
                                        child: Column(
                                          mainAxisAlignment: MainAxisAlignment.center,
                                          children: [
                                            Text(
                                              day['day_number']?.toString() ?? '-',
                                              style: TextStyle(
                                                fontSize: 13,
                                                fontWeight: FontWeight.w800,
                                                color: isSelected ? Colors.white : AppTheme.textPrimary,
                                              ),
                                            ),
                                            const SizedBox(height: 2),
                                            Container(
                                              width: 5,
                                              height: 5,
                                              decoration: BoxDecoration(
                                                color: isSelected ? Colors.white : color,
                                                shape: BoxShape.circle,
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                    );
                                  },
                                ),
                                const SizedBox(height: 12),
                                const Divider(height: 1),
                                const SizedBox(height: 10),
                                const Wrap(
                                  spacing: 12,
                                  runSpacing: 6,
                                  children: [
                                    _LegendDot(color: AppTheme.success, label: 'Kerja/Pagi'),
                                    _LegendDot(color: AppTheme.indigo, label: 'Shift Malam'),
                                    _LegendDot(color: AppTheme.danger, label: 'Libur'),
                                    _LegendDot(color: AppTheme.warning, label: 'Cuti'),
                                  ],
                                ),
                              ],
                            ),
                          ),
                        ),
                        const SizedBox(height: 12),

                        // Detail Tanggal Terpilih
                        if (_selectedDay != null)
                          Card(
                            child: Padding(
                              padding: const EdgeInsets.all(16),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    children: [
                                      Expanded(
                                        child: Text(
                                          _selectedDay!['full_date']?.toString() ??
                                              _selectedDay!['date']?.toString() ??
                                              '-',
                                          style: const TextStyle(
                                            fontWeight: FontWeight.w800,
                                            fontSize: 15,
                                            color: AppTheme.textPrimary,
                                          ),
                                        ),
                                      ),
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                        decoration: BoxDecoration(
                                          color: _colorForStatus(
                                            _selectedDay!['status_type']?.toString() ?? 'normal_work',
                                          ).withValues(alpha: 0.12),
                                          borderRadius: BorderRadius.circular(999),
                                        ),
                                        child: Text(
                                          _labelForStatus(
                                            _selectedDay!['status_type']?.toString() ?? 'normal_work',
                                          ),
                                          style: TextStyle(
                                            fontSize: 11.5,
                                            fontWeight: FontWeight.w800,
                                            color: _colorForStatus(
                                              _selectedDay!['status_type']?.toString() ?? 'normal_work',
                                            ),
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 8),
                                  Text(
                                    _selectedDay!['title']?.toString() ?? '-',
                                    style: const TextStyle(
                                      fontSize: 14,
                                      fontWeight: FontWeight.w700,
                                      color: AppTheme.textPrimary,
                                    ),
                                  ),
                                  const SizedBox(height: 4),
                                  Text(
                                    _selectedDay!['description']?.toString() ?? '-',
                                    style: const TextStyle(
                                      fontSize: 12.5,
                                      height: 1.4,
                                      color: AppTheme.textSecondary,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                      ],
                    ],
                  ),
                ),
    );
  }
}

class _StatPill extends StatelessWidget {
  final String label;
  final int count;
  final Color color;

  const _StatPill({required this.label, required this.count, required this.color});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppTheme.border),
      ),
      child: Row(
        children: [
          Container(
            width: 8,
            height: 8,
            decoration: BoxDecoration(color: color, shape: BoxShape.circle),
          ),
          const SizedBox(width: 8),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  '$count Hari',
                  style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13.5, color: AppTheme.textPrimary),
                ),
                Text(
                  label,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(fontSize: 10.5, color: AppTheme.textSecondary),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _LegendDot extends StatelessWidget {
  final Color color;
  final String label;

  const _LegendDot({required this.color, required this.label});

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Container(width: 8, height: 8, decoration: BoxDecoration(color: color, shape: BoxShape.circle)),
        const SizedBox(width: 5),
        Text(label, style: const TextStyle(fontSize: 11.5, color: AppTheme.textSecondary, fontWeight: FontWeight.w600)),
      ],
    );
  }
}
