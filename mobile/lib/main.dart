import 'package:material_ui/material_ui.dart';
import 'package:provider/provider.dart';
import 'core/config/app_config.dart';
import 'core/network/api_client.dart';
import 'core/providers/auth_provider.dart';
import 'core/services/push_notification_service.dart';
import 'core/theme/app_theme.dart';
import 'features/auth/screens/login_screen.dart';
import 'features/common/widgets/ui_kit.dart';
import 'features/home/screens/main_shell_screen.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(const UmbulanApp());
}

class UmbulanApp extends StatelessWidget {
  final AuthProvider? overrideAuthProvider;

  const UmbulanApp({super.key, this.overrideAuthProvider});

  @override
  Widget build(BuildContext context) {
    if (overrideAuthProvider != null) {
      return ChangeNotifierProvider<AuthProvider>.value(
        value: overrideAuthProvider!,
        child: MaterialApp(
          title: AppConfig.appName,
          debugShowCheckedModeBanner: false,
          theme: AppTheme.lightTheme,
          home: const _RootGate(),
        ),
      );
    }

    final apiClient = ApiClient();
    final pushService = PushNotificationService();
    final authProvider = AuthProvider(apiClient: apiClient, pushService: pushService)
      ..bootstrap();

    return MultiProvider(
      providers: [
        Provider<ApiClient>.value(value: apiClient),
        Provider<PushNotificationService>.value(value: pushService),
        ChangeNotifierProvider<AuthProvider>.value(value: authProvider),
      ],
      child: MaterialApp(
        title: AppConfig.appName,
        debugShowCheckedModeBanner: false,
        theme: AppTheme.lightTheme,
        home: const _RootGate(),
      ),
    );
  }
}

class _RootGate extends StatelessWidget {
  const _RootGate();

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();

    if (auth.isBootstrapping) {
      return Scaffold(
        body: Container(
          decoration: const BoxDecoration(
            gradient: LinearGradient(
              colors: [AppTheme.primaryDark, AppTheme.primary, AppTheme.accent],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
          ),
          child: const Center(
            child: Padding(
              padding: EdgeInsets.all(24),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  UmbulanBrandMark(size: 74, darkSurface: false),
                  SizedBox(height: 18),
                  Text(
                    AppConfig.appName,
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 21,
                      fontWeight: FontWeight.w800,
                      letterSpacing: -0.3,
                    ),
                  ),
                  SizedBox(height: 6),
                  Text(
                    'Portal Layanan Karyawan',
                    style: TextStyle(
                      color: Color(0xFFE0F2FE),
                      fontSize: 13,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  SizedBox(height: 28),
                  SizedBox(
                    width: 24,
                    height: 24,
                    child: CircularProgressIndicator(
                      strokeWidth: 2.4,
                      color: Colors.white,
                    ),
                  ),
                  SizedBox(height: 12),
                  Text(
                    'Memuat ${AppConfig.appName}...',
                    style: TextStyle(color: Color(0xFFBAE6FD), fontSize: 12.5),
                  ),
                ],
              ),
            ),
          ),
        ),
      );
    }

    return auth.isAuthenticated ? const MainShellScreen() : const LoginScreen();
  }
}
