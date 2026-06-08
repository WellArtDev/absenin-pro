import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import '../services/api_client.dart';
import 'package:intl/intl.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  Map<String, dynamic>? _session;
  bool _loading = false;
  String? _statusText;
  Color? _statusColor;

  @override
  void initState() {
    super.initState();
    _checkStatus();
  }

  Future<void> _checkStatus() async {
    final api = context.read<ApiClient>();
    try {
      final r = await api.dio.get('/api/v1/attendance/status/today');
      if (r.data['success'] == true && mounted) {
        setState(() {
          _session = r.data['data'];
          _statusText = 'Hadir · ${DateFormat('HH:mm').format(DateTime.tryParse(_session?['clock_in'] ?? '') ?? DateTime.now())}';
          _statusColor = Colors.green;
        });
      }
    } catch (_) {
      if (mounted) setState(() { _statusText = 'Belum Presensi'; _statusColor = Colors.grey; });
    }
  }

  Future<void> _clockIn() async {
    setState(() => _loading = true);
    final api = context.read<ApiClient>();
    try {
      await api.dio.post('/api/v1/attendance/clock-in', data: {
        'gps_lat': -6.2088,
        'gps_lng': 106.8456,
        'device_id': 'flutter-device',
      });
      if (mounted) {
        setState(() { _statusText = 'Hadir · ${DateFormat('HH:mm').format(DateTime.now())}'; _statusColor = Colors.green; _loading = false; });
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Presensi berhasil. $_statusText'), backgroundColor: Colors.green));
      }
    } catch (e) {
      if (mounted) {
        setState(() => _loading = false);
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Gagal presensi'), backgroundColor: Colors.red));
      }
    }
  }

  Future<void> _clockOut() async {
    setState(() => _loading = true);
    final api = context.read<ApiClient>();
    try {
      await api.dio.post('/api/v1/attendance/clock-out');
      if (mounted) {
        setState(() { _statusText = 'Belum Presensi'; _statusColor = Colors.grey; _loading = false; });
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Clock-out berhasil'), backgroundColor: Colors.green));
      }
    } catch (e) {
      if (mounted) {
        setState(() => _loading = false);
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Gagal clock-out'), backgroundColor: Colors.red));
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final isClockedIn = _session != null && (_session!['status'] == 'hadir' || _session!['status'] == 'lembur');
    final theme = Theme.of(context);

    return Scaffold(
      appBar: AppBar(title: const Text('Absenin'), centerTitle: true),
      body: Center(
        child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
          Container(
            margin: const EdgeInsets.symmetric(horizontal: 24),
            padding: const EdgeInsets.all(24),
            decoration: BoxDecoration(
              color: _statusColor?.withValues(alpha: 0.1) ?? theme.colorScheme.surfaceContainerHighest,
              borderRadius: BorderRadius.circular(16),
            ),
            child: Column(children: [
              Icon(Icons.access_time, size: 48, color: _statusColor),
              const SizedBox(height: 8),
              Text(_statusText ?? 'Memuat...', style: theme.textTheme.titleLarge?.copyWith(fontWeight: FontWeight.bold)),
            ]),
          ),
          const SizedBox(height: 48),
          if (isClockedIn) ...[
            OutlinedButton.icon(
              onPressed: () {},
              icon: const Icon(Icons.gps_fixed, size: 18),
              label: const Text('Mode Spesifik'),
            ),
            const SizedBox(height: 8),
            OutlinedButton.icon(
              onPressed: () {},
              icon: const Icon(Icons.gps_off, size: 18),
              label: const Text('Mode Bebas'),
            ),
            const SizedBox(height: 16),
          ],
          FloatingActionButton.extended(
            onPressed: _loading ? null : (isClockedIn ? _clockOut : _clockIn),
            icon: Icon(isClockedIn ? Icons.logout : Icons.camera_alt),
            label: Text(isClockedIn ? 'Clock Out' : 'Clock In'),
            backgroundColor: isClockedIn ? Colors.red.shade600 : theme.colorScheme.primary,
            foregroundColor: Colors.white,
          ),
        ]),
      ),
      bottomNavigationBar: _nav(context, 0),
    );
  }
}

NavigationBar _nav(BuildContext context, int selected) {
  return NavigationBar(
    selectedIndex: selected,
    onDestinationSelected: (i) {
      switch (i) {
        case 0: break;
        case 1: context.go('/history');
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
