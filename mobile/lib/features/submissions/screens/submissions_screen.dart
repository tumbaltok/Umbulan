import 'package:material_ui/material_ui.dart';
import 'package:provider/provider.dart';
import '../../../core/network/api_client.dart';
import '../../../core/providers/auth_provider.dart';
import '../../../core/theme/app_theme.dart';
import '../../common/widgets/ui_kit.dart';
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
            'Lengkapi 3 syarat kelengkapan akun di Beranda (Email, TTD Digital, Jadwal Kerja) untuk membuat dan melihat pengajuan.';
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
          content: Text('Harap lengkapi 3 syarat kelengkapan akun di tab Beranda terlebih dahulu.'),
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
                            module: 'cuti',
                            items: _cutis,
                            emptyTitle: 'Belum Ada Pengajuan Cuti',
                            emptySubtitle: 'Permohonan cuti tahunan atau izin kerja Anda akan muncul di sini.',
                            titleBuilder: (e) => (e['jenis_cuti']?['name_cuti'] ?? 'Cuti').toString(),
                            subtitleBuilder: (e) =>
                                '${_shortDate(e['tanggal_mulai'])} s/d ${_shortDate(e['tanggal_selesai'])} (${e['total_hari'] ?? '-'} hari)\n${e['alasan_cuti'] ?? ''}',
                            onRefresh: _loadAll,
                            onCreateTap: _openCreateForm,
                          ),
                          _SubmissionList(
                            module: 'car',
                            items: _cars,
                            emptyTitle: 'Belum Ada Pengajuan CAR',
                            emptySubtitle: 'Ajukan kasbon atau dana muka operasional melalui tombol di bawah.',
                            titleBuilder: (e) => (e['nomor_car'] ?? 'CAR').toString(),
                            subtitleBuilder: (e) => '${e['alasan_pembelian'] ?? ''}',
                            onRefresh: _loadAll,
                            onCreateTap: _openCreateForm,
                          ),
                          _SubmissionList(
                            module: 'mpr',
                            items: _mprs,
                            emptyTitle: 'Belum Ada Pengajuan MPR',
                            emptySubtitle: 'Permintaan pengadaan barang atau sparepart teknik akan tercatat di sini.',
                            titleBuilder: (e) => '${e['nomor_mpr'] ?? 'MPR'} • ${e['priority'] ?? 'Normal'}',
                            subtitleBuilder: (e) => '${e['keperluan_urgensi'] ?? ''}',
                            onRefresh: _loadAll,
                            onCreateTap: _openCreateForm,
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
        icon: const Icon(Icons.add_rounded),
        label: const Text('Ajukan', style: TextStyle(fontWeight: FontWeight.w700)),
      ),
    );
  }
}

String _shortDate(dynamic value) {
  final raw = value?.toString() ?? '';
  return raw.length >= 10 ? raw.substring(0, 10) : (raw.isEmpty ? '-' : raw);
}

class _SubmissionList extends StatefulWidget {
  final String module;
  final List<Map<String, dynamic>> items;
  final String emptyTitle;
  final String emptySubtitle;
  final String Function(Map<String, dynamic>) titleBuilder;
  final String Function(Map<String, dynamic>) subtitleBuilder;
  final Future<void> Function() onRefresh;
  final VoidCallback onCreateTap;

  const _SubmissionList({
    required this.module,
    required this.items,
    required this.emptyTitle,
    required this.emptySubtitle,
    required this.titleBuilder,
    required this.subtitleBuilder,
    required this.onRefresh,
    required this.onCreateTap,
  });

  @override
  State<_SubmissionList> createState() => _SubmissionListState();
}

class _SubmissionListState extends State<_SubmissionList> {
  String _filter = 'all';

