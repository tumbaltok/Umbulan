import 'dart:io';

class AppConfig {
  AppConfig._();

  static const String appName = 'META Umbulan';
  static const String bundleId = 'com.meta.umbulan';

  // Default URL API backend; dapat diubah langsung lewat dialog Pengaturan Server di aplikasi.
  static String get defaultApiBaseUrl {
    if (Platform.isAndroid) {
      return 'http://10.0.2.2:8000/api';
    }
    return 'http://umbulan.test/api';
  }

  static String get platformCode {
    if (Platform.isIOS) return 'ios';
    if (Platform.isAndroid) return 'android';
    return 'web';
  }
}
