import 'package:material_ui/material_ui.dart';
import 'package:provider/provider.dart';
import '../../../core/network/api_client.dart';
import '../../../core/theme/app_theme.dart';
import '../../common/widgets/ui_kit.dart';

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
                SizedBox(height: 48),
                UmbulanEmptyState(
                  icon: Icons.task_alt_rounded,
                  title: 'Semua Pengajuan Telah Ditinjau',
                  subtitle: 'Tidak ada permohonan Cuti, CAR, atau MPR yang menunggu persetujuan Anda saat ini.',
                ),
              ],
            )
          : ListView.builder(
              padding: const EdgeInsets.fromLTRB(16, 16, 16, 24),
              itemCount: all.length + 1,
              itemBuilder: (_, idx) {
                if (idx == 0) {
                  return Padding(
                    padding: const EdgeInsets.only(bottom: 12),
                    child: UmbulanSectionHeader(
                      title: 'Antrean Persetujuan (${all.length})',
                      subtitle: 'Tinjau pengajuan staf di bawah wewenang Anda',
                    ),
                  );
                }
                final item = all[idx - 1];
                final d = item.data;
                final id = (d['id'] as num).toInt();
                final pemohon = (d['user'] is Map ? d['user']['name'] : 'Karyawan').toString();
                final initial = pemohon.trim().isNotEmpty ? pemohon.trim()[0].toUpperCase() : 'K';
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
                            CircleAvatar(
                              radius: 18,
                              backgroundColor: AppTheme.primary.withValues(alpha: 0.12),
                              child: Text(
                                initial,
                                style: const TextStyle(
                                  fontWeight: FontWeight.w800,
                                  color: AppTheme.primary,
                                ),
                              ),
                            ),
                            const SizedBox(width: 10),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    pemohon,
                                    style: const TextStyle(
                                      fontWeight: FontWeight.w800,
                                      fontSize: 15,
                                      color: AppTheme.textPrimary,
                                    ),
                                  ),
                                  const SizedBox(height: 2),
                                  UmbulanModuleBadge(module: item.module),
                                ],
                              ),
                            ),
                            const UmbulanStatusBadge(status: 'pending'),
                          ],
                        ),
                        const SizedBox(height: 12),
                        Container(
                          width: double.infinity,
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: AppTheme.surfaceMuted,
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: AppTheme.border),
                          ),
                          child: Text(
                            summary,
                            style: const TextStyle(
                              color: AppTheme.textPrimary,
                              height: 1.45,
                              fontSize: 13.5,
                            ),
                          ),
                        ),
                        const SizedBox(height: 14),
                        Row(
                          children: [
                            Expanded(
                              child: OutlinedButton.icon(
                                style: OutlinedButton.styleFrom(
                                  foregroundColor: AppTheme.danger,
                                  side: BorderSide(color: AppTheme.danger.withValues(alpha: 0.45)),
                                ),
                                onPressed: () => _process(item.module, id, 'reject'),
                                icon: const Icon(Icons.close_rounded, size: 18),
                                label: const Text('Tolak'),
                              ),
                            ),
                            const SizedBox(width: 10),
                            Expanded(
                              child: FilledButton.icon(
                                style: FilledButton.styleFrom(backgroundColor: AppTheme.success),
                                onPressed: () => _process(item.module, id, 'approve'),
                                icon: const Icon(Icons.check_rounded, size: 18),
                                label: const Text('Setujui'),
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
