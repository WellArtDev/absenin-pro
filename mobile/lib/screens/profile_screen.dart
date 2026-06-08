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
    final api = context.read<ApiClient>();
    try {
      final results = await Future.wait([
        api.dio.get('/api/v1/devices/status'),
        api.dio.get('/api/v1/attendance/summary'),
      ]);
      if (mounted) {
        setState(() {
          _deviceInfo = results[0].data['success'] == true ? results[0].data['data'] : null;
          _loading = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _requestDeviceChange() async {
    final api = context.read<ApiClient>();
    final reasonCtrl = TextEditingController();

    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Ganti Perangkat'),
        content: Column(mainAxisSize: MainAxisSize.min, children: [
          const Text('Permintaan ganti perangkat akan dikirim ke HR untuk approval.'),
          const SizedBox(height: 12),
          TextField(controller: reasonCtrl, decoration: const InputDecoration(labelText: 'Alasan', hintText: 'Misal: HP rusak / ganti HP baru'), maxLines: 2),
        ]),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
          FilledButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Kirim')),
        ],
      ),
    );

    if (ok == true) {
      try {
        await api.dio.post('/api/v1/devices/request-change', data: {'reason': reasonCtrl.text});
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Permintaan dikirim. Menunggu approval HR.'), backgroundColor: Colors.green));
          _load();
        }
      } catch (e) {
        if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Gagal: $e'), backgroundColor: Colors.red));
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final auth = context.watch<AuthProvider>();

    return Scaffold(
      appBar: AppBar(title: const Text('Profil')),
      body: _loading
        ? const Center(child: CircularProgressIndicator())
        : ListView(padding: const EdgeInsets.all(24), children: [
            const CircleAvatar(radius: 40, child: Icon(Icons.person, size: 40)),
            const SizedBox(height: 16),
            Center(child: Text('Absenin Mobile', style: theme.textTheme.titleMedium)),
            Center(child: Text('v1.0.0', style: theme.textTheme.bodySmall?.copyWith(color: theme.colorScheme.onSurfaceVariant))),
            const SizedBox(height: 32),

            if (_deviceInfo != null) Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  Row(children: [
                    Icon(Icons.phone_android, color: theme.colorScheme.primary),
                    const SizedBox(width: 8),
                    Text('Perangkat', style: theme.textTheme.titleSmall),
                    const Spacer(),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                      decoration: BoxDecoration(
                        color: _deviceInfo!['is_active'] == true ? Colors.green.withValues(alpha: 0.1) : Colors.orange.withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Text(
                        _deviceInfo!['is_active'] == true ? 'Aktif' : 'Pending',
                        style: TextStyle(fontSize: 11, fontWeight: FontWeight.w500, color: _deviceInfo!['is_active'] == true ? Colors.green : Colors.orange),
                      ),
                    ),
                  ]),
                  const SizedBox(height: 12),
                  Text('Device ID: ${(_deviceInfo!['device_id'] ?? '-').toString().substring(0, 12)}...', style: theme.textTheme.bodySmall),
                  const SizedBox(height: 8),
                  OutlinedButton.icon(
                    onPressed: _deviceInfo!['pending_change'] == true ? null : _requestDeviceChange,
                    icon: const Icon(Icons.swap_horiz, size: 18),
                    label: Text(_deviceInfo!['pending_change'] == true ? 'Menunggu Approval HR' : 'Ganti Perangkat'),
                  ),
                ]),
              ),
            ),

            const SizedBox(height: 16),
            Card(
              child: ListTile(
                leading: Icon(Icons.beach_access, color: theme.colorScheme.primary),
                title: const Text('Sisa Cuti'),
                subtitle: const Text('12 hari'),
                trailing: Text('12', style: theme.textTheme.headlineMedium?.copyWith(color: theme.colorScheme.primary)),
              ),
            ),

            const SizedBox(height: 24),
            ListTile(
              leading: const Icon(Icons.logout, color: Colors.red),
              title: const Text('Keluar'),
              onTap: () async {
                final confirm = await showDialog<bool>(context: context, builder: (ctx) => AlertDialog(
                  title: const Text('Keluar?'),
                  content: const Text('Anda akan logout dari aplikasi.'),
                  actions: [
                    TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
                    FilledButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Keluar')),
                  ],
                ));
                if (confirm == true) {
                  await auth.logout();
                  if (context.mounted) context.go('/login');
                }
              },
            ),
          ]),
      bottomNavigationBar: _nav(context, 3),
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
        case 2: context.go('/leave');
        case 3: break;
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
