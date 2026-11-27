import 'package:dio/dio.dart';
import 'package:file_picker/file_picker.dart';
import 'package:material_ui/material_ui.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import '../../../core/models/app_models.dart';
import '../../../core/network/api_client.dart';
import '../../../core/theme/app_theme.dart';
import '../../common/widgets/ui_kit.dart';

class CreateCutiScreen extends StatefulWidget {
  const CreateCutiScreen({super.key});

  @override
  State<CreateCutiScreen> createState() => _CreateCutiScreenState();
}

class _CreateCutiScreenState extends State<CreateCutiScreen> {
  List<JenisCutiModel> _jenisList = [];
  JenisCutiModel? _selectedJenis;
  SubCutiModel? _selectedSub;
  DateTime _mulai = DateTime.now().add(const Duration(days: 1));
  DateTime _selesai = DateTime.now().add(const Duration(days: 1));
  final _alasanCtrl = TextEditingController();
  PlatformFile? _dokumen;
  bool _loadingJenis = true;
  bool _submitting = false;

  @override
  void initState() {
    super.initState();
    _loadJenis();
  }

  @override
  void dispose() {
    _alasanCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadJenis() async {
    try {
      final res = await context.read<ApiClient>().get('/cuti/jenis');
      final list = (res['data'] as List? ?? [])
          .whereType<Map<String, dynamic>>()
          .map(JenisCutiModel.fromJson)
          .toList();
      setState(() {
        _jenisList = list;
        if (list.isNotEmpty) {
          _selectedJenis = list.first;
          _selectedSub = list.first.subCutis.isNotEmpty ? list.first.subCutis.first : null;
        }
        _loadingJenis = false;
      });
    } catch (_) {
      if (mounted) setState(() => _loadingJenis = false);
    }
  }

  Future<void> _pickDate({required bool isStart}) async {
    final picked = await showDatePicker(
      context: context,
      initialDate: isStart ? _mulai : _selesai,
      firstDate: DateTime(2024),
      lastDate: DateTime(2030),
    );
    if (picked != null) {
      setState(() {
        if (isStart) {
          _mulai = picked;
          if (_selesai.isBefore(_mulai)) _selesai = picked;
        } else {
          _selesai = picked.isBefore(_mulai) ? _mulai : picked;
        }
      });
    }
  }

  Future<void> _submit() async {
    if (_selectedJenis == null || _alasanCtrl.text.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Pilih jenis cuti dan isi alasan cuti.')),
      );
      return;
    }

    setState(() => _submitting = true);
    final api = context.read<ApiClient>();
    final messenger = ScaffoldMessenger.of(context);
    final navigator = Navigator.of(context);
    try {
      final df = DateFormat('yyyy-MM-dd');
      final map = <String, dynamic>{
        'jenis_cuti_id': _selectedJenis!.id,
        if (_selectedSub != null) 'sub_cuti_id': _selectedSub!.id,
        'tanggal_mulai': df.format(_mulai),
        'tanggal_selesai': df.format(_selesai),
        'alasan_cuti': _alasanCtrl.text.trim(),
        if (_dokumen?.path != null)
          'dokumen_pendukung': await MultipartFile.fromFile(
            _dokumen!.path!,
            filename: _dokumen!.name,
          ),
      };

      await api.post('/cuti', data: FormData.fromMap(map));
      if (mounted) {
        messenger.showSnackBar(
          const SnackBar(content: Text('Pengajuan cuti berhasil dikirim!'), backgroundColor: AppTheme.success),
        );
        navigator.pop(true);
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

  @override
  Widget build(BuildContext context) {
    final df = DateFormat('dd MMM yyyy');
    final durationDays = _selesai.difference(_mulai).inDays + 1;
    return Scaffold(
      appBar: AppBar(title: const Text('Ajukan Cuti / Izin')),
      body: _loadingJenis
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: const EdgeInsets.fromLTRB(16, 16, 16, 28),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Card(
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          const UmbulanSectionHeader(
                            title: 'Rincian Cuti / Izin',
                            subtitle: 'Pilih kategori cuti dan tentukan periode pelaksanaan',
                          ),
                          const SizedBox(height: 14),
                          DropdownButtonFormField<JenisCutiModel>(
                            initialValue: _selectedJenis,
                            isExpanded: true,
                            decoration: const InputDecoration(labelText: 'Jenis Cuti *'),
                            items: _jenisList
                                .map((j) => DropdownMenuItem(value: j, child: Text(j.nameCuti)))
                                .toList(),
                            onChanged: (v) {
                              setState(() {
                                _selectedJenis = v;
                                _selectedSub = (v != null && v.subCutis.isNotEmpty) ? v.subCutis.first : null;
                              });
                            },
                          ),
                          if (_selectedJenis != null && _selectedJenis!.subCutis.isNotEmpty) ...[
                            const SizedBox(height: 12),
                            DropdownButtonFormField<SubCutiModel>(
                              initialValue: _selectedSub,
                              isExpanded: true,
                              decoration: const InputDecoration(labelText: 'Kategori / Sub Cuti'),
                              items: _selectedJenis!.subCutis
                                  .map((s) => DropdownMenuItem(value: s, child: Text(s.nameCuti)))
                                  .toList(),
                              onChanged: (v) => setState(() => _selectedSub = v),
                            ),
                          ],
                          const SizedBox(height: 14),
                          Row(
                            children: [
                              Expanded(
                                child: OutlinedButton.icon(
                                  onPressed: () => _pickDate(isStart: true),
                                  icon: const Icon(Icons.date_range_rounded, size: 18),
                                  label: Text('Mulai: ${df.format(_mulai)}'),
                                ),
                              ),
                              const SizedBox(width: 10),
                              Expanded(
                                child: OutlinedButton.icon(
                                  onPressed: () => _pickDate(isStart: false),
                                  icon: const Icon(Icons.event_rounded, size: 18),
                                  label: Text('Selesai: ${df.format(_selesai)}'),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 10),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 9),
                            decoration: BoxDecoration(
                              color: AppTheme.primary.withValues(alpha: 0.08),
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: Row(
                              children: [
                                const Icon(Icons.hourglass_bottom_rounded, size: 16, color: AppTheme.primary),
                                const SizedBox(width: 8),
                                Text(
                                  'Rentang dipilih: $durationDays hari kalender',
                                  style: const TextStyle(
                                    fontSize: 12.5,
                                    fontWeight: FontWeight.w700,
                                    color: AppTheme.primaryDark,
                                  ),
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(height: 14),
                          TextField(
                            controller: _alasanCtrl,
                            maxLines: 3,
                            decoration: const InputDecoration(labelText: 'Alasan Cuti *'),
                          ),
                          const SizedBox(height: 14),
                          OutlinedButton.icon(
                            onPressed: () async {
                              final files = await FilePicker.pickFiles(
                                type: FileType.custom,
                                allowedExtensions: ['pdf', 'jpg', 'jpeg', 'png'],
                              );
                              if (files.isNotEmpty) {
                                setState(() => _dokumen = files.first);
                              }
                            },
                            icon: const Icon(Icons.attach_file_rounded),
                            label: Text(
                              _dokumen == null ? 'Lampiran Dokumen (Opsional)' : _dokumen!.name,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 20),
                  FilledButton.icon(
                    onPressed: _submitting ? null : _submit,
                    icon: const Icon(Icons.send_rounded, size: 18),
                    label: Text(_submitting ? 'Mengirim...' : 'Kirim Pengajuan Cuti'),
                  ),
                ],
              ),
            ),
    );
  }
}

class CreateCarScreen extends StatefulWidget {
  const CreateCarScreen({super.key});

  @override
  State<CreateCarScreen> createState() => _CreateCarScreenState();
}

class _CreateCarScreenState extends State<CreateCarScreen> {
  final _alasanCtrl = TextEditingController();
  final _noteCtrl = TextEditingController();
  final _rekeningCtrl = TextEditingController();
  final List<_ItemRow> _items = [_ItemRow()];
  bool _submitting = false;

  @override
  void dispose() {
    _alasanCtrl.dispose();
    _noteCtrl.dispose();
    _rekeningCtrl.dispose();
    for (final item in _items) {
      item.dispose();
    }
    super.dispose();
  }

  Future<void> _submit() async {
    if (_alasanCtrl.text.trim().isEmpty || _items.any((i) => i.nama.text.trim().isEmpty)) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Isi alasan pembelian dan nama seluruh item barang.')),
      );
      return;
    }

    setState(() => _submitting = true);
    try {
      await context.read<ApiClient>().post('/car', data: {
        'alasan_pembelian': _alasanCtrl.text.trim(),
        if (_noteCtrl.text.trim().isNotEmpty) 'note_explanation': _noteCtrl.text.trim(),
        if (_rekeningCtrl.text.trim().isNotEmpty) 'receiving_account': _rekeningCtrl.text.trim(),
        'items': _items
            .map((i) => {
                  'nama_barang': i.nama.text.trim(),
                  'jumlah': int.tryParse(i.qty.text) ?? 1,
                  'satuan': i.satuan.text.trim().isEmpty ? 'PCS' : i.satuan.text.trim(),
                  'estimasi_harga': double.tryParse(i.harga.text) ?? 0,
                  'ongkir': double.tryParse(i.ongkir.text) ?? 0,
                })
            .toList(),
      });
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Pengajuan CAR berhasil dikirim!'), backgroundColor: AppTheme.success),
        );
        Navigator.pop(context, true);
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(e.toString()), backgroundColor: AppTheme.danger),
        );
      }
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  double get _totalEstimasi {
    double total = 0;
    for (final i in _items) {
      final qty = double.tryParse(i.qty.text) ?? 1;
      final harga = double.tryParse(i.harga.text) ?? 0;
      final ongkir = double.tryParse(i.ongkir.text) ?? 0;
      total += (qty * harga) + ongkir;
    }
    return total;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Pengajuan CAR')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.fromLTRB(16, 16, 16, 28),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    const UmbulanSectionHeader(
                      title: 'Informasi Kebutuhan Dana',
                      subtitle: 'Alasan pengeluaran & rekening pencairan',
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: _alasanCtrl,
                      maxLines: 2,
                      decoration: const InputDecoration(labelText: 'Alasan Pembelian / Pengeluaran *'),
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: _rekeningCtrl,
                      decoration: const InputDecoration(labelText: 'Rekening Penerima (Opsional)'),
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: _noteCtrl,
                      decoration: const InputDecoration(labelText: 'Catatan Tambahan (Opsional)'),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),
            UmbulanSectionHeader(
              title: 'Daftar Item Barang',
              subtitle: 'Estimasi Total: ${formatRupiah(_totalEstimasi)}',
              trailing: TextButton.icon(
                onPressed: () => setState(() => _items.add(_ItemRow())),
                icon: const Icon(Icons.add_circle_outline_rounded, size: 18),
                label: const Text('Tambah Item'),
              ),
            ),
            const SizedBox(height: 8),
            ..._items.asMap().entries.map((entry) {
              final idx = entry.key;
              final item = entry.value;
              return Card(
                margin: const EdgeInsets.only(bottom: 12),
                child: Padding(
                  padding: const EdgeInsets.all(14),
                  child: Column(
                    children: [
                      Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 3),
                            decoration: BoxDecoration(
                              color: AppTheme.teal.withValues(alpha: 0.10),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Text(
                              'Item #${idx + 1}',
                              style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 12, color: AppTheme.teal),
                            ),
                          ),
                          const Spacer(),
                          if (_items.length > 1)
                            IconButton(
                              onPressed: () => setState(() => _items.removeAt(idx)),
                              icon: const Icon(Icons.delete_outline, color: AppTheme.danger),
                            ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      TextField(
                        controller: item.nama,
                        decoration: const InputDecoration(labelText: 'Nama Barang *'),
                      ),
                      const SizedBox(height: 10),
                      Row(
                        children: [
                          Expanded(
                            child: TextField(
                              controller: item.qty,
                              keyboardType: TextInputType.number,
                              onChanged: (_) => setState(() {}),
                              decoration: const InputDecoration(labelText: 'Jumlah'),
                            ),
                          ),
                          const SizedBox(width: 10),
                          Expanded(
                            child: TextField(
                              controller: item.satuan,
                              decoration: const InputDecoration(labelText: 'Satuan'),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 10),
                      Row(
                        children: [
                          Expanded(
                            child: TextField(
                              controller: item.harga,
                              keyboardType: TextInputType.number,
                              onChanged: (_) => setState(() {}),
                              decoration: const InputDecoration(labelText: 'Estimasi Harga'),
                            ),
                          ),
                          const SizedBox(width: 10),
                          Expanded(
                            child: TextField(
                              controller: item.ongkir,
                              keyboardType: TextInputType.number,
                              onChanged: (_) => setState(() {}),
                              decoration: const InputDecoration(labelText: 'Ongkir'),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              );
            }),
            const SizedBox(height: 8),
            FilledButton.icon(
              style: FilledButton.styleFrom(backgroundColor: AppTheme.teal),
              onPressed: _submitting ? null : _submit,
              icon: const Icon(Icons.send_rounded, size: 18),
              label: Text(_submitting ? 'Mengirim...' : 'Kirim Pengajuan CAR'),
            ),
          ],
        ),
      ),
    );
  }
}

class CreateMprScreen extends StatefulWidget {
  const CreateMprScreen({super.key});

  @override
  State<CreateMprScreen> createState() => _CreateMprScreenState();
}

class _CreateMprScreenState extends State<CreateMprScreen> {
  String _priority = 'Normal';
  final _deptCtrl = TextEditingController(text: 'Operation');
  final _deliveryCtrl = TextEditingController(text: 'Site Umbulan');
  final _urgensiCtrl = TextEditingController();
  final List<_ItemRow> _items = [_ItemRow()];
  bool _submitting = false;

  @override
  void dispose() {
    _deptCtrl.dispose();
    _deliveryCtrl.dispose();
    _urgensiCtrl.dispose();
    for (final item in _items) {
      item.dispose();
    }
    super.dispose();
  }

  Future<void> _submit() async {
    if (_urgensiCtrl.text.trim().isEmpty || _items.any((i) => i.nama.text.trim().isEmpty)) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Isi keperluan urgensi dan nama barang.')),
      );
      return;
    }

    setState(() => _submitting = true);
    try {
      await context.read<ApiClient>().post('/mpr', data: {
        'priority': _priority,
        'department': _deptCtrl.text.trim(),
        'delivery_point': _deliveryCtrl.text.trim(),
        'keperluan_urgensi': _urgensiCtrl.text.trim(),
        'items': _items
            .map((i) => {
                  'nama_barang': i.nama.text.trim(),
                  'jumlah': int.tryParse(i.qty.text) ?? 1,
                  'satuan': i.satuan.text.trim().isEmpty ? 'PCS' : i.satuan.text.trim(),
                  'estimasi_harga': double.tryParse(i.harga.text) ?? 0,
                })
            .toList(),
      });
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Pengajuan MPR berhasil dikirim!'), backgroundColor: AppTheme.success),
        );
        Navigator.pop(context, true);
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(e.toString()), backgroundColor: AppTheme.danger),
        );
      }
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  double get _totalEstimasiMpr {
    double total = 0;
    for (final i in _items) {
      final qty = double.tryParse(i.qty.text) ?? 1;
      final harga = double.tryParse(i.harga.text) ?? 0;
      total += qty * harga;
    }
    return total;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Pengajuan MPR')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.fromLTRB(16, 16, 16, 28),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    const UmbulanSectionHeader(
                      title: 'Detail Pengadaan Material',
                      subtitle: 'Tentukan prioritas & titik pengiriman barang',
                    ),
                    const SizedBox(height: 12),
                    DropdownButtonFormField<String>(
                      initialValue: _priority,
                      decoration: const InputDecoration(labelText: 'Prioritas'),
                      items: const [
                        DropdownMenuItem(value: 'Normal', child: Text('Normal')),
                        DropdownMenuItem(value: 'Urgent', child: Text('Urgent')),
                        DropdownMenuItem(value: 'Emergency', child: Text('Emergency')),
                      ],
                      onChanged: (v) => setState(() => _priority = v ?? 'Normal'),
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: _deptCtrl,
                      decoration: const InputDecoration(labelText: 'Departemen'),
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: _deliveryCtrl,
                      decoration: const InputDecoration(labelText: 'Titik Pengiriman (Delivery Point)'),
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: _urgensiCtrl,
                      maxLines: 2,
                      decoration: const InputDecoration(labelText: 'Keperluan & Urgensi *'),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),
            UmbulanSectionHeader(
              title: 'Daftar Material',
              subtitle: 'Estimasi Total: ${formatRupiah(_totalEstimasiMpr)}',
              trailing: TextButton.icon(
                onPressed: () => setState(() => _items.add(_ItemRow())),
                icon: const Icon(Icons.add_circle_outline_rounded, size: 18),
                label: const Text('Tambah Item'),
              ),
            ),
            const SizedBox(height: 8),
            ..._items.asMap().entries.map((entry) {
              final idx = entry.key;
              final item = entry.value;
              return Card(
                margin: const EdgeInsets.only(bottom: 12),
                child: Padding(
                  padding: const EdgeInsets.all(14),
                  child: Column(
                    children: [
                      Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 3),
                            decoration: BoxDecoration(
                              color: AppTheme.indigo.withValues(alpha: 0.10),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Text(
                              'Material #${idx + 1}',
                              style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 12, color: AppTheme.indigo),
                            ),
                          ),
                          const Spacer(),
                          if (_items.length > 1)
                            IconButton(
                              onPressed: () => setState(() => _items.removeAt(idx)),
                              icon: const Icon(Icons.delete_outline, color: AppTheme.danger),
                            ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      TextField(
                        controller: item.nama,
                        decoration: const InputDecoration(labelText: 'Nama Material *'),
                      ),
                      const SizedBox(height: 10),
                      Row(
                        children: [
                          Expanded(
                            child: TextField(
                              controller: item.qty,
                              keyboardType: TextInputType.number,
                              onChanged: (_) => setState(() {}),
                              decoration: const InputDecoration(labelText: 'Jumlah'),
                            ),
                          ),
                          const SizedBox(width: 10),
                          Expanded(
                            child: TextField(
                              controller: item.satuan,
                              decoration: const InputDecoration(labelText: 'Satuan'),
                            ),
                          ),
                          const SizedBox(width: 10),
                          Expanded(
                            child: TextField(
                              controller: item.harga,
                              keyboardType: TextInputType.number,
                              onChanged: (_) => setState(() {}),
                              decoration: const InputDecoration(labelText: 'Est. Harga'),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              );
            }),
            const SizedBox(height: 8),
            FilledButton.icon(
              style: FilledButton.styleFrom(backgroundColor: AppTheme.indigo),
              onPressed: _submitting ? null : _submit,
              icon: const Icon(Icons.send_rounded, size: 18),
              label: Text(_submitting ? 'Mengirim...' : 'Kirim Pengajuan MPR'),
            ),
          ],
        ),
      ),
    );
  }
}

class _ItemRow {
  final nama = TextEditingController();
  final qty = TextEditingController(text: '1');
  final satuan = TextEditingController(text: 'PCS');
  final harga = TextEditingController(text: '0');
  final ongkir = TextEditingController(text: '0');

  void dispose() {
    nama.dispose();
    qty.dispose();
    satuan.dispose();
    harga.dispose();
    ongkir.dispose();
  }
}
