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
    final api = context.read<ApiClient>();
    try {
      final results = await Future.wait([
        api.dio.get('/api/v1/leaves?status=pending'),
        api.dio.get('/api/v1/leaves?limit=50'),
      ]);
      if (mounted) {
        setState(() {
          _pending = results[0].data['success'] == true ? (results[0].data['data'] ?? []) : [];
          _history = results[1].data['success'] == true ? (results[1].data['data'] ?? []) : [];
          _loading = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _submitLeave() async {
    final api = context.read<ApiClient>();
    final typeCtrl = TextEditingController(text: 'cuti_tahunan');
    final startCtrl = TextEditingController();
    final endCtrl = TextEditingController();
    final reasonCtrl = TextEditingController();

    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Ajukan Cuti/Izin'),
        content: Column(mainAxisSize: MainAxisSize.min, children: [
          DropdownButtonFormField<String>(
            initialValue: 'cuti_tahunan',
            items: const [
              DropdownMenuItem(value: 'cuti_tahunan', child: Text('Cuti Tahunan')),
              DropdownMenuItem(value: 'izin', child: Text('Izin')),
              DropdownMenuItem(value: 'sakit', child: Text('Sakit')),
            ],
            onChanged: (v) => typeCtrl.text = v!,
            decoration: const InputDecoration(labelText: 'Tipe'),
          ),
          const SizedBox(height: 12),
          TextField(controller: startCtrl, decoration: const InputDecoration(labelText: 'Tanggal Mulai'), onTap: () async {
            final d = await showDatePicker(context: ctx, firstDate: DateTime.now(), lastDate: DateTime.now().add(const Duration(days: 365)));
            if (d != null) startCtrl.text = DateFormat('yyyy-MM-dd').format(d);
          }, readOnly: true),
          const SizedBox(height: 8),
          TextField(controller: endCtrl, decoration: const InputDecoration(labelText: 'Tanggal Selesai'), onTap: () async {
            final d = await showDatePicker(context: ctx, firstDate: DateTime.now(), lastDate: DateTime.now().add(const Duration(days: 365)));
            if (d != null) endCtrl.text = DateFormat('yyyy-MM-dd').format(d);
          }, readOnly: true),
          const SizedBox(height: 8),
          TextField(controller: reasonCtrl, decoration: const InputDecoration(labelText: 'Alasan'), maxLines: 2),
        ]),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
          FilledButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Kirim')),
        ],
      ),
    );

    if (ok == true) {
      try {
        await api.dio.post('/api/v1/leaves', data: {
          'leave_type': typeCtrl.text,
          'start_date': startCtrl.text,
          'end_date': endCtrl.text,
          'reason': reasonCtrl.text,
        });
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Pengajuan dikirim'), backgroundColor: Colors.green));
          _load();
        }
      } catch (e) {
        if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Gagal: $e'), backgroundColor: Colors.red));
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Pengajuan Cuti'), bottom: TabBar(controller: _tabCtrl, tabs: const [Tab(text: 'Pending'), Tab(text: 'Riwayat')])),
      floatingActionButton: FloatingActionButton.extended(onPressed: _submitLeave, icon: const Icon(Icons.add), label: const Text('Ajukan')),
      body: _loading ? const Center(child: CircularProgressIndicator())
        : TabBarView(controller: _tabCtrl, children: [
            _buildList(_pending, isPending: true),
            _buildList(_history, isPending: false),
          ]),
      bottomNavigationBar: _nav(context, 2),
    );
  }

  Widget _buildList(List items, {required bool isPending}) {
    if (items.isEmpty) return const Center(child: Text('Belum ada pengajuan'));
    return ListView.builder(
      itemCount: items.length,
      itemBuilder: (_, i) {
        final l = items[i];
        final status = l['status'] ?? 'pending';
        final color = status == 'disetujui' || status == 'approved' ? Colors.green : status == 'ditolak' || status == 'rejected' ? Colors.red : Colors.orange;
        return Card(
          margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
          child: ListTile(
            title: Text(l['leave_type'] ?? '-'),
            subtitle: Text('${l['start_date']} - ${l['end_date']}\n${l['reason'] ?? ''}'),
            trailing: Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
              decoration: BoxDecoration(color: color.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(12)),
              child: Text(status, style: TextStyle(color: color, fontSize: 12, fontWeight: FontWeight.w500)),
            ),
          ),
        );
      },
    );
  }
}

NavigationBar _nav(BuildContext context, int selected) {
  return NavigationBar(
    selectedIndex: selected,
    onDestinationSelected: (i) {
      switch (i) {
        case 0: context.go('/home');
        case 1: context.go('/history');
        case 2: break;
        case 3: context.go('/profile');
      }
    },
    destinations: const [
      NavigationDestination(icon: Icon(Icons.home_outlined), selectedIcon: Icon(Icons.home), label: 'Home'),
      NavigationDestination(icon: Icon(Icons.history_outlined), selectedIcon: Icon(Icons.history), label: 'Riwayat'),
      NavigationDestination(icon: Icon(Icons.edit_note_outlined), selectedIcon: Icon(Icons.edit_note), label: 'Pengajuan'),
      NavigationDestination(icon: Icon(Icons.person_outlined), selectedIcon: Icon(Icons.person), label: 'Profil'),
    ],
  );
}
