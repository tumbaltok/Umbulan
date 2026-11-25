import 'package:flutter/foundation.dart';
import '../config/app_config.dart';
import '../models/app_models.dart';
import '../network/api_client.dart';
import '../services/push_notification_service.dart';

class AuthProvider extends ChangeNotifier {
  final ApiClient apiClient;
  final PushNotificationService pushService;

  UserModel? _user;
  bool _isBootstrapping = true;
  bool _isBusy = false;
  String? _error;

  AuthProvider({required this.apiClient, required this.pushService}) {
    apiClient.onUnauthorized = () {
      _user = null;
      apiClient.setToken(null);
      notifyListeners();
    };
  }

  UserModel? get user => _user;
  bool get isBootstrapping => _isBootstrapping;
  bool get isAuthenticated => _user != null && (apiClient.token?.isNotEmpty ?? false);
  bool get isBusy => _isBusy;
  String? get error => _error;
  String get baseUrl => apiClient.baseUrl;

  Future<void> bootstrap() async {
    _isBootstrapping = true;
    notifyListeners();

    await apiClient.init();
    await pushService.initialize(apiClient: apiClient);

    if (apiClient.token != null && apiClient.token!.isNotEmpty) {
      try {
        await refreshMe();
      } catch (_) {
        await apiClient.setToken(null);
        _user = null;
      }
    }

    _isBootstrapping = false;
    notifyListeners();
  }

  Future<void> updateServerBaseUrl(String rawUrl) async {
    await apiClient.setBaseUrl(rawUrl);
    notifyListeners();
  }

  Future<bool> login({required String email, required String password}) async {
    _setBusy(true);
    try {
      final res = await apiClient.post('/login', data: {
        'email': email.trim(),
        'password': password,
        'device_name': '${AppConfig.appName} ${AppConfig.platformCode.toUpperCase()}',
        'platform': AppConfig.platformCode,
        if (pushService.currentFcmToken != null)
          'fcm_token': pushService.currentFcmToken,
      });

      final data = res['data'] as Map<String, dynamic>;
      await apiClient.setToken(data['token']?.toString());
      _user = UserModel.fromJson(data['user'] as Map<String, dynamic>);
      _error = null;
      return true;
    } catch (e) {
      _error = e.toString();
      return false;
    } finally {
      _setBusy(false);
    }
  }

  Future<bool> register({
    required String name,
    String? nip,
    required String email,
    required String password,
    required String passwordConfirmation,
    required int stationId,
    required int genderId,
    required String scheduleType,
    String? currentShiftChoice,
  }) async {
    _setBusy(true);
    try {
      final res = await apiClient.post('/register', data: {
        'name': name.trim(),
        if (nip != null && nip.trim().isNotEmpty) 'nip': nip.trim(),
        'email': email.trim(),
        'password': password,
        'password_confirmation': passwordConfirmation,
        'station_id': stationId,
        'gender_id': genderId,
        'schedule_type': scheduleType,
        if (scheduleType == 'roster' && currentShiftChoice != null)
          'current_shift_choice': currentShiftChoice,
        'device_name': '${AppConfig.appName} ${AppConfig.platformCode.toUpperCase()}',
        'platform': AppConfig.platformCode,
        if (pushService.currentFcmToken != null)
          'fcm_token': pushService.currentFcmToken,
      });

      final data = res['data'] as Map<String, dynamic>;
      await apiClient.setToken(data['token']?.toString());
      _user = UserModel.fromJson(data['user'] as Map<String, dynamic>);
      _error = null;
      return true;
    } catch (e) {
      _error = e.toString();
      return false;
    } finally {
      _setBusy(false);
    }
  }

  Future<void> refreshMe() async {
    final res = await apiClient.get('/me');
    final data = res['data'] as Map<String, dynamic>;
    _user = UserModel.fromJson(data['user'] as Map<String, dynamic>);
    notifyListeners();
  }

  Future<bool> verifyEmailOtp(String otp) async {
    _setBusy(true);
    try {
      final res = await apiClient.post('/email/verify-otp', data: {'otp': otp.trim()});
      final data = res['data'] as Map<String, dynamic>?;
      if (data != null && data['user'] is Map<String, dynamic>) {
        _user = UserModel.fromJson(data['user'] as Map<String, dynamic>);
      } else {
        await refreshMe();
      }
      _error = null;
      return true;
    } catch (e) {
      _error = e.toString();
      return false;
    } finally {
      _setBusy(false);
    }
  }

  Future<String?> resendEmailOtp() async {
    final res = await apiClient.post('/email/resend-otp');
    return res['message']?.toString();
  }

  Future<void> logout() async {
    _setBusy(true);
    try {
      await apiClient.post('/logout', data: {
        if (pushService.currentFcmToken != null)
          'fcm_token': pushService.currentFcmToken,
      });
    } catch (_) {
    } finally {
      await apiClient.setToken(null);
      _user = null;
      _error = null;
      _setBusy(false);
    }
  }

  void _setBusy(bool value) {
    _isBusy = value;
    notifyListeners();
  }
}
