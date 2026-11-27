import 'package:material_ui/material_ui.dart';
import 'package:provider/provider.dart';
import '../../../core/network/api_client.dart';
import '../../../core/providers/auth_provider.dart';
import '../../../core/theme/app_theme.dart';
import '../../auth/screens/email_otp_screen.dart';
import '../../common/widgets/readiness_banner.dart';
import '../../onboarding/screens/schedule_screen.dart';
import '../../onboarding/screens/signature_screen.dart';
import 'calendar_history_screen.dart';

class DashboardScreen extends StatefulWidget {
  final VoidCallback onGoToSubmissions;
  final void Function(int unreadNotifications, int pendingApprovals) onSyncCounts;

  const DashboardScreen({
    super.key,
    required this.onGoToSubmissions,
    required this.onSyncCounts,
  });

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  bool _loading = true;
  Map<String, dynamic> _data = {};
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadDashboard();
  }

  Future<void> _loadDashboard() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final res = await context.read<ApiClient>().get('/dashboard');
      final payload = (res['data'] as Map<String, dynamic>?) ?? {};
      final unread = (payload['unread_notifications_count'] as num?)?.toInt() ?? 0;
      final pending = (payload['pending_approvals_count']?['total'] as num?)?.toInt() ?? 0;
      widget.onSyncCounts(unread, pending);

      if (mounted) {
        setState(() => _data = payload);
      }
    } catch (e) {
      if (mounted) setState(() => _error = e.toString());
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _openAndReload(Widget page) async {
    await Navigator.push(context, MaterialPageRoute(builder: (_) => page));
    if (mounted) {
      await context.read<AuthProvider>().refreshMe();
      await _loadDashboard();
    }
  }

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().user;
    if (user == null) return const SizedBox.shrink();

    final schedule = (_data['today_schedule'] as Map<String, dynamic>?) ?? {};
    final leaveBalance = (_data['leave_balance'] as Map<String, dynamic>?) ?? {};

    return RefreshIndicator(
      onRefresh: () async {
        await context.read<AuthProvider>().refreshMe();
        await _loadDashboard();
      },
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // Header Profil Singkat
          Container(
            padding: const EdgeInsets.all(18),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [AppTheme.primary, AppTheme.primaryDark],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(18),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Halo, ${user.name}',
                  style: const TextStyle(color: Colors.white, fontSize: 19, fontWeight: FontWeight.w800),
                ),
                const SizedBox(height: 4),
                Text(
                  '${user.station?.name ?? 'Stasiun Kerja'} • ${user.roleNames.join(', ')}',
                  style: TextStyle(color: Colors.white.withValues(alpha: 0.85), fontSize: 13),
                ),
                const SizedBox(height: 14),
                Row(
                  children: [
                    _QuickBadge(
                      label: user.scheduleType == 'roster' ? 'Sistem Roster' : 'Jadwal Normal',
                      icon: Icons.calendar_today,
                    ),
                    const SizedBox(width: 10),
                    _QuickBadge(
                      label: user.isAccountComplete ? 'Akun Siap' : 'Lengkapi Profil',
                      icon: user.isAccountComplete ? Icons.verified_user : Icons.pending_actions,
                    ),
                  ],
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),

          // Guard 3 Syarat Kelengkapan Akun
          ReadinessBanner(
            status: user.completionStatus,
            onVerifyEmail: () => _openAndReload(const EmailOtpScreen()),
            onSetupSignature: () => _openAndReload(const SignatureScreen()),
            onSetupSchedule: () => _openAndReload(const ScheduleScreen()),
          ),

          if (_error != null)
            Card(
              color: const Color(0xFFFEF2F2),
              child: Padding(
                padding: const EdgeInsets.all(14),
                child: Text(_error!, style: const TextStyle(color: AppTheme.danger)),
              ),
            )
          else if (_loading && _data.isEmpty)
            const Padding(
              padding: EdgeInsets.all(32),
              child: Center(child: CircularProgressIndicator()),
            )
          else ...[
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        const Icon(Icons.access_time_filled, color: AppTheme.primary),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Text(
                            schedule['shift_name']?.toString() ?? 'Jadwal Kerja',
                            style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 15),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Text(
                      schedule['is_day_off'] == true
                          ? 'Status hari ini: Libur Kerja (OFF).'
                          : 'Jam Kerja: ${schedule['scheduled_in'] ?? '-'} s/d ${schedule['scheduled_out'] ?? '-'} WIB',
                      style: const TextStyle(
                        color: AppTheme.textSecondary,
                        fontSize: 13,
                      ),
                    ),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Expanded(
                          child: FilledButton.icon(
                            onPressed: widget.onGoToSubmissions,
                            icon: const Icon(Icons.assignment_add, size: 18),
                            label: const Text('Buat Pengajuan'),
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: OutlinedButton.icon(
                            onPressed: () => _openAndReload(const CalendarHistoryScreen()),
                            icon: const Icon(Icons.calendar_month_outlined, size: 18),
                            label: const Text('Kalender'),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(
                  child: Card(
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text('Sisa Cuti Tahunan', style: TextStyle(fontSize: 12, color: AppTheme.textSecondary)),
                          const SizedBox(height: 6),
                          Text(
                            '${leaveBalance['sisa_cuti_tahunan'] ?? 0} Hari',
                            style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w800, color: AppTheme.primary),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Card(
                    child: InkWell(
                      onTap: () => _openAndReload(const CalendarHistoryScreen()),
                      borderRadius: BorderRadius.circular(16),
                      child: const Padding(
                        padding: EdgeInsets.all(16),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text('Kalender & Cuti', style: TextStyle(fontSize: 12, color: AppTheme.textSecondary)),
                            SizedBox(height: 6),
                            Row(
                              children: [
                                Text('Lihat Jadwal', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: AppTheme.accent)),
                                Spacer(),
                                Icon(Icons.arrow_forward_ios, size: 14, color: AppTheme.accent),
                              ],
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ],
        ],
      ),
    );
  }
}

class _QuickBadge extends StatelessWidget {
  final String label;
  final IconData icon;
  const _QuickBadge({required this.label, required this.icon});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.16),
        borderRadius: BorderRadius.circular(10),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, color: Colors.white, size: 15),
          const SizedBox(width: 6),
          Text(label, style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w600)),
        ],
      ),
    );
  }
}
