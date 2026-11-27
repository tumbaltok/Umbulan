import 'package:material_ui/material_ui.dart';
import 'package:provider/provider.dart';
import '../../../core/models/app_models.dart';
import '../../../core/network/api_client.dart';
import '../../../core/providers/auth_provider.dart';
import '../../../core/theme/app_theme.dart';

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameCtrl = TextEditingController();
  final _nipCtrl = TextEditingController();
  final _emailCtrl = TextEditingController();
  final _passCtrl = TextEditingController();
  final _confirmCtrl = TextEditingController();

  List<StationModel> _stations = [];
  int? _selectedStationId;
  int _selectedGenderId = 1;
  String _scheduleType = 'normal';
  String _shiftChoice = 'pagi';
  bool _loadingMaster = true;
  String? _masterError;

  @override
  void initState() {
    super.initState();
    _loadMasterData();
  }

  @override
  void dispose() {
    _nameCtrl.dispose();
    _nipCtrl.dispose();
    _emailCtrl.dispose();
    _passCtrl.dispose();
    _confirmCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadMasterData() async {
    setState(() {
      _loadingMaster = true;
      _masterError = null;
    });
    try {
      final res = await context.read<ApiClient>().get('/master-data');
      final data = res['data'] as Map<String, dynamic>;
      final list = (data['stations'] as List? ?? [])
          .whereType<Map<String, dynamic>>()
          .map(StationModel.fromJson)
          .toList();
      setState(() {
        _stations = list;
        if (list.isNotEmpty) _selectedStationId = list.first.id;
        _loadingMaster = false;
      });
    } catch (e) {
      setState(() {
        _masterError = e.toString();
        _loadingMaster = false;
      });
    }
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate() || _selectedStationId == null) return;

    final auth = context.read<AuthProvider>();
    final ok = await auth.register(
      name: _nameCtrl.text,
      nip: _nipCtrl.text,
      email: _emailCtrl.text,
      password: _passCtrl.text,
      passwordConfirmation: _confirmCtrl.text,
      stationId: _selectedStationId!,
      genderId: _selectedGenderId,
      scheduleType: _scheduleType,
      currentShiftChoice: _scheduleType == 'roster' ? _shiftChoice : null,
    );

    if (!mounted) return;
    if (ok) {
      Navigator.pop(context);
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Registrasi berhasil! Silakan verifikasi email melalui kode OTP.'),
        ),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(auth.error ?? 'Registrasi gagal'),
          backgroundColor: AppTheme.danger,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();

    return Scaffold(
      appBar: AppBar(title: const Text('Registrasi Karyawan')),
      body: _loadingMaster
          ? const Center(child: CircularProgressIndicator())
          : _masterError != null
              ? Center(
                  child: Padding(
                    padding: const EdgeInsets.all(24),
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text(_masterError!, textAlign: TextAlign.center),
                        const SizedBox(height: 12),
                        OutlinedButton(onPressed: _loadMasterData, child: const Text('Coba Lagi')),
                      ],
                    ),
                  ),
                )
              : SingleChildScrollView(
                  padding: const EdgeInsets.fromLTRB(16, 16, 16, 28),
                  child: Form(
                    key: _formKey,
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        Container(
                          padding: const EdgeInsets.all(14),
                          decoration: BoxDecoration(
                            gradient: const LinearGradient(
                              colors: [AppTheme.primaryDark, AppTheme.primary],
                              begin: Alignment.topLeft,
                              end: Alignment.bottomRight,
                            ),
                            borderRadius: BorderRadius.circular(18),
                          ),
                          child: const Row(
                            children: [
                              Icon(Icons.badge_outlined, color: Color(0xFFBAE6FD), size: 28),
                              SizedBox(width: 12),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      'Aktivasi Akun Kepegawaian',
                                      style: TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 15),
                                    ),
                                    SizedBox(height: 2),
                                    Text(
                                      'Lengkapi data diri, penempatan stasiun, dan pola jadwal kerja Anda.',
                                      style: TextStyle(color: Color(0xFFE0F2FE), fontSize: 12),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 16),
                        Card(
                          child: Padding(
                            padding: const EdgeInsets.all(16),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.stretch,
                              children: [
                                const Text(
                                  '1. Data Diri Karyawan',
                                  style: TextStyle(fontWeight: FontWeight.w800, fontSize: 14, color: AppTheme.primary),
                                ),
                                const SizedBox(height: 12),
                                TextFormField(
                                  controller: _nameCtrl,
                                  decoration: const InputDecoration(
                                    labelText: 'Nama Lengkap *',
                                    prefixIcon: Icon(Icons.person_outline),
                                  ),
                                  validator: (v) => (v == null || v.trim().isEmpty) ? 'Nama wajib diisi.' : null,
                                ),
                                const SizedBox(height: 12),
                                TextFormField(
                                  controller: _nipCtrl,
                                  decoration: const InputDecoration(
                                    labelText: 'NIP (Opsional)',
                                    prefixIcon: Icon(Icons.badge_outlined),
                                  ),
                                ),
                                const SizedBox(height: 12),
                                TextFormField(
                                  controller: _emailCtrl,
                                  keyboardType: TextInputType.emailAddress,
                                  decoration: const InputDecoration(
                                    labelText: 'Alamat Email *',
                                    prefixIcon: Icon(Icons.email_outlined),
                                  ),
                                  validator: (v) {
                                    if (v == null || v.trim().isEmpty) return 'Email wajib diisi.';
                                    if (!v.contains('@')) return 'Format email tidak valid.';
                                    return null;
                                  },
                                ),
                                const SizedBox(height: 12),
                                DropdownButtonFormField<int>(
                                  initialValue: _selectedGenderId,
                                  decoration: const InputDecoration(
                                    labelText: 'Jenis Kelamin *',
                                    prefixIcon: Icon(Icons.wc_outlined),
                                  ),
                                  items: const [
                                    DropdownMenuItem(value: 1, child: Text('Laki-laki')),
                                    DropdownMenuItem(value: 2, child: Text('Perempuan')),
                                  ],
                                  onChanged: (v) => setState(() => _selectedGenderId = v ?? 1),
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
                              crossAxisAlignment: CrossAxisAlignment.stretch,
                              children: [
                                const Text(
                                  '2. Penempatan & Jadwal Kerja',
                                  style: TextStyle(fontWeight: FontWeight.w800, fontSize: 14, color: AppTheme.primary),
                                ),
                                const SizedBox(height: 12),
                                DropdownButtonFormField<int>(
                                  initialValue: _selectedStationId,
                                  decoration: const InputDecoration(
                                    labelText: 'Stasiun Penempatan *',
                                    prefixIcon: Icon(Icons.location_on_outlined),
                                  ),
                                  items: _stations
                                      .map((s) => DropdownMenuItem(value: s.id, child: Text(s.name)))
                                      .toList(),
                                  onChanged: (v) => setState(() => _selectedStationId = v),
                                ),
                                const SizedBox(height: 12),
                                DropdownButtonFormField<String>(
                                  initialValue: _scheduleType,
                                  isExpanded: true,
                                  decoration: const InputDecoration(
                                    labelText: 'Pilih Jenis Jadwal Kerja *',
                                    prefixIcon: Icon(Icons.schedule_outlined),
                                  ),
                                  items: const [
                                    DropdownMenuItem(
                                      value: 'normal',
                                      child: Text('Jadwal Normal (Senin - Jumat, 07:00 - 16:00)', overflow: TextOverflow.ellipsis),
                                    ),
                                    DropdownMenuItem(
                                      value: 'roster',
                                      child: Text('Jadwal Roster Shift (Pagi / Malam / Libur)', overflow: TextOverflow.ellipsis),
                                    ),
                                  ],
                                  onChanged: (v) => setState(() => _scheduleType = v ?? 'normal'),
                                ),
                                if (_scheduleType == 'roster') ...[
                                  const SizedBox(height: 12),
                                  DropdownButtonFormField<String>(
                                    initialValue: _shiftChoice,
                                    isExpanded: true,
                                    decoration: const InputDecoration(
                                      labelText: 'Shift Berjalan Minggu Ini',
                                      prefixIcon: Icon(Icons.timelapse),
                                    ),
                                    items: const [
                                      DropdownMenuItem(value: 'pagi', child: Text('Shift Pagi (07:00 - 19:00)')),
                                      DropdownMenuItem(value: 'malam', child: Text('Shift Malam (19:00 - 07:00)')),
                                      DropdownMenuItem(value: 'libur', child: Text('Minggu Libur (OFF)')),
                                    ],
                                    onChanged: (v) => setState(() => _shiftChoice = v ?? 'pagi'),
                                  ),
                                ],
                              ],
                            ),
                          ),
                        ),
                        const SizedBox(height: 12),
                        Card(
                          child: Padding(
                            padding: const EdgeInsets.all(16),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.stretch,
                              children: [
                                const Text(
                                  '3. Keamanan Kata Sandi',
                                  style: TextStyle(fontWeight: FontWeight.w800, fontSize: 14, color: AppTheme.primary),
                                ),
                                const SizedBox(height: 12),
                                TextFormField(
                                  controller: _passCtrl,
                                  obscureText: true,
                                  decoration: const InputDecoration(
                                    labelText: 'Kata Sandi (Min. 8 karakter) *',
                                    prefixIcon: Icon(Icons.lock_outline),
                                  ),
                                  validator: (v) => (v == null || v.length < 8) ? 'Minimal 8 karakter.' : null,
                                ),
                                const SizedBox(height: 12),
                                TextFormField(
                                  controller: _confirmCtrl,
                                  obscureText: true,
                                  decoration: const InputDecoration(
                                    labelText: 'Konfirmasi Kata Sandi *',
                                    prefixIcon: Icon(Icons.lock_reset_outlined),
                                  ),
                                  validator: (v) => v != _passCtrl.text ? 'Konfirmasi sandi tidak cocok.' : null,
                                ),
                              ],
                            ),
                          ),
                        ),
                        const SizedBox(height: 20),
                        FilledButton(
                          onPressed: auth.isBusy ? null : _submit,
                          child: auth.isBusy
                              ? const SizedBox(
                                  width: 22,
                                  height: 22,
                                  child: CircularProgressIndicator(strokeWidth: 2.2, color: Colors.white),
                                )
                              : const Text('Daftar Akun'),
                        ),
                      ],
                    ),
                  ),
                ),
    );
  }
}
