import 'package:material_ui/material_ui.dart';
import 'package:provider/provider.dart';
import '../../../core/config/app_config.dart';
import '../../../core/providers/auth_provider.dart';
import '../../../core/theme/app_theme.dart';
import '../../approvals/screens/approvals_screen.dart';
import '../../common/widgets/server_url_dialog.dart';
import '../../common/widgets/ui_kit.dart';
import '../../notifications/screens/notifications_screen.dart';
import '../../onboarding/screens/schedule_screen.dart';
import '../../onboarding/screens/signature_screen.dart';
import '../../submissions/screens/submissions_screen.dart';
import 'dashboard_screen.dart';

class MainShellScreen extends StatefulWidget {
  const MainShellScreen({super.key});

  @override
  State<MainShellScreen> createState() => _MainShellScreenState();
}

class _MainShellScreenState extends State<MainShellScreen> {
  int _selectedIndex = 0;
  int _unreadCount = 0;
  int _pendingApprovals = 0;

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final user = auth.user;
    if (user == null) return const SizedBox.shrink();

    final isApprover = user.isApprover;

    final pages = <Widget>[
      DashboardScreen(
        onGoToSubmissions: () => setState(() => _selectedIndex = 1),
        onSyncCounts: (unread, pending) {
          if (mounted && (unread != _unreadCount || pending != _pendingApprovals)) {
            setState(() {
              _unreadCount = unread;
              _pendingApprovals = pending;
            });
          }
        },
      ),
      SubmissionsScreen(onAccountUpdated: () => auth.refreshMe()),
      if (isApprover) ApprovalsScreen(onProcessed: () => auth.refreshMe()),
      NotificationsScreen(
        onUnreadChanged: () => setState(() => _unreadCount = 0),
      ),
    ];

    if (_selectedIndex >= pages.length) {
      _selectedIndex = 0;
    }

    final initials = user.name.trim().isEmpty
        ? 'U'
        : user.name
            .trim()
            .split(RegExp(r'\s+'))
            .take(2)
            .map((part) => part.isNotEmpty ? part[0].toUpperCase() : '')
            .join();

    return Scaffold(
      appBar: AppBar(
        titleSpacing: 16,
        title: Row(
          children: [
            const UmbulanBrandMark(size: 36),
            const SizedBox(width: 10),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    AppConfig.appName,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: TextStyle(
                      fontSize: 15.5,
                      fontWeight: FontWeight.w800,
                      color: AppTheme.textPrimary,
                      letterSpacing: -0.2,
                    ),
                  ),
                  Text(
                    user.station?.name ?? 'Operasional Umbulan',
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      fontSize: 11.5,
                      fontWeight: FontWeight.w600,
                      color: AppTheme.textSecondary,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
        actions: [
          PopupMenuButton<String>(
            tooltip: 'Menu Akun',
            position: PopupMenuPosition.under,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
            icon: Container(
              width: 36,
              height: 36,
              alignment: Alignment.center,
              decoration: BoxDecoration(
                color: AppTheme.primary.withValues(alpha: 0.10),
                borderRadius: BorderRadius.circular(11),
                border: Border.all(color: AppTheme.primary.withValues(alpha: 0.22)),
              ),
              child: Text(
                initials,
                style: const TextStyle(
                  color: AppTheme.primary,
                  fontWeight: FontWeight.w800,
                  fontSize: 13,
                ),
              ),
            ),
            onSelected: (val) async {
              if (val == 'schedule') {
                await Navigator.push(context, MaterialPageRoute(builder: (_) => const ScheduleScreen()));
                await auth.refreshMe();
              } else if (val == 'signature') {
                await Navigator.push(context, MaterialPageRoute(builder: (_) => const SignatureScreen()));
                await auth.refreshMe();
              } else if (val == 'server') {
                await showServerConfigDialog(context);
              } else if (val == 'logout') {
                await auth.logout();
              }
            },
            itemBuilder: (_) => [
              const PopupMenuItem(
                value: 'schedule',
                child: Row(
                  children: [
                    Icon(Icons.schedule_outlined, size: 19, color: AppTheme.primary),
                    SizedBox(width: 10),
                    Text('Atur Jadwal Kerja'),
                  ],
                ),
              ),
              const PopupMenuItem(
                value: 'signature',
                child: Row(
                  children: [
                    Icon(Icons.draw_outlined, size: 19, color: AppTheme.primary),
                    SizedBox(width: 10),
                    Text('Perbarui Tanda Tangan'),
                  ],
                ),
              ),
              const PopupMenuItem(
                value: 'server',
                child: Row(
                  children: [
                    Icon(Icons.dns_outlined, size: 19, color: AppTheme.textSecondary),
                    SizedBox(width: 10),
                    Text('Pengaturan Server API'),
                  ],
                ),
              ),
              const PopupMenuDivider(),
              const PopupMenuItem(
                value: 'logout',
                child: Row(
                  children: [
                    Icon(Icons.logout_rounded, size: 19, color: AppTheme.danger),
                    SizedBox(width: 10),
                    Text('Keluar Akun', style: TextStyle(color: AppTheme.danger, fontWeight: FontWeight.w600)),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(width: 6),
        ],
      ),
      body: pages[_selectedIndex],
      bottomNavigationBar: Container(
        decoration: const BoxDecoration(
          border: Border(top: BorderSide(color: AppTheme.border, width: 1)),
        ),
        child: NavigationBar(
          selectedIndex: _selectedIndex,
          onDestinationSelected: (idx) => setState(() => _selectedIndex = idx),
          destinations: [
            const NavigationDestination(
              icon: Icon(Icons.home_outlined),
              selectedIcon: Icon(Icons.home_rounded),
              label: 'Beranda',
            ),
            const NavigationDestination(
              icon: Icon(Icons.assignment_outlined),
              selectedIcon: Icon(Icons.assignment_rounded),
              label: 'Pengajuan',
            ),
            if (isApprover)
              NavigationDestination(
                icon: Badge(
                  isLabelVisible: _pendingApprovals > 0,
                  label: Text('$_pendingApprovals'),
                  child: const Icon(Icons.approval_outlined),
                ),
                selectedIcon: Badge(
                  isLabelVisible: _pendingApprovals > 0,
                  label: Text('$_pendingApprovals'),
                  child: const Icon(Icons.approval_rounded),
                ),
                label: 'Persetujuan',
              ),
            NavigationDestination(
              icon: Badge(
                isLabelVisible: _unreadCount > 0,
                label: Text('$_unreadCount'),
                child: const Icon(Icons.notifications_outlined),
              ),
              selectedIcon: Badge(
                isLabelVisible: _unreadCount > 0,
                label: Text('$_unreadCount'),
                child: const Icon(Icons.notifications_rounded),
              ),
              label: 'Notifikasi',
            ),
          ],
        ),
      ),
    );
  }
}
