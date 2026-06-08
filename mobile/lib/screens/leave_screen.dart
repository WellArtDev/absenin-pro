import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import '../services/api_client.dart';
import 'package:intl/intl.dart';

class LeaveScreen extends StatefulWidget {
  const LeaveScreen({super.key});
  @override
  State<LeaveScreen> createState() => _LeaveScreenState();
}

class _LeaveScreenState extends State<LeaveScreen> with SingleTickerProviderStateMixin {
  late TabController _tabCtrl;
  List<dynamic> _pending = [];
  List<dynamic> _history = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _tabCtrl = TabController(length: 2, vsync: this);
    _load();
  }

  Future<void> _load() async {
    try {
      final results = await Future.wait([
        context.read<ApiClient>().dio.get('/api/v1/leaves?status=pending'),
        context.read<ApiClient>().dio.get('/api/v1/leaves?limit=50'),
      ]);
      if (mounted) {
        setState(() {
          _pending = results[0].data['success'] == true ? (results[0].data['data'] ?? []) : [];
          _history = results[1].data['success'] == true ? (results[1].data['data'] ?? []) : [];
          _loading = false;
        });
      }
    } catch (_) { if (mounted) setState(() => _loading = false); }
  }

  Future<void> _submitLeave() async {
    final typeCtrl = TextEditingController(text: 'cuti_tahunan');
    final startCtrl = TextEditingController();
    final endCtrl = TextEditingController();
    final reasonCtrl = TextEditingController();

    final ok = await showDialog<bool>(context: context, builder: (ctx) => AlertDialog(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
      title: const Text('Ajukan Cuti/Izin', style: TextStyle(fontWeight: FontWeight.bold)),
      content: StatefulBuilder(builder: (_, setDlg) => Column(mainAxisSize: MainAxisSize.min, children: [
        DropdownButtonFormField<String>(
          initialValue: 'cuti_tahunan', decoration: const InputDecoration(labelText: 'Tipe', border: OutlineInputBorder(borderRadius: BorderRadius.all(Radius.circular(12)))),
          items: const [
            DropdownMenuItem(value: 'cuti_tahunan', child: Text('Cuti Tahunan')),
            DropdownMenuItem(value: 'izin', child: Text('Izin')),
            DropdownMenuItem(value: 'sakit', child: Text('Sakit')),
          ],
          onChanged: (v) { typeCtrl.text = v!; },
        ),
        const SizedBox(height: 12),
        _dateField(startCtrl, 'Mulai', ctx),
        const SizedBox(height: 8),
        _dateField(endCtrl, 'Selesai', ctx),
        const SizedBox(height: 8),
        TextField(controller: reasonCtrl, decoration: const InputDecoration(labelText: 'Alasan', border: OutlineInputBorder(borderRadius: BorderRadius.all(Radius.circular(12)))), maxLines: 2),
      ])),
      actions: [
        TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
        FilledButton(onPressed: () => Navigator.pop(ctx, true), style: FilledButton.styleFrom(shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12))), child: const Text('Kirim')),
      ],
    ));

    if (ok == true) {
      try {
        await context.read<ApiClient>().dio.post('/api/v1/leaves', data: {'leave_type': typeCtrl.text, 'start_date': startCtrl.text, 'end_date': endCtrl.text, 'reason': reasonCtrl.text});
        if (mounted) { _showSnackBar('Pengajuan dikirim'); _load(); }
      } catch (_) { if (mounted) _showSnackBar('Gagal', Colors.red); }
    }
  }

  Widget _dateField(TextEditingController ctrl, String label, BuildContext ctx) {
    return TextField(
      controller: ctrl, readOnly: true,
      decoration: InputDecoration(labelText: label, suffixIcon: const Icon(Icons.calendar_today, size: 18), border: const OutlineInputBorder(borderRadius: BorderRadius.all(Radius.circular(12)))),
      onTap: () async {
        final d = await showDatePicker(context: ctx, firstDate: DateTime.now(), lastDate: DateTime.now().add(const Duration(days: 365)));
        if (d != null) ctrl.text = DateFormat('yyyy-MM-dd').format(d);
      },
    );
  }

  void _showSnackBar(String msg, [Color? color]) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(msg), backgroundColor: color ?? Colors.green,
      behavior: SnackBarBehavior.floating, margin: const EdgeInsets.all(16),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
    ));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(title: const Text('Pengajuan', style: TextStyle(fontWeight: FontWeight.bold)), centerTitle: true,
        bottom: TabBar(controller: _tabCtrl, indicatorSize: TabBarIndicatorSize.label, tabs: const [Tab(text: 'Pending'), Tab(text: 'Riwayat')])),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _submitLeave, icon: const Icon(Icons.add_rounded),
        label: const Text('Ajukan'), backgroundColor: Theme.of(context).colorScheme.primary,
      ),
      body: _loading ? const Center(child: CircularProgressIndicator())
        : TabBarView(controller: _tabCtrl, children: [_buildList(_pending, true), _buildList(_history, false)]),
      bottomNavigationBar: _nav(context, 2),
    );
  }

  Widget _buildList(List items, bool isPending) {
    if (items.isEmpty) return Center(child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
      Icon(isPending ? Icons.check_circle_outline : Icons.inbox_outlined, size: 48, color: Colors.grey.shade300),
      const SizedBox(height: 8),
      Text(isPending ? 'Semua diproses' : 'Belum ada riwayat', style: const TextStyle(color: Colors.grey)),
    ]));
    return ListView.builder(
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
      itemCount: items.length,
      itemBuilder: (_, i) {
        final l = items[i];
        final status = l['status'] ?? 'pending';
        final color = status == 'disetujui' || status == 'approved' ? Colors.green : status == 'ditolak' || status == 'rejected' ? Colors.red : Colors.orange;
        return Container(
          margin: const EdgeInsets.only(bottom: 10),
          decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16), boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.04), blurRadius: 8)]),
          child: Padding(padding: const EdgeInsets.all(16), child: Row(children: [
            Container(width: 48, height: 48, decoration: BoxDecoration(color: color.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(14)), child: Icon(Icons.event_note, color: color)),
            const SizedBox(width: 14),
            Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text(l['leave_type'] ?? '-', style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w600)),
              Text('${l['start_date']} - ${l['end_date']}', style: TextStyle(fontSize: 12, color: Colors.grey.shade500)),
              if (l['reason'] != null && l['reason'].toString().isNotEmpty)
                Text(l['reason'].toString(), style: TextStyle(fontSize: 12, color: Colors.grey.shade400), maxLines: 1, overflow: TextOverflow.ellipsis),
            ])),
            Container(padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4), decoration: BoxDecoration(color: color.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(20)), child: Text(status, style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: color))),
          ])),
        );
      },
    );
  }
}

NavigationBar _nav(BuildContext context, int selected) {
  return NavigationBar(
    selectedIndex: selected, indicatorColor: Theme.of(context).colorScheme.primary.withValues(alpha: 0.15),
    onDestinationSelected: (i) { switch (i) { case 0: context.go('/home'); case 1: context.go('/history'); case 2: break; case 3: context.go('/profile'); } },
    destinations: const [
      NavigationDestination(icon: Icon(Icons.home_outlined), selectedIcon: Icon(Icons.home_rounded), label: 'Home'),
      NavigationDestination(icon: Icon(Icons.history_outlined), selectedIcon: Icon(Icons.history_rounded), label: 'Riwayat'),
      NavigationDestination(icon: Icon(Icons.edit_note_outlined), selectedIcon: Icon(Icons.edit_note_rounded), label: 'Pengajuan'),
      NavigationDestination(icon: Icon(Icons.person_outlined), selectedIcon: Icon(Icons.person_rounded), label: 'Profil'),
    ],
  );
}
