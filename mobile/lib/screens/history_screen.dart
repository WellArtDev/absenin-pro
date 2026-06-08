import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import '../services/api_client.dart';
import 'package:intl/intl.dart';

class HistoryScreen extends StatefulWidget {
  const HistoryScreen({super.key});

  @override
  State<HistoryScreen> createState() => _HistoryScreenState();
}

class _HistoryScreenState extends State<HistoryScreen> {
  List<dynamic> _items = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    final api = context.read<ApiClient>();
    try {
      final r = await api.dio.get('/api/v1/attendance/log');
      if (r.data['success'] && mounted) {
        setState(() { _items = r.data['data'] ?? []; _loading = false; });
      }
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Riwayat Presensi')),
      body: _loading
        ? const Center(child: CircularProgressIndicator())
        : _items.isEmpty
          ? const Center(child: Text('Belum ada riwayat presensi'))
          : ListView.builder(
              itemCount: _items.length,
              itemBuilder: (_, i) {
                final s = _items[i];
                final status = s['status'] ?? '-';
                final color = status == 'hadir' ? Colors.green : status == 'terlambat' ? Colors.orange : status == 'lembur' ? Colors.blue : Colors.grey;

                return Card(
                  margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
                  child: ListTile(
                    leading: CircleAvatar(backgroundColor: color.withValues(alpha: 0.2), child: Icon(Icons.access_time, color: color)),
                    title: Text(s['employee_name'] ?? '-'),
                    subtitle: Text('${DateFormat('dd MMM yyyy').format(DateTime.tryParse(s['created_at'] ?? '') ?? DateTime.now())}  ·  ${s['clock_in']?.toString().substring(0, 19) ?? '-'}'),
                    trailing: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(color: color.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(12)),
                      child: Text(status, style: TextStyle(color: color, fontSize: 12, fontWeight: FontWeight.w500)),
                    ),
                  ),
                );
              },
            ),
      bottomNavigationBar: _nav(context, 1),
    );
  }
}

NavigationBar _nav(BuildContext context, int selected) {
  return NavigationBar(
    selectedIndex: selected,
    onDestinationSelected: (i) {
      switch (i) {
        case 0: context.go('/home');
        case 1: break;
        case 2: context.go('/leave');
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