  @override
  Widget build(BuildContext context) {
    final filtered = widget.items.where((e) {
      if (_filter == 'all') return true;
      final st = (e['status_akhir'] ?? 'pending').toString().toLowerCase();
      return st == _filter;
    }).toList();

    return RefreshIndicator(
      onRefresh: widget.onRefresh,
      child: widget.items.isEmpty
          ? ListView(
              children: [
                const SizedBox(height: 40),
                UmbulanEmptyState(
                  icon: widget.module == 'car'
                      ? Icons.receipt_long_rounded
                      : (widget.module == 'mpr' ? Icons.inventory_2_rounded : Icons.event_available_rounded),
                  title: widget.emptyTitle,
                  subtitle: widget.emptySubtitle,
                  actionLabel: 'Ajukan Sekarang',
                  onAction: widget.onCreateTap,
                ),
              ],
            )
          : ListView(
              padding: const EdgeInsets.fromLTRB(16, 12, 16, 88),
              children: [
                SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  child: Row(
                    children: [
                      _FilterChipButton(
                        label: 'Semua (${widget.items.length})',
                        selected: _filter == 'all',
                        onTap: () => setState(() => _filter = 'all'),
                      ),
                      const SizedBox(width: 8),
                      _FilterChipButton(
                        label: 'Menunggu',
                        selected: _filter == 'pending',
                        onTap: () => setState(() => _filter = 'pending'),
                      ),
                      const SizedBox(width: 8),
                      _FilterChipButton(
                        label: 'Disetujui',
                        selected: _filter == 'approved',
                        onTap: () => setState(() => _filter = 'approved'),
                      ),
                      const SizedBox(width: 8),
                      _FilterChipButton(
                        label: 'Ditolak',
                        selected: _filter == 'rejected',
                        onTap: () => setState(() => _filter = 'rejected'),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 12),
                if (filtered.isEmpty)
                  const Card(
                    child: UmbulanEmptyState(
                      icon: Icons.filter_list_off_rounded,
                      title: 'Tidak Ada Data Sesuai Filter',
                      subtitle: 'Coba pilih status lain untuk melihat daftar pengajuan Anda.',
                    ),
                  )
                else
                  ...filtered.map((e) {
                    final status = (e['status_akhir'] ?? 'pending').toString().toLowerCase();
                    final rejectNote = e['catatan_penolakan']?.toString() ?? '';

                    return Card(
                      margin: const EdgeInsets.only(bottom: 10),
                      child: InkWell(
                        borderRadius: BorderRadius.circular(18),
                        onTap: () => _showSubmissionDetail(context, widget.module, e),
                        child: Padding(
                          padding: const EdgeInsets.all(14),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  UmbulanModuleBadge(module: widget.module),
                                  const Spacer(),
                                  UmbulanStatusBadge(status: status),
                                ],
                              ),
                              const SizedBox(height: 10),
                              Text(
                                widget.titleBuilder(e),
                                style: const TextStyle(
                                  fontWeight: FontWeight.w800,
                                  fontSize: 15,
                                  color: AppTheme.textPrimary,
                                ),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                widget.subtitleBuilder(e),
                                maxLines: 3,
                                overflow: TextOverflow.ellipsis,
                                style: const TextStyle(
                                  fontSize: 12.5,
                                  height: 1.4,
                                  color: AppTheme.textSecondary,
                                ),
                              ),
                              if (status == 'rejected' && rejectNote.isNotEmpty) ...[
                                const SizedBox(height: 10),
                                Container(
                                  width: double.infinity,
                                  padding: const EdgeInsets.all(10),
                                  decoration: BoxDecoration(
                                    color: const Color(0xFFFEF2F2),
                                    borderRadius: BorderRadius.circular(10),
                                    border: Border.all(color: const Color(0xFFFECACA)),
                                  ),
                                  child: Text(
                                    'Alasan penolakan: $rejectNote',
                                    style: const TextStyle(
                                      fontSize: 11.5,
                                      fontWeight: FontWeight.w600,
                                      color: AppTheme.danger,
                                    ),
                                  ),
                                ),
                              ],
                            ],
                          ),
                        ),
                      ),
                    );
                  }),
              ],
            ),
    );
  }

  void _showSubmissionDetail(BuildContext context, String module, Map<String, dynamic> data) {
    final status = (data['status_akhir'] ?? 'pending').toString();
    showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      builder: (ctx) => SafeArea(
        child: Padding(
          padding: const EdgeInsets.fromLTRB(20, 14, 20, 22),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(
                child: Container(
                  width: 40,
                  height: 4,
                  decoration: BoxDecoration(
                    color: AppTheme.border,
                    borderRadius: BorderRadius.circular(99),
                  ),
                ),
              ),
              const SizedBox(height: 16),
              Row(
                children: [
                  UmbulanModuleBadge(module: module),
                  const Spacer(),
                  UmbulanStatusBadge(status: status),
                ],
              ),
              const SizedBox(height: 12),
              Text(
                widget.titleBuilder(data),
                style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w800, color: AppTheme.textPrimary),
              ),
              const SizedBox(height: 8),
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: AppTheme.surfaceMuted,
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: AppTheme.border),
                ),
                child: Text(
                  widget.subtitleBuilder(data),
                  style: const TextStyle(fontSize: 13.5, height: 1.45, color: AppTheme.textPrimary),
                ),
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(
                    child: _StageBox(
                      title: 'Tahap 1',
                      value: (data['status_tahap_1'] ?? 'pending').toString(),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: _StageBox(
                      title: 'Tahap 2',
                      value: (data['status_tahap_2'] ?? 'pending').toString(),
                    ),
                  ),
                ],
              ),
              if ((data['catatan_penolakan'] ?? '').toString().isNotEmpty) ...[
                const SizedBox(height: 12),
                Text(
                  'Catatan Penolakan: ${data['catatan_penolakan']}',
                  style: const TextStyle(fontSize: 12.5, color: AppTheme.danger, fontWeight: FontWeight.w600),
                ),
              ],
              const SizedBox(height: 16),
              SizedBox(
                width: double.infinity,
                child: FilledButton(
                  onPressed: () => Navigator.pop(ctx),
                  child: const Text('Tutup'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _FilterChipButton extends StatelessWidget {
  final String label;
  final bool selected;
  final VoidCallback onTap;

  const _FilterChipButton({
    required this.label,
    required this.selected,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(999),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 13, vertical: 7),
        decoration: BoxDecoration(
          color: selected ? AppTheme.primary : Colors.white,
          borderRadius: BorderRadius.circular(999),
          border: Border.all(color: selected ? AppTheme.primary : AppTheme.border),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 12,
            fontWeight: FontWeight.w700,
            color: selected ? Colors.white : AppTheme.textSecondary,
          ),
        ),
      ),
    );
  }
}

class _StageBox extends StatelessWidget {
  final String title;
  final String value;

  const _StageBox({required this.title, required this.value});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(10),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppTheme.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(title, style: const TextStyle(fontSize: 11, color: AppTheme.textSecondary, fontWeight: FontWeight.w600)),
          const SizedBox(height: 4),
          UmbulanStatusBadge(status: value),
        ],
      ),
    );
  }
}
