import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import '../services/api_client.dart';
import '../providers/auth_provider.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});
  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  Map<String, dynamic>? _deviceInfo;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    try {
      final r = await context.read<ApiClient>().dio.get('/api/v1/devices/status');
      if (r.data['success'] == true && mounted) setState(() => _deviceInfo = r.data['data']);
    } catch (_) {}
    if (mounted) setState(() => _loading = false);
  }

  Future<void> _requestDeviceChange() async {
    final reasonCtrl = TextEditingController();
    final ok = await showDialog<bool>(context: context, builder: (ctx) => AlertDialog(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
      title: const Text('Ganti Perangkat'),
      content: Column(mainAxisSize: MainAxisSize.min, children: [
        const Text('Permintaan akan dikirim ke HR untuk approval.'),
        const SizedBox(height: 12),
        TextField(controller: reasonCtrl, decoration: const InputDecoration(labelText: 'Alasan', border: OutlineInputBorder(borderRadius: BorderRadius.all(Radius.circular(12)))), maxLines: 2),
      ]),
      actions: [
        TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
        FilledButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Kirim')),
      ],
    ));
    if (ok == true) {
      try {
        await context.read<ApiClient>().dio.post('/api/v1/devices/request-change', data: {'reason': reasonCtrl.text});
        if (mounted) { _showSnackBar('Permintaan dikirim'); _load(); }
      } catch (_) { if (mounted) _showSnackBar('Gagal', Colors.red); }
    }
  }

  void _showSnackBar(String msg, [Color? color]) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(msg), backgroundColor: color ?? Colors.green, behavior: SnackBarBehavior.floating,
      margin: const EdgeInsets.all(16), shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
    ));
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final auth = context.watch<AuthProvider>();

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(title: const Text('Profil', style: TextStyle(fontWeight: FontWeight.bold)), centerTitle: true),
      body: _loading ? const Center(child: CircularProgressIndicator()) : ListView(padding: const EdgeInsets.all(20), children: [
        const SizedBox(height: 20),
        Center(child: Container(
          padding: const EdgeInsets.all(3),
          decoration: BoxDecoration(shape: BoxShape.circle, border: Border.all(color: theme.colorScheme.primary, width: 2)),
          child: CircleAvatar(radius: 44, backgroundColor: theme.colorScheme.primaryContainer, child: Icon(Icons.person, size: 44, color: theme.colorScheme.primary)),
        )),
        const SizedBox(height: 12),
        Center(child: Text('Absenin Mobile', style: theme.textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold))),
        Center(child: Text('v1.0.0', style: TextStyle(fontSize: 13, color: Colors.grey.shade500))),
        const SizedBox(height: 32),

        _sectionTitle('Perangkat'),
        Container(
          decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16), boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.04), blurRadius: 8)]),
          padding: const EdgeInsets.all(16),
          child: Row(children: [
            Container(width: 44, height: 44, decoration: BoxDecoration(color: (_deviceInfo?['is_active'] == true ? Colors.green : Colors.orange).withValues(alpha: 0.1), borderRadius: BorderRadius.circular(12)), child: Icon(Icons.phone_android, color: _deviceInfo?['is_active'] == true ? Colors.green : Colors.orange)),
            const SizedBox(width: 14),
            Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text('Device ID', style: const TextStyle(fontWeight: FontWeight.w600)),
              Text((_deviceInfo?['device_id'] ?? '-').toString().length > 12 ? '${(_deviceInfo!['device_id']).toString().substring(0, 12)}...' : '-', style: TextStyle(fontSize: 12, color: Colors.grey.shade500)),
            ])),
            Container(padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4), decoration: BoxDecoration(color: (_deviceInfo?['is_active'] == true ? Colors.green : Colors.orange).withValues(alpha: 0.1), borderRadius: BorderRadius.circular(20)), child: Text(_deviceInfo?['is_active'] == true ? 'Aktif' : 'Pending', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: _deviceInfo?['is_active'] == true ? Colors.green : Colors.orange))),
          ]),
        ),
        const SizedBox(height: 8),
        OutlinedButton.icon(
          onPressed: _deviceInfo?['pending_change'] == true ? null : _requestDeviceChange,
          icon: const Icon(Icons.swap_horiz, size: 18),
          label: Text(_deviceInfo?['pending_change'] == true ? 'Menunggu Approval' : 'Ganti Perangkat'),
          style: OutlinedButton.styleFrom(shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)), minimumSize: const Size(double.infinity, 48)),
        ),
        const SizedBox(height: 24),

        _sectionTitle('Info'),
        Container(
          decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16), boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.04), blurRadius: 8)]),
          child: Column(children: [
            _infoTile(Icons.beach_access, 'Sisa Cuti', '12 hari', theme.colorScheme.primary),
            const Divider(height: 1),
            _infoTile(Icons.work_outline, 'Status', 'Tetap', Colors.blue),
          ]),
        ),
        const SizedBox(height: 24),

        SizedBox(
          width: double.infinity, height: 48,
          child: OutlinedButton.icon(
            onPressed: () async {
              final c = await showDialog<bool>(context: context, builder: (ctx) => AlertDialog(
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                title: const Text('Keluar?'), content: const Text('Anda akan logout.'),
                actions: [TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')), FilledButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Keluar'))],
              ));
              if (c == true) { await auth.logout(); if (context.mounted) context.go('/login'); }
            },
            icon: const Icon(Icons.logout, color: Colors.red, size: 20),
            label: const Text('Keluar', style: TextStyle(color: Colors.red)),
            style: OutlinedButton.styleFrom(shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)), side: const BorderSide(color: Colors.red)),
          ),
        ),
        const SizedBox(height: 40),
      ]),
      bottomNavigationBar: _nav(context, 3),
    );
  }

  Widget _sectionTitle(String title) => Padding(padding: const EdgeInsets.only(left: 4, bottom: 10), child: Text(title, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, letterSpacing: 0.5)));

  Widget _infoTile(IconData icon, String title, String value, Color color) {
    return ListTile(
      leading: Container(width: 40, height: 40, decoration: BoxDecoration(color: color.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(10)), child: Icon(icon, color: color, size: 20)),
      title: Text(title, style: const TextStyle(fontSize: 14)),
      trailing: Text(value, style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: color)),
    );
  }
}

NavigationBar _nav(BuildContext context, int selected) {
  return NavigationBar(
    selectedIndex: selected, indicatorColor: Theme.of(context).colorScheme.primary.withValues(alpha: 0.15),
    onDestinationSelected: (i) { switch (i) { case 0: context.go('/home'); case 1: context.go('/history'); case 2: context.go('/leave'); case 3: break; } },
    destinations: const [
      NavigationDestination(icon: Icon(Icons.home_outlined), selectedIcon: Icon(Icons.home_rounded), label: 'Home'),
      NavigationDestination(icon: Icon(Icons.history_outlined), selectedIcon: Icon(Icons.history_rounded), label: 'Riwayat'),
      NavigationDestination(icon: Icon(Icons.edit_note_outlined), selectedIcon: Icon(Icons.edit_note_rounded), label: 'Pengajuan'),
      NavigationDestination(icon: Icon(Icons.person_outlined), selectedIcon: Icon(Icons.person_rounded), label: 'Profil'),
    ],
  );
}
