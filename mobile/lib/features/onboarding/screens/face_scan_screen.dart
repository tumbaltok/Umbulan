import 'dart:io';
import 'package:camera/camera.dart';
import 'package:flutter/foundation.dart';
import 'package:material_ui/material_ui.dart';
import 'package:google_mlkit_face_detection/google_mlkit_face_detection.dart';
import 'package:permission_handler/permission_handler.dart';
import 'package:provider/provider.dart';
import '../../../core/network/api_client.dart';
import '../../../core/providers/auth_provider.dart';
import '../../../core/services/face_extractor_service.dart';
import '../../../core/theme/app_theme.dart';

enum FaceScanMode { register, attendance }

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
  final FaceDetector _faceDetector = FaceDetector(
    options: FaceDetectorOptions(
      enableClassification: true,
      enableLandmarks: true,
      performanceMode: FaceDetectorMode.accurate,
    ),
  );

  bool _initializing = true;
  bool _processing = false;
  String? _errorText;
  String _statusText = 'Posisikan wajah tegak lurus di dalam lingkaran';

  @override
  void initState() {
    super.initState();
    _initCamera();
  }

  @override
  void dispose() {
    _cameraCtrl?.dispose();
    _faceDetector.close();
    super.dispose();
  }

  Future<void> _initCamera() async {
    try {
      final status = await Permission.camera.request();
      if (!status.isGranted) {
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

      final ctrl = CameraController(
        front,
        ResolutionPreset.medium,
        enableAudio: false,
      );
      await ctrl.initialize();

      if (!mounted) return;
      setState(() {
        _cameraCtrl = ctrl;
        _initializing = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _errorText = 'Kamera tidak tersedia: $e';
        _initializing = false;
      });
    }
  }

  Future<void> _captureAndProcess() async {
    if (_cameraCtrl == null || !_cameraCtrl!.value.isInitialized || _processing) return;

    setState(() {
      _processing = true;
      _statusText = 'Memeriksa keaktifan & kualitas wajah...';
    });

    final extractor = context.read<FaceDescriptorExtractorService>();
    final api = context.read<ApiClient>();
    final auth = context.read<AuthProvider>();
    final messenger = ScaffoldMessenger.of(context);
    final navigator = Navigator.of(context);

    try {
      final file = await _cameraCtrl!.takePicture();
      final inputImage = InputImage.fromFilePath(file.path);
      final faces = await _faceDetector.processImage(inputImage);

      if (faces.isEmpty) {
        if (mounted) {
          setState(() {
            _processing = false;
            _statusText = 'Wajah belum terdeteksi. Posisikan wajah lebih jelas ke kamera.';
          });
        }
        return;
      }

      final face = faces.first;
      final headY = face.headEulerAngleY?.abs() ?? 0;
      final headZ = face.headEulerAngleZ?.abs() ?? 0;
      if (headY > 18 || headZ > 18) {
        if (mounted) {
          setState(() {
            _processing = false;
            _statusText = 'Mohon hadap lurus tegak ke kamera.';
          });
        }
        return;
      }

      if (mounted) {
        setState(() => _statusText = 'Mengekstrak vektor biometrik 128-point...');
      }
      final bytes = await File(file.path).readAsBytes();
      final descriptor = await extractor.extractDescriptorFromBytes(bytes);

      if (widget.mode == FaceScanMode.register) {
        if (mounted) {
          setState(() => _statusText = 'Menyimpan data biometrik ke server...');
        }
        await api.post(
          '/user/face/register',
          data: {'face_descriptor': descriptor},
        );
        await auth.refreshMe();
        if (mounted) {
          messenger.showSnackBar(
            const SnackBar(content: Text('Biometrik wajah berhasil didaftarkan!')),
          );
          navigator.pop(true);
        }
        return;
      }

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
      if (mounted) {
        setState(() {
          _processing = false;
          _statusText = e.toString().replaceFirst('Exception: ', '');
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final isReg = widget.mode == FaceScanMode.register;

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
                  ? 'Perekaman biometrik dilakukan 1 kali untuk keamanan absensi Anda.'
                  : 'Posisikan wajah di dalam lingkaran untuk verifikasi presensi.',
              textAlign: TextAlign.center,
              style: const TextStyle(fontSize: 13.5, color: AppTheme.textSecondary),
            ),
            const Spacer(),
            Center(
              child: Container(
                width: 268,
                height: 268,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  border: Border.all(
                    color: _processing ? AppTheme.accent : AppTheme.primary,
                    width: 4,
                  ),
                  boxShadow: [
                    BoxShadow(
                      color: AppTheme.primary.withValues(alpha: 0.08),
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
                                  style: const TextStyle(color: AppTheme.danger, fontSize: 13),
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
            ),
            const SizedBox(height: 24),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
              decoration: BoxDecoration(
                color: const Color(0xFFF1F5F9),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(
                    _processing ? Icons.hourglass_top_rounded : Icons.info_outline,
                    size: 18,
                    color: AppTheme.primary,
                  ),
                  const SizedBox(width: 8),
                  Flexible(
                    child: Text(
                      _statusText,
                      textAlign: TextAlign.center,
                      style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w500),
                    ),
                  ),
                ],
              ),
            ),
            const Spacer(),
            FilledButton.icon(
              onPressed: (_initializing || _errorText != null || _processing)
                  ? null
                  : _captureAndProcess,
              icon: _processing
                  ? const SizedBox(
                      width: 18,
                      height: 18,
                      child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                    )
                  : const Icon(Icons.camera_alt_outlined),
              label: Text(isReg ? 'Simpan Biometrik Wajah' : 'Verifikasi & Lanjutkan'),
            ),
            const SizedBox(height: 12),
          ],
        ),
      ),
    );
  }
}
