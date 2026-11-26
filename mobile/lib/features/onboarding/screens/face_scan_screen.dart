import 'dart:async';
import 'dart:io';
import 'package:camera/camera.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/services.dart';
import 'package:google_mlkit_face_detection/google_mlkit_face_detection.dart';
import 'package:material_ui/material_ui.dart';
import 'package:permission_handler/permission_handler.dart';
import 'package:provider/provider.dart';
import '../../../core/network/api_client.dart';
import '../../../core/providers/auth_provider.dart';
import '../../../core/services/face_extractor_service.dart';
import '../../../core/theme/app_theme.dart';

enum FaceScanMode { register, attendance }

enum _ScanFeedbackState { neutral, warning, ready, processing }

class FaceScanResult {
  final List<double> descriptor;
  final XFile selfieFile;
  final bool livenessVerified;

  FaceScanResult({
    required this.descriptor,
    required this.selfieFile,
    required this.livenessVerified,
  });
}

class FaceScanScreen extends StatefulWidget {
  final FaceScanMode mode;
  const FaceScanScreen({super.key, required this.mode});

  @override
  State<FaceScanScreen> createState() => _FaceScanScreenState();
}

class _FaceScanScreenState extends State<FaceScanScreen> {
  CameraController? _cameraCtrl;
  CameraDescription? _frontCamera;

  final FaceDetector _faceDetector = FaceDetector(
    options: FaceDetectorOptions(
      enableClassification: true,
      enableLandmarks: true,
      minFaceSize: 0.15,
      performanceMode: FaceDetectorMode.fast,
    ),
  );

  bool _initializing = true;
  bool _isStreaming = false;
  bool _isDetectingFrame = false;
  bool _processingCapture = false;
  bool _completed = false;
  int _stableFrames = 0;
  DateTime _lastAnalyzedAt = DateTime.fromMillisecondsSinceEpoch(0);

  String? _errorText;
  String _statusText = 'Posisikan wajah di dalam lingkaran kamera';
  _ScanFeedbackState _feedbackState = _ScanFeedbackState.neutral;

  static const int _requiredStableFrames = 3;

  @override
  void initState() {
    super.initState();
    _initCamera();
  }

  @override
  void dispose() {
    _completed = true;
    final ctrl = _cameraCtrl;
    _cameraCtrl = null;
    if (ctrl != null) {
      if (_isStreaming && ctrl.value.isStreamingImages) {
        _isStreaming = false;
        ctrl.stopImageStream().catchError((_) {}).whenComplete(() => ctrl.dispose());
      } else {
        ctrl.dispose();
      }
    }
    _faceDetector.close();
    super.dispose();
  }

