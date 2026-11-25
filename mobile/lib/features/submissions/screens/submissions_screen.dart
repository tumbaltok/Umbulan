import 'package:material_ui/material_ui.dart';
import 'package:provider/provider.dart';
import '../../../core/network/api_client.dart';
import '../../../core/providers/auth_provider.dart';
import '../../../core/theme/app_theme.dart';
import 'submission_forms.dart';

class SubmissionsScreen extends StatefulWidget {
  final VoidCallback onAccountUpdated;
  const SubmissionsScreen({super.key, required this.onAccountUpdated});

  @override
  State<SubmissionsScreen> createState() => _SubmissionsScreenState();
}

class _SubmissionsScreenState extends State<SubmissionsScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabCtrl;
  bool _loading = true;
  String? _blockedMessage;
  List<Map<String, dynamic>> _cutis = [];
  List<Map<String, dynamic>> _cars = [];
  List<Map<String, dynamic>> _mprs = [];

  @override
  void initState() {
    super.initState();
    _tabCtrl = TabController(length: 3, vsync: this);
    _loadAll();
  }

  @override
  void dispose() {
    _tabCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadAll() async {
    final user = context.read<AuthProvider>().user;
    if (user == null || !user.isAccountComplete) {
      setState(() {
        _loading = false;
        _blockedMessage =
            'Lengkapi 4 syarat kelengkapan akun di Beranda (Email, Biometrik Wajah, TTD Digital, Jadwal Kerja) untuk membuat dan melihat pengajuan.';
      });
      return;
    }

    setState(() {
      _loading = true;
      _blockedMessage = null;
    });

    try {
      final api = context.read<ApiClient>();
      final results = await Future.wait([
        api.get('/cuti'),
        api.get('/car'),
        api.get('/mpr'),
      ]);

      setState(() {
        _cutis = _extractList(results[0]);
        _cars = _extractList(results[1]);
        _mprs = _extractList(results[2]);
      });
    } catch (e) {
      setState(() => _blockedMessage = e.toString());
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  List<Map<String, dynamic>> _extractList(Map<String, dynamic> res) {
    final data = res['data'];
    if (data is Map && data['data'] is List) {
      return (data['data'] as List).whereType<Map<String, dynamic>>().toList();
    }
    return [];
  }

  Future<void> _openCreateForm() async {
    final user = context.read<AuthProvider>().user;
    if (user == null || !user.isAccountComplete) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Harap lengkapi 4 syarat kelengkapan akun di tab Beranda terlebih dahulu.'),
          backgroundColor: AppTheme.warning,
        ),
      );
      return;
    }

    Widget target;
    if (_tabCtrl.index == 0) {
      target = const CreateCutiScreen();
    } else if (_tabCtrl.index == 1) {
      target = const CreateCarScreen();
    } else {
      target = const CreateMprScreen();
    }

    final created = await Navigator.push<bool>(
      context,
      MaterialPageRoute(builder: (_) => target),
    );
    if (created == true) {
      widget.onAccountUpdated();
      await _loadAll();
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Column(
        children: [
          Material(
            color: Colors.white,
            child: TabBar(
              controller: _tabCtrl,
              labelColor: AppTheme.primary,
              indicatorColor: AppTheme.primary,
              tabs: const [
                Tab(text: 'Cuti / Izin'),
                Tab(text: 'CAR'),
                Tab(text: 'MPR'),
              ],
            ),
          ),
          Expanded(
            child: _loading
                ? const Center(child: CircularProgressIndicator())
                : _blockedMessage != null
                    ? Padding(
                        padding: const EdgeInsets.all(24),
                        child: Center(
                          child: Column(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(Icons.lock_outline, size: 48, color: AppTheme.warning),
                              const SizedBox(height: 12),
                              Text(_blockedMessage!, textAlign: TextAlign.center),
                              const SizedBox(height: 14),
                              OutlinedButton(onPressed: _loadAll, child: const Text('Muat Ulang')),
                            ],
                          ),
                        ),
                      )
                    : TabBarView(
                        controller: _tabCtrl,
                        children: [
                          _SubmissionList(
                            items: _cutis,
                            emptyText: 'Belum ada pengajuan Cuti.',
                            titleBuilder: (e) => (e['jenis_cuti']?['name_cuti'] ?? 'Cuti').toString(),
                            subtitleBuilder: (e) =>
                                '${e['tanggal_mulai']?.toString().substring(0, 10) ?? ''} s/d ${e['tanggal_selesai']?.toString().substring(0, 10) ?? ''} (${e['total_hari']} hari)\n${e['alasan_cuti'] ?? ''}',
                            onRefresh: _loadAll,
                          ),
                          _SubmissionList(
                            items: _cars,
                            emptyText: 'Belum ada pengajuan CAR.',
                            titleBuilder: (e) => (e['nomor_car'] ?? 'CAR').toString(),
                            subtitleBuilder: (e) => '${e['alasan_pembelian'] ?? ''}',
                            onRefresh: _loadAll,
                          ),
                          _SubmissionList(
                            items: _mprs,
                            emptyText: 'Belum ada pengajuan MPR.',
                            titleBuilder: (e) => '${e['nomor_mpr'] ?? 'MPR'} (${e['priority'] ?? 'Normal'})',
                            subtitleBuilder: (e) => '${e['keperluan_urgensi'] ?? ''}',
                            onRefresh: _loadAll,
                          ),
                        ],
                      ),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _openCreateForm,
        backgroundColor: AppTheme.primary,
        foregroundColor: Colors.white,
        icon: const Icon(Icons.add),
        label: const Text('Ajukan'),
      ),
    );
  }
}

class _SubmissionList extends StatelessWidget {
  final List<Map<String, dynamic>> items;
  final String emptyText;
  final String Function(Map<String, dynamic>) titleBuilder;
  final String Function(Map<String, dynamic>) subtitleBuilder;
  final Future<void> Function() onRefresh;

  const _SubmissionList({
    required this.items,
    required this.emptyText,
    required this.titleBuilder,
    required this.subtitleBuilder,
    required this.onRefresh,
  });

  @override
  Widget build(BuildContext context) {
    return RefreshIndicator(
      onRefresh: onRefresh,
      child: items.isEmpty
          ? ListView(
              children: [
                const SizedBox(height: 100),
                Text(emptyText, textAlign: TextAlign.center, style: const TextStyle(color: AppTheme.textSecondary)),
              ],
            )
          : ListView.builder(
              padding: const EdgeInsets.fromLTRB(16, 16, 16, 84),
              itemCount: items.length,
              itemBuilder: (_, idx) {
                final e = items[idx];
                final status = (e['status_akhir'] ?? 'pending').toString().toLowerCase();
                final color = status == 'approved'
                    ? AppTheme.success
                    : (status == 'rejected' ? AppTheme.danger : AppTheme.warning);
                return Card(
                  margin: const EdgeInsets.only(bottom: 10),
                  child: ListTile(
                    title: Row(
                      children: [
                        Expanded(
                          child: Text(
                            titleBuilder(e),
                            style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 14.5),
                          ),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: color.withValues(alpha: 0.12),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Text(
                            status.toUpperCase(),
                            style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: color),
                          ),
                        ),
                      ],
                    ),
                    subtitle: Padding(
                      padding: const EdgeInsets.only(top: 6),
                      child: Text(subtitleBuilder(e), style: const TextStyle(fontSize: 12.5)),
                    ),
                  ),
                );
              },
            ),
    );
  }
}
