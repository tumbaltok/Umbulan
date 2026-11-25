import 'dart:convert';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import '../config/app_config.dart';
import '../network/api_client.dart';

@pragma('vm:entry-point')
Future<void> firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  try {
    await Firebase.initializeApp();
  } catch (_) {}
}

class PushNotificationService {
  final FlutterLocalNotificationsPlugin _localNotifications =
      FlutterLocalNotificationsPlugin();

  bool _firebaseAvailable = false;
  String? _currentFcmToken;

  String? get currentFcmToken => _currentFcmToken;
  bool get isFirebaseAvailable => _firebaseAvailable;

  Future<void> initialize({required ApiClient apiClient}) async {
    await _initLocalNotifications();

    try {
      await Firebase.initializeApp();
      _firebaseAvailable = true;
      FirebaseMessaging.onBackgroundMessage(firebaseMessagingBackgroundHandler);

      final messaging = FirebaseMessaging.instance;
      await messaging.requestPermission(
        alert: true,
        badge: true,
        sound: true,
      );

      messaging.onRegistered.listen((newToken) async {
        _currentFcmToken = newToken;
        if (apiClient.token != null) {
          await syncTokenWithServer(apiClient, newToken);
        }
      });

      await messaging.register();

      FirebaseMessaging.onMessage.listen((RemoteMessage message) {
        final notif = message.notification;
        if (notif != null) {
          showLocalBanner(
            title: notif.title ?? AppConfig.appName,
            body: notif.body ?? '',
            payload: jsonEncode(message.data),
          );
        }
      });
    } catch (e) {
      // Aman saat file google-services.json / GoogleService-Info.plist belum dipasang;
      // aplikasi tetap berjalan penuh memakai notifikasi in-app (/api/notifications).
      _firebaseAvailable = false;
      debugPrint('[PushNotificationService] Firebase belum dikonfigurasi: $e');
    }
  }

  Future<void> syncTokenWithServer(ApiClient apiClient, String token) async {
    try {
      await apiClient.post('/devices/fcm-token', data: {
        'fcm_token': token,
        'device_name': '${AppConfig.appName} ${AppConfig.platformCode.toUpperCase()}',
        'platform': AppConfig.platformCode,
      });
    } catch (e) {
      debugPrint('[PushNotificationService] Gagal sinkron FCM token: $e');
    }
  }

  Future<void> _initLocalNotifications() async {
    const androidSettings = AndroidInitializationSettings('@mipmap/ic_launcher');
    const darwinSettings = DarwinInitializationSettings();
    const initSettings = InitializationSettings(
      android: androidSettings,
      iOS: darwinSettings,
    );

    await _localNotifications.initialize(settings: initSettings);

    const channel = AndroidNotificationChannel(
      'umbulan_high_importance',
      'Notifikasi Persetujuan & Pengajuan',
      description: 'Notifikasi pengajuan Cuti, CAR, MPR, dan persetujuan atasan',
      importance: Importance.high,
    );

    await _localNotifications
        .resolvePlatformSpecificImplementation<
            AndroidFlutterLocalNotificationsPlugin>()
        ?.createNotificationChannel(channel);
  }

  Future<void> showLocalBanner({
    required String title,
    required String body,
    String? payload,
  }) async {
    const androidDetails = AndroidNotificationDetails(
      'umbulan_high_importance',
      'Notifikasi Persetujuan & Pengajuan',
      importance: Importance.max,
      priority: Priority.high,
    );
    const darwinDetails = DarwinNotificationDetails();
    const details = NotificationDetails(
      android: androidDetails,
      iOS: darwinDetails,
    );

    await _localNotifications.show(
      id: DateTime.now().millisecondsSinceEpoch ~/ 1000,
      title: title,
      body: body,
      notificationDetails: details,
      payload: payload,
    );
  }
}