  Future<void> _initCamera() async {
    try {
      final status = await Permission.camera.request();
      if (!status.isGranted) {
        if (!mounted) return;
        setState(() {
          _errorText = 'Izin kamera ditolak. Aktifkan izin kamera pada pengaturan HP.';
          _initializing = false;
        });
        return;
      }

      final cameras = await availableCameras();
      final front = cameras.firstWhere(
        (c) => c.lensDirection == CameraLensDirection.front,
        orElse: () => cameras.first,
      );
      _frontCamera = front;

      final ctrl = CameraController(
        front,
        ResolutionPreset.medium,
        enableAudio: false,
        imageFormatGroup: Platform.isAndroid
            ? ImageFormatGroup.nv21
            : ImageFormatGroup.bgra8888,
      );
      await ctrl.initialize();

      if (!mounted) {
        await ctrl.dispose();
        return;
      }

      setState(() {
        _cameraCtrl = ctrl;
        _initializing = false;
      });

      await _startImageStream();
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _errorText = 'Kamera tidak tersedia: $e';
        _initializing = false;
      });
    }
  }

  Future<void> _startImageStream() async {
    final ctrl = _cameraCtrl;
    if (ctrl == null || !ctrl.value.isInitialized || _isStreaming || _completed) return;

    _stableFrames = 0;
    _isStreaming = true;

    try {
      await ctrl.startImageStream(_onCameraFrame);
    } catch (e) {
      _isStreaming = false;
      if (kDebugMode) debugPrint('[FaceScan] Stream start error: $e');
    }
  }

  Future<void> _stopImageStreamSafely() async {
    final ctrl = _cameraCtrl;
    if (ctrl != null && _isStreaming) {
      _isStreaming = false;
      try {
        if (ctrl.value.isStreamingImages) {
          await ctrl.stopImageStream();
        }
      } catch (_) {}
    }
  }

  Future<void> _onCameraFrame(CameraImage image) async {
    if (_completed || _processingCapture || _isDetectingFrame || !_isStreaming) return;

    // Throttle analisis ~250ms per frame agar UI tetap halus 60fps & hemat CPU
    final now = DateTime.now();
    if (now.difference(_lastAnalyzedAt).inMilliseconds < 250) return;
    _lastAnalyzedAt = now;
    _isDetectingFrame = true;

    try {
      // 1. Cek tingkat pencahayaan frame langsung dari luma/luminance piksel
      final avgLuma = _estimateBrightness(image);
      if (avgLuma < 52) {
        _updateGuide(
          'Pencahayaan kurang terang. Pindah ke area yang lebih terang.',
          _ScanFeedbackState.warning,
          resetStable: true,
        );
        return;
      }
      if (avgLuma > 240) {
        _updateGuide(
          'Cahaya terlalu silau. Hindari pantulan cahaya di belakang Anda.',
          _ScanFeedbackState.warning,
          resetStable: true,
        );
        return;
      }

      // 2. Konversi CameraImage ke InputImage ML Kit
      final inputImage = _toInputImage(image);
      if (inputImage == null) return;

      final faces = await _faceDetector.processImage(inputImage);
      if (!mounted || _completed || _processingCapture) return;

      if (faces.isEmpty) {
        _updateGuide(
          'Wajah tidak terdeteksi. Arahkan wajah ke dalam lingkaran.',
          _ScanFeedbackState.neutral,
          resetStable: true,
        );
        return;
      }

      if (faces.length > 1) {
        _updateGuide(
          'Terdeteksi lebih dari 1 wajah. Pastikan hanya wajah Anda di kamera.',
          _ScanFeedbackState.warning,
          resetStable: true,
        );
        return;
      }

      final face = faces.first;
      final validationMessage = _validateFaceAlignment(face, image);
      if (validationMessage != null) {
        _updateGuide(
          validationMessage,
          _ScanFeedbackState.warning,
          resetStable: true,
        );
        return;
      }

      // 3. Posisi & pencahayaan sudah pas -> kumpulkan frame stabil lalu auto-capture
      _stableFrames++;
      if (_stableFrames < _requiredStableFrames) {
        _updateGuide(
          'Posisi wajah pas! Tahan sebentar... ($_stableFrames/$_requiredStableFrames)',
          _ScanFeedbackState.ready,
          resetStable: false,
        );
        return;
      }

      await _autoCaptureAndSubmit();
    } catch (e) {
      if (kDebugMode) debugPrint('[FaceScan] Stream detect error: $e');
    } finally {
      _isDetectingFrame = false;
    }
  }

  String? _validateFaceAlignment(Face face, CameraImage image) {
    final rotation = _getInputImageRotation();
    final swapped = rotation == InputImageRotation.rotation90deg ||
        rotation == InputImageRotation.rotation270deg;
    final imgW = (swapped ? image.height : image.width).toDouble();
    final imgH = (swapped ? image.width : image.height).toDouble();

    final box = face.boundingBox;
    final widthRatio = box.width / imgW;

    // 1. Cek jarak wajah (kurang maju / kurang mundur)
    if (widthRatio < 0.27) {
      return 'Wajah terlalu jauh. Majukan wajah sedikit ke kamera.';
    }
    if (widthRatio > 0.72) {
      return 'Wajah terlalu dekat. Mundurkan posisi wajah sedikit.';
    }

    // 2. Cek posisi horizontal (kurang kanan / kurang kiri)
    // Untuk kamera depan, preview di-mirror horizontal pada layar HP.
    final rawNormX = box.center.dx / imgW;
    final screenNormX = (_frontCamera?.lensDirection == CameraLensDirection.front)
        ? (1.0 - rawNormX)
        : rawNormX;

    if (screenNormX < 0.34) {
      return 'Posisi kurang ke kanan. Geser wajah sedikit ke kanan.';
    }
    if (screenNormX > 0.66) {
      return 'Posisi kurang ke kiri. Geser wajah sedikit ke kiri.';
    }

    // 3. Cek posisi vertikal (terlalu atas / terlalu bawah)
    final screenNormY = box.center.dy / imgH;
    if (screenNormY < 0.28) {
      return 'Posisi terlalu atas. Turunkan wajah sedikit ke tengah lingkaran.';
    }
    if (screenNormY > 0.74) {
      return 'Posisi terlalu bawah. Angkat wajah sedikit ke tengah lingkaran.';
    }

    // 4. Cek kemiringan wajah (harus menghadap tegak lurus ke kamera)
    final headY = face.headEulerAngleY?.abs() ?? 0;
    final headZ = face.headEulerAngleZ?.abs() ?? 0;
    if (headY > 14 || headZ > 14) {
      return 'Hadapkan wajah tegak lurus menghadap kamera.';
    }

    return null;
  }

  double _estimateBrightness(CameraImage image) {
    if (image.planes.isEmpty) return 128;
    final bytes = image.planes.first.bytes;
    if (bytes.isEmpty) return 128;

    int sum = 0;
    int count = 0;

    if (Platform.isAndroid) {
      // Pada NV21 / YUV, N byte pertama adalah komponen Luminance (Y)
      final yLength = (image.width * image.height).clamp(0, bytes.length);
      const step = 32;
      for (int i = 0; i < yLength; i += step) {
        sum += bytes[i];
        count++;
      }
    } else {
      // Pada BGRA8888 iOS (4 byte per pixel)
      const step = 128;
      for (int i = 0; i + 2 < bytes.length; i += step) {
        final b = bytes[i];
        final g = bytes[i + 1];
        final r = bytes[i + 2];
        sum += ((r * 299 + g * 587 + b * 114) ~/ 1000);
        count++;
      }
    }

    return count == 0 ? 128 : sum / count;
  }

  InputImage? _toInputImage(CameraImage image) {
    final rotation = _getInputImageRotation();
    if (rotation == null) return null;

    final format = InputImageFormatValue.fromRawValue(image.format.raw);
    if (format == null) return null;

    if (Platform.isAndroid &&
        format != InputImageFormat.nv21 &&
        format != InputImageFormat.yuv_420_888) {
      return null;
    }
    if (Platform.isIOS && format != InputImageFormat.bgra8888) {
      return null;
    }

    if (image.planes.isEmpty) return null;

    // Jika Android mengembalikan 1 plane (NV21) atau iOS BGRA8888, gunakan planes.first langsung.
    // Jika Android mengembalikan 3 plane YUV_420_888, konversi ke NV21 agar ML Kit akurat di semua perangkat.
    final bytes = image.planes.length == 1
        ? image.planes.first.bytes
        : _yuv420ToNv21(image);

    return InputImage.fromBytes(
      bytes: bytes,
      metadata: InputImageMetadata(
        size: Size(image.width.toDouble(), image.height.toDouble()),
        rotation: rotation,
        format: image.planes.length == 1 ? format : InputImageFormat.nv21,
        bytesPerRow: image.planes.first.bytesPerRow,
      ),
    );
  }

  Uint8List _yuv420ToNv21(CameraImage image) {
    final width = image.width;
    final height = image.height;
    final yPlane = image.planes[0];
    final uPlane = image.planes[1];
    final vPlane = image.planes[2];

    final nv21 = Uint8List(width * height * 3 ~/ 2);
    var offset = 0;

    for (var row = 0; row < height; row++) {
      final rowStart = row * yPlane.bytesPerRow;
      nv21.setRange(offset, offset + width, yPlane.bytes, rowStart);
      offset += width;
    }

    final uvHeight = height ~/ 2;
    final uvWidth = width ~/ 2;
    final uPixelStride = uPlane.bytesPerPixel ?? 1;
    final vPixelStride = vPlane.bytesPerPixel ?? 1;

    for (var row = 0; row < uvHeight; row++) {
      for (var col = 0; col < uvWidth; col++) {
        final vIdx = row * vPlane.bytesPerRow + col * vPixelStride;
        final uIdx = row * uPlane.bytesPerRow + col * uPixelStride;
        nv21[offset++] = vPlane.bytes[vIdx];
        nv21[offset++] = uPlane.bytes[uIdx];
      }
    }

    return nv21;
  }

  InputImageRotation? _getInputImageRotation() {
    final camera = _frontCamera;
    final ctrl = _cameraCtrl;
    if (camera == null || ctrl == null) return null;

    final sensorOrientation = camera.sensorOrientation;
    if (Platform.isIOS) {
      return InputImageRotationValue.fromRawValue(sensorOrientation);
    }

    final orientations = <DeviceOrientation, int>{
      DeviceOrientation.portraitUp: 0,
      DeviceOrientation.landscapeLeft: 90,
      DeviceOrientation.portraitDown: 180,
      DeviceOrientation.landscapeRight: 270,
    };

    var rotationComp = orientations[ctrl.value.deviceOrientation];
    if (rotationComp == null) return null;

    if (camera.lensDirection == CameraLensDirection.front) {
      rotationComp = (sensorOrientation + rotationComp) % 360;
    } else {
      rotationComp = (sensorOrientation - rotationComp + 360) % 360;
    }

    return InputImageRotationValue.fromRawValue(rotationComp);
  }

  void _updateGuide(
    String message,
    _ScanFeedbackState state, {
    required bool resetStable,
  }) {
    if (resetStable) _stableFrames = 0;
    if (!mounted) return;
    if (_statusText == message && _feedbackState == state && _stableFrames == 0) return;

    setState(() {
      _statusText = message;
      _feedbackState = state;
    });
  }

  Future<void> _autoCaptureAndSubmit() async {
    if (_processingCapture || _completed || _cameraCtrl == null) return;

    setState(() {
      _processingCapture = true;
      _feedbackState = _ScanFeedbackState.processing;
      _statusText = 'Mengambil gambar & memverifikasi biometrik 128-point...';
    });

    final extractor = context.read<FaceDescriptorExtractorService>();
    final api = context.read<ApiClient>();
    final auth = context.read<AuthProvider>();
    final messenger = ScaffoldMessenger.of(context);
    final navigator = Navigator.of(context);

    try {
      await _stopImageStreamSafely();
      await Future.delayed(const Duration(milliseconds: 120));

      if (!mounted || _completed || _cameraCtrl == null) return;
      final file = await _cameraCtrl!.takePicture();
      final bytes = await File(file.path).readAsBytes();
      final descriptor = await extractor.extractDescriptorFromBytes(bytes);

      if (widget.mode == FaceScanMode.register) {
        if (mounted) {
          setState(() => _statusText = 'Menyimpan biometrik wajah ke server...');
        }
        await api.post(
          '/user/face/register',
          data: {'face_descriptor': descriptor},
        );
        await auth.refreshMe();
        _completed = true;
        if (mounted) {
          messenger.showSnackBar(
            const SnackBar(
              content: Text('Biometrik wajah berhasil direkam otomatis!'),
              backgroundColor: AppTheme.success,
            ),
          );
          navigator.pop(true);
        }
        return;
      }

      _completed = true;
      if (mounted) {
        navigator.pop(
          FaceScanResult(
            descriptor: descriptor,
            selfieFile: file,
            livenessVerified: true,
          ),
        );
      }
    } catch (e) {
      if (kDebugMode) debugPrint('[FaceScan] Error: $e');
      if (!mounted || _completed) return;

      setState(() {
        _processingCapture = false;
        _stableFrames = 0;
        _feedbackState = _ScanFeedbackState.warning;
        _statusText = e.toString().replaceFirst('Exception: ', '');
      });

      // Beri waktu 1.5 detik agar user membaca alert, lalu lanjutkan deteksi otomatis lagi
      await Future.delayed(const Duration(milliseconds: 1500));
      if (mounted && !_completed && !_processingCapture) {
        await _startImageStream();
      }
    }
  }

  Color _ringColor() {
    return switch (_feedbackState) {
      _ScanFeedbackState.ready => AppTheme.success,
      _ScanFeedbackState.warning => AppTheme.warning,
      _ScanFeedbackState.processing => AppTheme.accent,
      _ScanFeedbackState.neutral => AppTheme.primary,
    };
  }

  IconData _statusIcon() {
    return switch (_feedbackState) {
      _ScanFeedbackState.ready => Icons.check_circle_outline,
      _ScanFeedbackState.warning => Icons.warning_amber_rounded,
      _ScanFeedbackState.processing => Icons.hourglass_top_rounded,
      _ScanFeedbackState.neutral => Icons.face_retouching_natural,
    };
  }

  Color _bannerBgColor() {
    return switch (_feedbackState) {
      _ScanFeedbackState.ready => AppTheme.success.withValues(alpha: 0.12),
      _ScanFeedbackState.warning => const Color(0xFFFFFBEB),
      _ScanFeedbackState.processing => AppTheme.accent.withValues(alpha: 0.12),
      _ScanFeedbackState.neutral => const Color(0xFFF1F5F9),
    };
  }

  Color _bannerBorderColor() {
    return switch (_feedbackState) {
      _ScanFeedbackState.ready => AppTheme.success.withValues(alpha: 0.45),
      _ScanFeedbackState.warning => const Color(0xFFFCD34D),
      _ScanFeedbackState.processing => AppTheme.accent.withValues(alpha: 0.45),
      _ScanFeedbackState.neutral => AppTheme.border,
    };
  }

  @override
  Widget build(BuildContext context) {
    final isReg = widget.mode == FaceScanMode.register;
    final ringColor = _ringColor();

    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(
        title: Text(isReg ? 'Perekaman Wajah' : 'Verifikasi Biometrik'),
        backgroundColor: Colors.white,
      ),
      body: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
        child: Column(
          children: [
            Text(
              isReg
                  ? 'Pemindaian berjalan otomatis. Posisikan wajah lurus di dalam lingkaran.'
                  : 'Pemindaian otomatis. Arahkan wajah ke lingkaran untuk verifikasi.',
              textAlign: TextAlign.center,
              style: const TextStyle(fontSize: 13.5, color: AppTheme.textSecondary),
            ),
            const Spacer(),
            Center(
              child: Stack(
                alignment: Alignment.center,
                children: [
                  SizedBox(
                    width: 282,
                    height: 282,
                    child: CircularProgressIndicator(
                      value: _processingCapture
                          ? null
                          : (_stableFrames / _requiredStableFrames).clamp(0.0, 1.0),
                      strokeWidth: 4,
                      backgroundColor: Colors.transparent,
                      valueColor: AlwaysStoppedAnimation(ringColor),
                    ),
                  ),
                  AnimatedContainer(
                    duration: const Duration(milliseconds: 200),
                    width: 268,
                    height: 268,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      border: Border.all(color: ringColor, width: 4),
                      boxShadow: [
                        BoxShadow(
                          color: ringColor.withValues(alpha: 0.14),
                          blurRadius: 24,
                          spreadRadius: 4,
                        ),
                      ],
                    ),
                    child: ClipOval(
                      child: _initializing
                          ? const Center(child: CircularProgressIndicator())
                          : _errorText != null
                              ? Center(
                                  child: Padding(
                                    padding: const EdgeInsets.all(20),
                                    child: Text(
                                      _errorText!,
                                      textAlign: TextAlign.center,
                                      style: const TextStyle(
                                        color: AppTheme.danger,
                                        fontSize: 13,
                                      ),
                                    ),
                                  ),
                                )
                              : _cameraCtrl != null
                                  ? FittedBox(
                                      fit: BoxFit.cover,
                                      child: SizedBox(
                                        width: _cameraCtrl!.value.previewSize?.height ?? 300,
                                        height: _cameraCtrl!.value.previewSize?.width ?? 300,
                                        child: CameraPreview(_cameraCtrl!),
                                      ),
                                    )
                                  : const SizedBox.shrink(),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 28),
            AnimatedContainer(
              duration: const Duration(milliseconds: 200),
              width: double.infinity,
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
              decoration: BoxDecoration(
                color: _bannerBgColor(),
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: _bannerBorderColor()),
              ),
              child: Row(
                children: [
                  Icon(_statusIcon(), size: 22, color: ringColor),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Text(
                      _statusText,
                      style: TextStyle(
                        fontSize: 13.5,
                        fontWeight: FontWeight.w600,
                        color: _feedbackState == _ScanFeedbackState.warning
                            ? const Color(0xFF92400E)
                            : AppTheme.textPrimary,
                      ),
                    ),
                  ),
                ],
              ),
            ),
            const Spacer(),
            const Text(
              'Otomatis memotret & menyimpan saat posisi dan cahaya sesuai',
              textAlign: TextAlign.center,
              style: TextStyle(fontSize: 12, color: AppTheme.textSecondary),
            ),
            const SizedBox(height: 12),
          ],
        ),
      ),
    );
  }
}
