import 'dart:convert';
import 'dart:math';
import 'package:dio/dio.dart';
import 'package:material_ui/material_ui.dart';
import 'package:geolocator/geolocator.dart';
import 'package:image_picker/image_picker.dart';
import 'package:provider/provider.dart';
import '../../../core/models/app_models.dart';
import '../../../core/network/api_client.dart';
import '../../../core/providers/auth_provider.dart';
import '../../../core/theme/app_theme.dart';
import '../../onboarding/screens/face_scan_screen.dart';

class AttendanceScreen extends StatefulWidget {
  final VoidCallback onSuccessRefresh;
  const AttendanceScreen({super.key, required this.onSuccessRefresh});

  @override
  State<AttendanceScreen> createState() => _AttendanceScreenState();
}

class _AttendanceScreenState extends State<AttendanceScreen> {
  bool _loading = true;
  bool _submitting = false;
  Position? _position;
  String? _gpsError;
  List<StationModel> _stations = [];
  Map<String, dynamic> _todaySchedule = {};
  AttendanceRecord? _todayAttendance;
  StationModel? _nearestStation;
  double? _nearestDistance;
  bool _isInRadius = false;

  @override
  void initState() {
    super.initState();
    _loadContext();
  }

  Future<void> _loadContext() async {
    setState(() {
      _loading = true;
      _gpsError = null;
    });

    try {
      final dash = await context.read<ApiClient>().get('/dashboard');
      final data = dash['data'] as Map<String, dynamic>;
      _stations = (data['stations'] as List? ?? [])
          .whereType<Map<String, dynamic>>()
          .map(StationModel.fromJson)
          .toList();
      _todaySchedule = (data['today_schedule'] as Map<String, dynamic>?) ?? {};
      _todayAttendance = data['today_attendance'] is Map<String, dynamic>
          ? AttendanceRecord.fromJson(data['today_attendance'] as Map<String, dynamic>)
          : null;

      await _refreshGps();
    } catch (e) {
      if (mounted) {
        setState(() => _gpsError = e.toString());
      }
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _refreshGps() async {
    final serviceEnabled = await Geolocator.isLocationServiceEnabled();
    if (!serviceEnabled) {
      setState(() => _gpsError = 'Layanan GPS perangkat belum aktif.');
      return;
    }

    var perm = await Geolocator.checkPermission();
    if (perm == LocationPermission.denied) {
      perm = await Geolocator.requestPermission();
    }
    if (perm == LocationPermission.denied || perm == LocationPermission.deniedForever) {
      setState(() => _gpsError = 'Izin lokasi ditolak. Aktifkan izin lokasi di pengaturan.');
      return;
    }

    final pos = await Geolocator.getCurrentPosition(
      locationSettings: const LocationSettings(accuracy: LocationAccuracy.high),
    );
    _position = pos;
    _evaluateNearestStation(pos);
    if (mounted) setState(() => _gpsError = null);
  }

  void _evaluateNearestStation(Position pos) {
    StationModel? nearest;
    double? bestDist;
    bool inRadius = false;

    for (final st in _stations) {
      if (st.latitude == null || st.longitude == null) continue;
      final dist = _haversineMeters(
        pos.latitude,
        pos.longitude,
        st.latitude!,
        st.longitude!,
      );
      if (bestDist == null || dist < bestDist) {
        bestDist = dist;
        nearest = st;
      }
      if (dist <= st.radiusMeters) {
        inRadius = true;
      }
    }

    _nearestStation = nearest;
    _nearestDistance = bestDist;
    _isInRadius = inRadius;
  }

  double _haversineMeters(double lat1, double lon1, double lat2, double lon2) {
    const r = 6371000.0;
    final dLat = (lat2 - lat1) * pi / 180.0;
    final dLon = (lon2 - lon1) * pi / 180.0;
    final a = sin(dLat / 2) * sin(dLat / 2) +
        cos(lat1 * pi / 180.0) * cos(lat2 * pi / 180.0) * sin(dLon / 2) * sin(dLon / 2);
    return r * 2 * atan2(sqrt(a), sqrt(1 - a));
  }

  Future<void> _handleAttendance({required bool isCheckIn}) async {
    final user = context.read<AuthProvider>().user;
    if (user == null) return;

    final api = context.read<ApiClient>();
    final messenger = ScaffoldMessenger.of(context);

    if (!user.hasFaceDescriptor) {
      messenger.showSnackBar(
        const SnackBar(
          content: Text('Rekam wajah Anda terlebih dahulu di Beranda sebelum absensi.'),
        ),
      );
      return;
    }

    if (_position == null) {
      await _refreshGps();
      if (_position == null || !mounted) return;
    }

    setState(() => _submitting = true);
    try {
      final challenge = await api.get('/attendance/challenge');
      final nonce = challenge['nonce']?.toString() ?? '';

      if (!mounted) return;
      final scan = await Navigator.push<FaceScanResult>(
        context,
        MaterialPageRoute(
          builder: (_) => const FaceScanScreen(mode: FaceScanMode.attendance),
        ),
      );

      if (scan == null || !mounted) {
        setState(() => _submitting = false);
        return;
      }

      String? reason;
      XFile? evidenceFile;

      // Jika di luar radius (atau server meminta alasan), tampilkan dialog alasan cepat.
      if (!_isInRadius) {
        final extra = await _askReasonAndEvidence(
          title: 'Konfirmasi Lokasi Luar Radius',
          subtitle: 'Anda terdeteksi di luar radius stasiun (${_nearestDistance?.round() ?? '-'}m). Wajib isi alasan.',
        );
        if (extra == null) {
          if (mounted) setState(() => _submitting = false);
          return;
        }
        reason = extra['reason'] as String?;
        evidenceFile = extra['evidence'] as XFile?;
      }

      await _submitToServer(
        isCheckIn: isCheckIn,
        nonce: nonce,
        scan: scan,
        reason: reason,
        evidenceFile: evidenceFile,
      );
    } on ApiException catch (e) {
      if (e.statusCode == 422 &&
          e.message.toLowerCase().contains('alasan') &&
          mounted) {
        messenger.showSnackBar(
          SnackBar(content: Text(e.message), backgroundColor: AppTheme.warning),
        );
      } else if (mounted) {
        messenger.showSnackBar(
          SnackBar(content: Text(e.message), backgroundColor: AppTheme.danger),
        );
      }
    } catch (e) {
      if (mounted) {
        messenger.showSnackBar(
          SnackBar(content: Text(e.toString()), backgroundColor: AppTheme.danger),
        );
      }
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  Future<void> _submitToServer({
    required bool isCheckIn,
    required String nonce,
    required FaceScanResult scan,
    String? reason,
    XFile? evidenceFile,
  }) async {
    final pos = _position!;
    final api = context.read<ApiClient>();
    final messenger = ScaffoldMessenger.of(context);
    final map = <String, dynamic>{
      'latitude': pos.latitude.toString(),
      'longitude': pos.longitude.toString(),
      'accuracy': pos.accuracy.toString(),
      'is_face_verified': '1',
      'is_liveness_verified': scan.livenessVerified ? '1' : '0',
      'biometric_nonce': nonce,
      'face_descriptor': jsonEncode(scan.descriptor),
      'selfie_frame': await MultipartFile.fromFile(
        scan.selfieFile.path,
        filename: 'selfie_${DateTime.now().millisecondsSinceEpoch}.jpg',
      ),
      if (reason != null && reason.isNotEmpty) 'reason': reason,
      if (evidenceFile != null)
        'evidence': await MultipartFile.fromFile(
          evidenceFile.path,
          filename: evidenceFile.name,
        ),
    };

    final endpoint = isCheckIn ? '/attendance/check-in' : '/attendance/check-out';
    try {
      final res = await api.post(
        endpoint,
        data: FormData.fromMap(map),
      );
      if (mounted) {
        messenger.showSnackBar(
          SnackBar(
            content: Text(res['message']?.toString() ?? 'Absensi berhasil tercatat.'),
            backgroundColor: AppTheme.success,
          ),
        );
        widget.onSuccessRefresh();
        await _loadContext();
      }
    } on ApiException catch (e) {
      if (e.statusCode == 422 &&
          e.message.toLowerCase().contains('alasan') &&
          (reason == null || reason.isEmpty) &&
          mounted) {
        final extra = await _askReasonAndEvidence(
          title: 'Alasan Wajib Diisi',
          subtitle: e.message,
        );
        if (extra != null && mounted) {
          final freshChallenge = await api.get('/attendance/challenge');
          if (!mounted) return;
          await _submitToServer(
            isCheckIn: isCheckIn,
            nonce: freshChallenge['nonce']?.toString() ?? nonce,
            scan: scan,
            reason: extra['reason'] as String?,
            evidenceFile: extra['evidence'] as XFile?,
          );
          return;
        }
      }
      rethrow;
    }
  }

  Future<Map<String, dynamic>?> _askReasonAndEvidence({
    required String title,
    required String subtitle,
  }) async {
    final reasonCtrl = TextEditingController();
    XFile? picked;

    return showDialog<Map<String, dynamic>>(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setDialogState) => AlertDialog(
          title: Text(title, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w700)),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(subtitle, style: const TextStyle(fontSize: 13, color: AppTheme.textSecondary)),
              const SizedBox(height: 12),
              TextField(
                controller: reasonCtrl,
                maxLines: 3,
                decoration: const InputDecoration(
                  labelText: 'Alasan Wajib *',
                  hintText: 'Contoh: Dinas lapangan / kendala perjalanan',
                ),
              ),
              const SizedBox(height: 10),
              OutlinedButton.icon(
                onPressed: () async {
                  final img = await ImagePicker().pickImage(source: ImageSource.camera, imageQuality: 80);
                  if (img != null) setDialogState(() => picked = img);
                },
                icon: const Icon(Icons.attach_file, size: 18),
                label: Text(picked == null ? 'Foto Bukti (Opsional)' : picked!.name),
              ),
            ],
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(ctx, null),
              child: const Text('Batal'),
            ),
            FilledButton(
              onPressed: () {
                if (reasonCtrl.text.trim().isEmpty) return;
                Navigator.pop(ctx, {
                  'reason': reasonCtrl.text.trim(),
                  'evidence': picked,
                });
              },
              child: const Text('Kirim Absensi'),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final hasIn = _todayAttendance?.checkIn != null;
    final hasOut = _todayAttendance?.checkOut != null;
    final isDayOff = _todaySchedule['is_day_off'] == true;

    return RefreshIndicator(
      onRefresh: _loadContext,
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          if (_loading)
            const Padding(
              padding: EdgeInsets.all(48),
              child: Center(child: CircularProgressIndicator()),
            )
          else ...[
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Icon(
                          _isInRadius ? Icons.gps_fixed : Icons.gps_not_fixed,
                          color: _isInRadius ? AppTheme.success : AppTheme.warning,
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Text(
                            _gpsError ??
                                (_isInRadius
                                    ? 'Dalam Radius (${_nearestStation?.name ?? 'Stasiun'})'
                                    : 'Di Luar Radius (${_nearestDistance?.round() ?? '-'} m dari ${_nearestStation?.name ?? 'Stasiun'})'),
                            style: TextStyle(
                              fontWeight: FontWeight.w700,
                              color: _gpsError != null
                                  ? AppTheme.danger
                                  : (_isInRadius ? AppTheme.success : AppTheme.warning),
                            ),
                          ),
                        ),
                        IconButton(
                          onPressed: _refreshGps,
                          icon: const Icon(Icons.my_location),
                          tooltip: 'Perbarui GPS',
                        ),
                      ],
                    ),
                    if (_position != null)
                      Text(
                        'Koordinat: ${_position!.latitude.toStringAsFixed(6)}, ${_position!.longitude.toStringAsFixed(6)} (±${_position!.accuracy.round()}m)',
                        style: const TextStyle(fontSize: 12, color: AppTheme.textSecondary),
                      ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 12),
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      _todaySchedule['shift_name']?.toString() ?? 'Jadwal Kerja Hari Ini',
                      style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 16),
                    ),
                    const SizedBox(height: 10),
                    Row(
                      children: [
                        Expanded(
                          child: _TimeStat(
                            label: 'Absen Masuk',
                            value: _todayAttendance?.checkIn ?? '--:--',
                            sub: 'Jadwal: ${_todaySchedule['scheduled_in'] ?? '-'}',
                          ),
                        ),
                        Expanded(
                          child: _TimeStat(
                            label: 'Absen Pulang',
                            value: _todayAttendance?.checkOut ?? '--:--',
                            sub: 'Jadwal: ${_todaySchedule['scheduled_out'] ?? '-'}',
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 20),
            if (isDayOff)
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: const Color(0xFFF1F5F9),
                  borderRadius: BorderRadius.circular(14),
                ),
                child: const Text(
                  'Hari ini adalah jadwal libur (OFF) Anda.',
                  textAlign: TextAlign.center,
                  style: TextStyle(fontWeight: FontWeight.w600, color: AppTheme.textSecondary),
                ),
              )
            else if (!hasIn)
              FilledButton.icon(
                onPressed: _submitting ? null : () => _handleAttendance(isCheckIn: true),
                icon: const Icon(Icons.login_rounded),
                label: Text(_submitting ? 'Memproses...' : 'Absen Masuk (Biometrik)'),
              )
            else if (!hasOut)
              FilledButton.icon(
                style: FilledButton.styleFrom(backgroundColor: AppTheme.primaryDark),
                onPressed: _submitting ? null : () => _handleAttendance(isCheckIn: false),
                icon: const Icon(Icons.logout_rounded),
                label: Text(_submitting ? 'Memproses...' : 'Absen Pulang (Biometrik)'),
              )
            else
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: AppTheme.success.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: AppTheme.success.withValues(alpha: 0.4)),
                ),
                child: const Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(Icons.check_circle, color: AppTheme.success),
                    SizedBox(width: 8),
                    Text(
                      'Absensi masuk dan pulang hari ini sudah selesai.',
                      style: TextStyle(fontWeight: FontWeight.w700, color: AppTheme.success),
                    ),
                  ],
                ),
              ),
          ],
        ],
      ),
    );
  }
}

class _TimeStat extends StatelessWidget {
  final String label;
  final String value;
  final String sub;

  const _TimeStat({required this.label, required this.value, required this.sub});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label, style: const TextStyle(fontSize: 12, color: AppTheme.textSecondary)),
        const SizedBox(height: 4),
        Text(value, style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w800)),
        Text(sub, style: const TextStyle(fontSize: 11.5, color: AppTheme.textSecondary)),
      ],
    );
  }
}
