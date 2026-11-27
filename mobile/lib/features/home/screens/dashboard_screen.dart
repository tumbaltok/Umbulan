import 'package:material_ui/material_ui.dart';
import 'package:provider/provider.dart';
import '../../../core/network/api_client.dart';
import '../../../core/providers/auth_provider.dart';
import '../../../core/theme/app_theme.dart';
import '../../auth/screens/email_otp_screen.dart';
import '../../common/widgets/readiness_banner.dart';
import '../../common/widgets/ui_kit.dart';
import '../../onboarding/screens/schedule_screen.dart';
import '../../onboarding/screens/signature_screen.dart';
import '../../submissions/screens/submission_forms.dart';
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
    final sisaCuti = (leaveBalance['sisa_cuti_tahunan'] as num?)?.toInt() ?? 0;
    final hakCuti = (leaveBalance['kuota_tahunan'] as num?)?.toInt() ?? 12;
    final cutiTerpakai = (leaveBalance['cuti_terpakai'] as num?)?.toInt() ?? 0;
    final pendingHari = (leaveBalance['pending_hari'] as num?)?.toInt() ?? 0;
    final sisaCutiHaid = (leaveBalance['sisa_cuti_haid'] as num?)?.toInt() ?? 0;
    final rasioSisa = hakCuti > 0 ? (sisaCuti / hakCuti).clamp(0.0, 1.0) : 0.0;
    final isDayOff = schedule['is_day_off'] == true;

    final hour = DateTime.now().hour;
    final greeting = hour < 11
        ? 'Selamat Pagi'
        : (hour < 15 ? 'Selamat Siang' : (hour < 18 ? 'Selamat Sore' : 'Selamat Malam'));

    return RefreshIndicator(
      onRefresh: () async {
        await context.read<AuthProvider>().refreshMe();
        await _loadDashboard();
      },
      child: ListView(
        padding: const EdgeInsets.fromLTRB(16, 16, 16, 28),
        children: [
          // Hero Profil & Status Ringkas
          Container(
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [AppTheme.primaryDark, AppTheme.primary, Color(0xFF0284C7)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(22),
              boxShadow: [
                BoxShadow(
                  color: AppTheme.primary.withValues(alpha: 0.22),
                  blurRadius: 18,
                  offset: const Offset(0, 8),
                ),
              ],
            ),
            child: Stack(
              children: [
                Positioned(
                  right: -28,
                  top: -28,
                  child: Container(
                    width: 120,
                    height: 120,
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.08),
                      shape: BoxShape.circle,
                    ),
                  ),
                ),
                Positioned(
                  right: 28,
                  bottom: -36,
                  child: Container(
                    width: 88,
                    height: 88,
                    decoration: BoxDecoration(
                      color: const Color(0xFF38BDF8).withValues(alpha: 0.12),
                      shape: BoxShape.circle,
                    ),
                  ),
                ),
                Padding(
                  padding: const EdgeInsets.all(20),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        greeting,
                        style: TextStyle(
                          color: const Color(0xFFBAE6FD).withValues(alpha: 0.95),
                          fontSize: 12.5,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        user.name,
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 20,
                          fontWeight: FontWeight.w800,
                          letterSpacing: -0.3,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        '${user.station?.name ?? 'Stasiun Kerja'} • ${user.roleNames.join(', ')}',
                        style: TextStyle(color: Colors.white.withValues(alpha: 0.85), fontSize: 12.5),
                      ),
                      const SizedBox(height: 14),
                      Wrap(
                        spacing: 8,
                        runSpacing: 8,
                        children: [
                          _QuickBadge(
                            label: user.scheduleType == 'roster' ? 'Sistem Roster' : 'Jadwal Normal',
                            icon: Icons.calendar_today_rounded,
                          ),
                          _QuickBadge(
                            label: user.isAccountComplete ? 'Akun Siap' : 'Lengkapi Profil',
                            icon: user.isAccountComplete ? Icons.verified_user_rounded : Icons.pending_actions_rounded,
                          ),
                        ],
                      ),
                    ],
                  ),
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
                child: Row(
                  children: [
                    const Icon(Icons.error_outline, color: AppTheme.danger, size: 20),
                    const SizedBox(width: 10),
                    Expanded(child: Text(_error!, style: const TextStyle(color: AppTheme.danger, fontSize: 13))),
                  ],
                ),
              ),
            )
          else if (_loading && _data.isEmpty)
            const Padding(
              padding: EdgeInsets.all(36),
              child: Center(child: CircularProgressIndicator()),
            )
          else ...[
            // Aksi Cepat Layanan
            const UmbulanSectionHeader(
              title: 'Layanan Cepat',
              subtitle: 'Buat pengajuan atau pantau kalender shift Anda',
            ),
            const SizedBox(height: 10),
            Row(
              children: [
                Expanded(
                  child: _QuickServiceButton(
                    icon: Icons.event_available_rounded,
                    label: 'Cuti',
                    color: AppTheme.primary,
                    onTap: () => user.isAccountComplete
                        ? _openAndReload(const CreateCutiScreen())
                        : widget.onGoToSubmissions(),
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: _QuickServiceButton(
                    icon: Icons.receipt_long_rounded,
                    label: 'CAR',
                    color: AppTheme.teal,
                    onTap: () => user.isAccountComplete
                        ? _openAndReload(const CreateCarScreen())
                        : widget.onGoToSubmissions(),
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: _QuickServiceButton(
                    icon: Icons.inventory_2_rounded,
                    label: 'MPR',
                    color: AppTheme.indigo,
                    onTap: () => user.isAccountComplete
                        ? _openAndReload(const CreateMprScreen())
                        : widget.onGoToSubmissions(),
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: _QuickServiceButton(
                    icon: Icons.calendar_month_rounded,
                    label: 'Kalender',
                    color: AppTheme.accent,
                    onTap: () => _openAndReload(const CalendarHistoryScreen()),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),

            // Kartu Jadwal Kerja Hari Ini
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Container(
                          width: 40,
                          height: 40,
                          decoration: BoxDecoration(
                            color: (isDayOff ? AppTheme.danger : AppTheme.primary).withValues(alpha: 0.10),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Icon(
                            isDayOff ? Icons.weekend_rounded : Icons.access_time_filled_rounded,
                            color: isDayOff ? AppTheme.danger : AppTheme.primary,
                            size: 20,
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text(
                                'JADWAL HARI INI',
                                style: TextStyle(
                                  fontSize: 10.5,
                                  fontWeight: FontWeight.w800,
                                  letterSpacing: 0.6,
                                  color: AppTheme.textSecondary,
                                ),
                              ),
                              const SizedBox(height: 2),
                              Text(
                                schedule['shift_name']?.toString() ?? 'Jadwal Kerja',
                                style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 15.5, color: AppTheme.textPrimary),
                              ),
                            ],
                          ),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                          decoration: BoxDecoration(
                            color: (isDayOff ? AppTheme.danger : AppTheme.success).withValues(alpha: 0.10),
                            borderRadius: BorderRadius.circular(999),
                          ),
                          child: Text(
                            isDayOff ? 'Libur (OFF)' : 'Aktif',
                            style: TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.w800,
                              color: isDayOff ? AppTheme.danger : AppTheme.success,
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                      decoration: BoxDecoration(
                        color: AppTheme.surfaceMuted,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: AppTheme.border),
                      ),
                      child: Row(
                        children: [
                          Icon(
                            isDayOff ? Icons.beach_access_outlined : Icons.schedule_rounded,
                            size: 16,
                            color: AppTheme.textSecondary,
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Text(
                              isDayOff
                                  ? 'Hari ini Anda terjadwal libur kerja.'
                                  : 'Jam Operasional: ${schedule['scheduled_in'] ?? '-'} – ${schedule['scheduled_out'] ?? '-'} WIB',
                              style: const TextStyle(
                                color: AppTheme.textPrimary,
                                fontWeight: FontWeight.w600,
                                fontSize: 13,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 14),
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
                          Row(
                            children: [
                              const Expanded(
                                child: Text(
                                  'Sisa Cuti Tahunan',
                                  style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppTheme.textSecondary),
                                ),
                              ),
                              Icon(Icons.pie_chart_outline_rounded, size: 16, color: AppTheme.primary.withValues(alpha: 0.7)),
                            ],
                          ),
                          const SizedBox(height: 8),
                          Text(
                            '$sisaCuti Hari',
                            style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w800, color: AppTheme.primary),
                          ),
                          const SizedBox(height: 6),
                          Text(
                            'Terpakai $cutiTerpakai H • Pending $pendingHari H',
                            style: const TextStyle(fontSize: 10.5, color: AppTheme.textSecondary, fontWeight: FontWeight.w600),
                          ),
                          const SizedBox(height: 8),
                          ClipRRect(
                            borderRadius: BorderRadius.circular(99),
                            child: LinearProgressIndicator(
                              value: rasioSisa,
                              minHeight: 5,
                              backgroundColor: AppTheme.border,
                              valueColor: const AlwaysStoppedAnimation(AppTheme.primary),
                            ),
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
                      borderRadius: BorderRadius.circular(18),
                      child: Padding(
                        padding: const EdgeInsets.all(16),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                const Expanded(
                                  child: Text(
                                    'Kalender & Cuti',
                                    style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppTheme.textSecondary),
                                  ),
                                ),
                                Icon(Icons.calendar_today_outlined, size: 15, color: AppTheme.accent.withValues(alpha: 0.85)),
                              ],
                            ),
                            const SizedBox(height: 8),
                            const Row(
                              children: [
                                Expanded(
                                  child: Text(
                                    'Lihat Jadwal',
                                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: AppTheme.accent),
                                  ),
                                ),
                                Icon(Icons.arrow_forward_ios_rounded, size: 14, color: AppTheme.accent),
                              ],
                            ),
                            const SizedBox(height: 10),
                            const Text(
                              'Shift & hari libur',
                              style: TextStyle(fontSize: 11, color: AppTheme.textSecondary),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                ),
              ],
            ),
            if (user.isPerempuan) ...[
              const SizedBox(height: 10),
              Card(
                color: const Color(0xFFFFF1F2),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(18),
                  side: const BorderSide(color: Color(0xFFFECDD3)),
                ),
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                  child: Row(
                    children: [
                      Container(
                        width: 40,
                        height: 40,
                        decoration: BoxDecoration(
                          color: const Color(0xFFE11D48).withValues(alpha: 0.12),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: const Icon(Icons.favorite_rounded, color: Color(0xFFE11D48), size: 20),
                      ),
                      const SizedBox(width: 12),
                      const Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Saldo Cuti Haid (Bulan Ini)',
                              style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w800, color: Color(0xFF881337)),
                            ),
                            SizedBox(height: 2),
                            Text(
                              'Jatah khusus karyawan perempuan maks. 2 hari/bulan',
                              style: TextStyle(fontSize: 11, color: Color(0xFF9F1239)),
                            ),
                          ],
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 11, vertical: 6),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(999),
                          border: Border.all(color: const Color(0xFFFDA4AF)),
                        ),
                        child: Text(
                          '$sisaCutiHaid / 2 Hari',
                          style: const TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w800,
                            color: Color(0xFFE11D48),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ],
        ],
      ),
    );
  }
}

class _QuickServiceButton extends StatelessWidget {
  final IconData icon;
  final String label;
  final Color color;
  final VoidCallback onTap;

  const _QuickServiceButton({
    required this.icon,
    required this.label,
    required this.color,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.white,
      borderRadius: BorderRadius.circular(16),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(16),
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 13, horizontal: 6),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: AppTheme.border),
          ),
          child: Column(
            children: [
              Container(
                width: 38,
                height: 38,
                decoration: BoxDecoration(
                  color: color.withValues(alpha: 0.11),
                  borderRadius: BorderRadius.circular(11),
                ),
                child: Icon(icon, size: 20, color: color),
              ),
              const SizedBox(height: 7),
              Text(
                label,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w700,
                  color: AppTheme.textPrimary,
                ),
              ),
            ],
          ),
        ),
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
