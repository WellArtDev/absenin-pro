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
    try {
      final r = await context.read<ApiClient>().dio.get('/api/v1/attendance/log');
      if (r.data['success'] == true && mounted) setState(() { _items = r.data['data'] ?? []; _loading = false; });
    } catch (_) { if (mounted) setState(() => _loading = false); }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(title: const Text('Riwayat', style: TextStyle(fontWeight: FontWeight.bold)), centerTitle: true),
      body: _loading ? const Center(child: CircularProgressIndicator())
        : _items.isEmpty ? _buildEmpty()
        : RefreshIndicator(
            onRefresh: _load,
            child: ListView.builder(
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
              itemCount: _items.length,
              itemBuilder: (_, i) => _buildCard(_items[i], theme),
            ),
          ),
      bottomNavigationBar: _nav(context, 1),
    );
  }

  Widget _buildEmpty() {
    return Center(
      child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
        Container(
          padding: const EdgeInsets.all(24),
          decoration: BoxDecoration(color: Colors.grey.shade100, shape: BoxShape.circle),
          child: Icon(Icons.history, size: 48, color: Colors.grey.shade400),
        ),
        const SizedBox(height: 16),
        const Text('Belum ada riwayat', style: TextStyle(fontSize: 16, color: Colors.grey)),
        const Text('Presensi akan muncul di sini', style: TextStyle(fontSize: 13, color: Colors.grey)),
      ]),
    );
  }

  Widget _buildCard(dynamic s, ThemeData theme) {
    final status = s['status'] ?? '-';
    final color = status == 'hadir' ? Colors.green : status == 'terlambat' ? Colors.orange : status == 'lembur' ? Colors.indigo : Colors.grey;
    final date = DateTime.tryParse(s['created_at'] ?? '');
    final time = s['clock_in']?.toString().substring(0, 19) ?? '-';

    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16), boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.04), blurRadius: 8, offset: const Offset(0, 2))]),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(children: [
          Container(
            width: 48, height: 48,
            decoration: BoxDecoration(color: color.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(14)),
            child: Icon(Icons.access_time, color: color, size: 24),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text(s['employee_name'] ?? 'Karyawan', style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w600)),
              const SizedBox(height: 2),
              Text(time, style: TextStyle(fontSize: 12, color: Colors.grey.shade500)),
              if (date != null)
                Text(DateFormat('EEEE, dd MMM yyyy', 'id_ID').format(date), style: TextStyle(fontSize: 12, color: Colors.grey.shade400)),
            ]),
          ),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
            decoration: BoxDecoration(color: color.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(20)),
            child: Text(status, style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: color)),
          ),
        ]),
      ),
    );
  }
}

NavigationBar _nav(BuildContext context, int selected) {
  return NavigationBar(
    selectedIndex: selected, indicatorColor: Theme.of(context).colorScheme.primary.withValues(alpha: 0.15),
    onDestinationSelected: (i) {
      switch (i) { case 0: context.go('/home'); case 1: break; case 2: context.go('/leave'); case 3: context.go('/profile'); }
    },
    destinations: const [
      NavigationDestination(icon: Icon(Icons.home_outlined), selectedIcon: Icon(Icons.home_rounded), label: 'Home'),
      NavigationDestination(icon: Icon(Icons.history_outlined), selectedIcon: Icon(Icons.history_rounded), label: 'Riwayat'),
      NavigationDestination(icon: Icon(Icons.edit_note_outlined), selectedIcon: Icon(Icons.edit_note_rounded), label: 'Pengajuan'),
      NavigationDestination(icon: Icon(Icons.person_outlined), selectedIcon: Icon(Icons.person_rounded), label: 'Profil'),
    ],
  );
}
