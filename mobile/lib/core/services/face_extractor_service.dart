import 'dart:async';
import 'dart:convert';
import 'dart:io';
import 'package:material_ui/material_ui.dart';
import 'package:flutter/services.dart';
import 'package:webview_flutter/webview_flutter.dart';

/// Mengekstrak vektor wajah 128-D dari foto kamera native menggunakan model
/// @vladmandic/face-api lokal agar 100% kompatibel dengan verifikasi server & web.
class FaceDescriptorExtractorService {
  HttpServer? _server;
  WebViewController? _controller;
  bool _isReady = false;
  Completer<void>? _readyCompleter;
  final Map<String, Completer<List<double>>> _pending = {};

  Future<void> ensureInitialized() async {
    if (_controller != null && _readyCompleter != null) {
      return _readyCompleter!.future;
    }

    _readyCompleter = Completer<void>();
    _server = await HttpServer.bind(InternetAddress.loopbackIPv4, 0);
    final port = _server!.port;

    _server!.listen((HttpRequest request) async {
      final path = request.uri.path;
      String assetPath;
      String contentType = 'application/octet-stream';

      if (path == '/' || path == '/extractor.html') {
        assetPath = 'assets/face-api/extractor.html';
        contentType = 'text/html; charset=utf-8';
      } else if (path == '/face-api/face-api.min.js') {
        assetPath = 'assets/face-api/face-api.min.js';
        contentType = 'application/javascript; charset=utf-8';
      } else if (path.startsWith('/models/face-api/')) {
        final fileName = path.replaceFirst('/models/face-api/', '');
        assetPath = 'assets/models/face-api/$fileName';
        if (fileName.endsWith('.json')) {
          contentType = 'application/json; charset=utf-8';
        }
      } else {
        request.response.statusCode = HttpStatus.notFound;
        await request.response.close();
        return;
      }

      try {
        final data = await rootBundle.load(assetPath);
        request.response.headers.set('Content-Type', contentType);
        request.response.headers.set('Access-Control-Allow-Origin', '*');
        request.response.add(
          data.buffer.asUint8List(data.offsetInBytes, data.lengthInBytes),
        );
      } catch (e) {
        debugPrint('[FaceExtractor] Missing asset: $assetPath ($e)');
        request.response.statusCode = HttpStatus.notFound;
      } finally {
        await request.response.close();
      }
    });

    final ctrl = WebViewController();
    await ctrl.setJavaScriptMode(JavaScriptMode.unrestricted);
    await ctrl.addJavaScriptChannel(
      'FaceBridge',
      onMessageReceived: (JavaScriptMessage msg) {
        try {
          final decoded = jsonDecode(msg.message) as Map<String, dynamic>;
          if (decoded.containsKey('ready')) {
            _isReady = decoded['ready'] == true;
            if (!(_readyCompleter?.isCompleted ?? true)) {
              if (_isReady) {
                _readyCompleter?.complete();
              } else {
                _readyCompleter?.completeError(
                  Exception(decoded['message'] ?? 'Model biometrik gagal dimuat'),
                );
              }
            }
            return;
          }

          final reqId = decoded['requestId']?.toString();
          if (reqId == null || !_pending.containsKey(reqId)) return;

          final completer = _pending.remove(reqId)!;
          if (decoded['success'] == true && decoded['descriptor'] is List) {
            final list = (decoded['descriptor'] as List)
                .map((e) => (e as num).toDouble())
                .toList();
            if (list.length == 128) {
              completer.complete(list);
              return;
            }
          }
          completer.completeError(
            Exception(decoded['message']?.toString() ?? 'Wajah tidak terbaca.'),
          );
        } catch (e) {
          debugPrint('[FaceExtractor] Bridge parse error: $e');
        }
      },
    );

    await ctrl.loadRequest(Uri.parse('http://127.0.0.1:$port/extractor.html'));
    _controller = ctrl;
    return _readyCompleter!.future.timeout(
      const Duration(seconds: 30),
      onTimeout: () => throw TimeoutException('Model wajah memerlukan waktu terlalu lama.'),
    );
  }

  /// Widget offstage berukuran 1x1 agar engine JS WebView dapat mengekstrak vektor wajah.
  Widget buildOffstageHost() {
    if (_controller == null) return const SizedBox.shrink();
    return SizedBox(
      width: 1,
      height: 1,
      child: Opacity(
        opacity: 0.01,
        child: WebViewWidget(controller: _controller!),
      ),
    );
  }

  Future<List<double>> extractDescriptorFromBytes(Uint8List jpegBytes) async {
    await ensureInitialized();
    final reqId = DateTime.now().microsecondsSinceEpoch.toString();
    final completer = Completer<List<double>>();
    _pending[reqId] = completer;

    final dataUrl = 'data:image/jpeg;base64,${base64Encode(jpegBytes)}';
    await _controller!.runJavaScript(
      'window.extractDescriptor(${jsonEncode(reqId)}, ${jsonEncode(dataUrl)});',
    );

    return completer.future.timeout(
      const Duration(seconds: 25),
      onTimeout: () {
        _pending.remove(reqId);
        throw TimeoutException('Ekstraksi wajah timeout.');
      },
    );
  }

  Future<void> dispose() async {
    await _server?.close(force: true);
    _server = null;
    _controller = null;
    _isReady = false;
  }
}
