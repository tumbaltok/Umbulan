import 'package:material_ui/material_ui.dart';
import 'package:provider/provider.dart';
import '../../../core/config/app_config.dart';
import '../../../core/providers/auth_provider.dart';
import '../../approvals/screens/approvals_screen.dart';
import '../../attendance/screens/attendance_screen.dart';
import '../../common/widgets/server_url_dialog.dart';
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
        onGoToAttendance: () => setState(() => _selectedIndex = 1),
        onSyncCounts: (unread, pending) {
          if (mounted && (unread != _unreadCount || pending != _pendingApprovals)) {
            setState(() {
              _unreadCount = unread;
              _pendingApprovals = pending;
            });
          }
        },
      ),
      AttendanceScreen(onSuccessRefresh: () => auth.refreshMe()),
      SubmissionsScreen(onAccountUpdated: () => auth.refreshMe()),
      if (isApprover) ApprovalsScreen(onProcessed: () => auth.refreshMe()),
      NotificationsScreen(
        onUnreadChanged: () => setState(() => _unreadCount = 0),
      ),
    ];

    if (_selectedIndex >= pages.length) {
      _selectedIndex = 0;
    }

    return Scaffold(
      appBar: AppBar(
        title: const Text(AppConfig.appName),
        actions: [
          PopupMenuButton<String>(
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
            itemBuilder: (_) => const [
              PopupMenuItem(value: 'schedule', child: Text('Atur Jadwal Kerja')),
              PopupMenuItem(value: 'signature', child: Text('Perbarui Tanda Tangan')),
              PopupMenuItem(value: 'server', child: Text('Pengaturan Server API')),
              PopupMenuDivider(),
              PopupMenuItem(value: 'logout', child: Text('Keluar Akun')),
            ],
          ),
        ],
      ),
      body: pages[_selectedIndex],
      bottomNavigationBar: NavigationBar(
        selectedIndex: _selectedIndex,
        onDestinationSelected: (idx) => setState(() => _selectedIndex = idx),
        destinations: [
          const NavigationDestination(
            icon: Icon(Icons.home_outlined),
            selectedIcon: Icon(Icons.home),
            label: 'Beranda',
          ),
          const NavigationDestination(
            icon: Icon(Icons.fingerprint),
            label: 'Absensi',
          ),
          const NavigationDestination(
            icon: Icon(Icons.assignment_outlined),
            selectedIcon: Icon(Icons.assignment),
            label: 'Pengajuan',
          ),
          if (isApprover)
            NavigationDestination(
              icon: Badge(
                isLabelVisible: _pendingApprovals > 0,
                label: Text('$_pendingApprovals'),
                child: const Icon(Icons.approval_outlined),
              ),
              label: 'Persetujuan',
            ),
          NavigationDestination(
            icon: Badge(
              isLabelVisible: _unreadCount > 0,
              label: Text('$_unreadCount'),
              child: const Icon(Icons.notifications_outlined),
            ),
            selectedIcon: const Icon(Icons.notifications),
            label: 'Notifikasi',
          ),
        ],
      ),
    );
  }
}
