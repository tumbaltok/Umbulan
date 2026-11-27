import 'package:material_ui/material_ui.dart';
import 'package:provider/provider.dart';
import 'core/config/app_config.dart';
import 'core/network/api_client.dart';
import 'core/providers/auth_provider.dart';
import 'core/services/push_notification_service.dart';
import 'core/theme/app_theme.dart';
import 'features/auth/screens/login_screen.dart';
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
      return const Scaffold(
        body: Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              CircularProgressIndicator(),
              SizedBox(height: 14),
              Text('Memuat ${AppConfig.appName}...'),
            ],
          ),
        ),
      );
    }

    return auth.isAuthenticated ? const MainShellScreen() : const LoginScreen();
  }
}
