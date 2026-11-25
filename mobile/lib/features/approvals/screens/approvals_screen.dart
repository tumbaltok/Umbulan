import 'package:material_ui/material_ui.dart';
import 'package:provider/provider.dart';
import '../../../core/network/api_client.dart';
import '../../../core/theme/app_theme.dart';

class ApprovalsScreen extends StatefulWidget {
  final VoidCallback onProcessed;
  const ApprovalsScreen({super.key, required this.onProcessed});

  @override
  State<ApprovalsScreen> createState() => _ApprovalsScreenState();
}

class _ApprovalsScreenState extends State<ApprovalsScreen> {
  bool _loading = true;
  String? _error;
  List<Map<String, dynamic>> _cuti = [];
  List<Map<String, dynamic>> _car = [];
  List<Map<String, dynamic>> _mpr = [];

  @override
  void initState() {
    super.initState();
    _loadPending();
  }

  Future<void> _loadPending() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final res = await context.read<ApiClient>().get('/approvals/pending');
      final data = res['data'] as Map<String, dynamic>;
      setState(() {
        _cuti = ((data['cuti'] as List?) ?? []).whereType<Map<String, dynamic>>().toList();
        _car = ((data['car'] as List?) ?? []).whereType<Map<String, dynamic>>().toList();
        _mpr = ((data['mpr'] as List?) ?? []).whereType<Map<String, dynamic>>().toList();
      });
    } catch (e) {
      _error = e.toString();
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _process(String module, int id, String action) async {
    final api = context.read<ApiClient>();
    final messenger = ScaffoldMessenger.of(context);
    String? catatan;
    if (action == 'reject') {
      final ctrl = TextEditingController();
      catatan = await showDialog<String>(
        context: context,
        builder: (ctx) => AlertDialog(
          title: const Text('Alasan Penolakan'),
          content: TextField(
            controller: ctrl,
            maxLines: 3,
            decoration: const InputDecoration(hintText: 'Masukkan catatan penolakan wajib...'),
          ),
          actions: [
            TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Batal')),
            FilledButton(
              style: FilledButton.styleFrom(backgroundColor: AppTheme.danger),
              onPressed: () {
                if (ctrl.text.trim().isEmpty) return;
                Navigator.pop(ctx, ctrl.text.trim());
              },
              child: const Text('Tolak Pengajuan'),
            ),
          ],
        ),
      );
      if (catatan == null || catatan.isEmpty || !mounted) return;
    }

    try {
      final res = await api.post(
        '/approvals/$module/$id/process',
        data: {
          'action': action,
          'catatan': ?catatan,
        },
      );
      if (mounted) {
        messenger.showSnackBar(
          SnackBar(content: Text(res['message']?.toString() ?? 'Diproses')),
        );
        widget.onProcessed();
        await _loadPending();
      }
    } catch (e) {
      if (mounted) {
        messenger.showSnackBar(
          SnackBar(content: Text(e.toString()), backgroundColor: AppTheme.danger),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final all = <_ApprovalItem>[
      ..._cuti.map((e) => _ApprovalItem(module: 'cuti', data: e)),
      ..._car.map((e) => _ApprovalItem(module: 'car', data: e)),
      ..._mpr.map((e) => _ApprovalItem(module: 'mpr', data: e)),
    ];

    if (_loading) return const Center(child: CircularProgressIndicator());
    if (_error != null) {
      return Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(_error!),
            const SizedBox(height: 10),
            OutlinedButton(onPressed: _loadPending, child: const Text('Muat Ulang')),
          ],
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _loadPending,
      child: all.isEmpty
          ? ListView(
              children: const [
                SizedBox(height: 120),
                Icon(Icons.task_alt, size: 52, color: AppTheme.textSecondary),
                SizedBox(height: 12),
                Text(
                  'Tidak ada pengajuan menunggu persetujuan Anda.',
                  textAlign: TextAlign.center,
                  style: TextStyle(color: AppTheme.textSecondary),
                ),
              ],
            )
          : ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: all.length,
              itemBuilder: (_, idx) {
                final item = all[idx];
                final d = item.data;
                final id = (d['id'] as num).toInt();
                final pemohon = (d['user'] is Map ? d['user']['name'] : 'Karyawan').toString();
                final summary = switch (item.module) {
                  'cuti' =>
                    '${d['jenis_cuti']?['name_cuti'] ?? 'Cuti'} (${d['total_hari']} hari)\n${d['alasan_cuti'] ?? ''}',
                  'car' =>
                    'CAR ${d['nomor_car'] ?? ''}\n${d['alasan_pembelian'] ?? ''}',
                  _ =>
                    'MPR ${d['nomor_mpr'] ?? ''} (${d['priority'] ?? 'Normal'})\n${d['keperluan_urgensi'] ?? ''}',
                };

                return Card(
                  margin: const EdgeInsets.only(bottom: 12),
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                              decoration: BoxDecoration(
                                color: AppTheme.primary.withValues(alpha: 0.1),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Text(
                                item.module.toUpperCase(),
                                style: const TextStyle(
                                  fontWeight: FontWeight.w700,
                                  fontSize: 11,
                                  color: AppTheme.primary,
                                ),
                              ),
                            ),
                            const SizedBox(width: 8),
                            Expanded(
                              child: Text(
                                pemohon,
                                style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 15),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 8),
                        Text(summary, style: const TextStyle(color: AppTheme.textSecondary, fontSize: 13.5)),
                        const SizedBox(height: 12),
                        Row(
                          children: [
                            Expanded(
                              child: OutlinedButton(
                                style: OutlinedButton.styleFrom(foregroundColor: AppTheme.danger),
                                onPressed: () => _process(item.module, id, 'reject'),
                                child: const Text('Tolak'),
                              ),
                            ),
                            const SizedBox(width: 10),
                            Expanded(
                              child: FilledButton(
                                style: FilledButton.styleFrom(backgroundColor: AppTheme.success),
                                onPressed: () => _process(item.module, id, 'approve'),
                                child: const Text('Setujui'),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                );
              },
            ),
    );
  }
}

class _ApprovalItem {
  final String module;
  final Map<String, dynamic> data;
  _ApprovalItem({required this.module, required this.data});
}
