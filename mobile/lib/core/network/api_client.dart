import 'package:dio/dio.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../config/app_config.dart';

class ApiException implements Exception {
  final String message;
  final int? statusCode;
  final Map<String, dynamic>? errors;

  ApiException(this.message, {this.statusCode, this.errors});

  @override
  String toString() => message;
}

class ApiClient {
  static const String _tokenKey = 'sanctum_token';
  static const String _baseUrlKey = 'api_base_url';

  late final Dio _dio;
  String? _token;
  String _baseUrl = AppConfig.defaultApiBaseUrl;

  VoidCallback? onUnauthorized;

  ApiClient() {
    _dio = Dio(
      BaseOptions(
        baseUrl: _baseUrl,
        connectTimeout: const Duration(seconds: 15),
        receiveTimeout: const Duration(seconds: 25),
        headers: {
          'Accept': 'application/json',
        },
      ),
    );

    _dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) {
          options.baseUrl = _baseUrl;
          if (_token != null && _token!.isNotEmpty) {
            options.headers['Authorization'] = 'Bearer $_token';
          } else {
            options.headers.remove('Authorization');
          }
          handler.next(options);
        },
        onError: (error, handler) {
          if (error.response?.statusCode == 401 && _token != null) {
            onUnauthorized?.call();
          }
          handler.next(error);
        },
      ),
    );
  }

  String get baseUrl => _baseUrl;
  String? get token => _token;

  Future<void> init() async {
    final prefs = await SharedPreferences.getInstance();
    _token = prefs.getString(_tokenKey);
    final savedUrl = prefs.getString(_baseUrlKey);
    if (savedUrl != null && savedUrl.trim().isNotEmpty) {
      _baseUrl = _normalizeBaseUrl(savedUrl);
    }
    _dio.options.baseUrl = _baseUrl;
  }

  Future<void> setToken(String? token) async {
    _token = token;
    final prefs = await SharedPreferences.getInstance();
    if (token == null || token.isEmpty) {
      await prefs.remove(_tokenKey);
    } else {
      await prefs.setString(_tokenKey, token);
    }
  }

  Future<void> setBaseUrl(String rawUrl) async {
    final normalized = _normalizeBaseUrl(rawUrl);
    _baseUrl = normalized;
    _dio.options.baseUrl = normalized;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_baseUrlKey, normalized);
  }

  String _normalizeBaseUrl(String raw) {
    var url = raw.trim();
    while (url.endsWith('/')) {
      url = url.substring(0, url.length - 1);
    }
    if (!url.endsWith('/api')) {
      url = '$url/api';
    }
    return url;
  }

  Future<Map<String, dynamic>> get(String path, {Map<String, dynamic>? query}) async {
    try {
      final res = await _dio.get<dynamic>(path, queryParameters: query);
      return _unwrapMap(res.data);
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<Map<String, dynamic>> post(String path, {dynamic data}) async {
    try {
      final res = await _dio.post<dynamic>(path, data: data);
      return _unwrapMap(res.data);
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<Map<String, dynamic>> put(String path, {dynamic data}) async {
    try {
      final res = await _dio.put<dynamic>(path, data: data);
      return _unwrapMap(res.data);
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Map<String, dynamic> _unwrapMap(dynamic raw) {
    if (raw is Map<String, dynamic>) return raw;
    if (raw is Map) return Map<String, dynamic>.from(raw);
    return {'data': raw};
  }

  ApiException _handleDioError(DioException e) {
    final res = e.response;
    if (res == null) {
      if (e.type == DioExceptionType.connectionTimeout ||
          e.type == DioExceptionType.receiveTimeout) {
        return ApiException('Koneksi ke server timeout. Periksa jaringan atau URL server.');
      }
      return ApiException('Tidak dapat terhubung ke server ($_baseUrl).');
    }

    final data = res.data;
    String message = 'Terjadi kesalahan pada server (${res.statusCode}).';
    Map<String, dynamic>? errors;

    if (data is Map) {
      if (data['message'] != null && data['message'].toString().isNotEmpty) {
        message = data['message'].toString();
      }
      if (data['errors'] is Map) {
        errors = Map<String, dynamic>.from(data['errors'] as Map);
        final firstField = errors.values.first;
        if (firstField is List && firstField.isNotEmpty) {
          message = firstField.first.toString();
        }
      }
    }

    return ApiException(message, statusCode: res.statusCode, errors: errors);
  }
}

typedef VoidCallback = void Function();
