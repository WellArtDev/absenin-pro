import 'dart:io';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import 'package:dio/dio.dart';
import '../services/api_client.dart';
import '../services/gps_service.dart';
import '../services/camera_service.dart';
import 'package:intl/intl.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  final _gpsService = GpsService();
  final _cameraService = CameraService();

  Map<String, dynamic>? _session;
  bool _loading = false;
  String? _statusText;
  Color? _statusColor;
  String _gpsStatus = 'accurate';
  String _gpsMode = 'Bebas';
  Map<String, dynamic>? _selectedClient;
  List<dynamic> _clients = [];

  @override
  void initState() {
    super.initState();
    _init();
  }

  Future<void> _init() async {
    _checkStatus();
    _updateGpsStatus();
    _loadClients();
  }

  Future<void> _updateGpsStatus() async {
    final status = await _gpsService.getGpsStatus();
    if (mounted) setState(() => _gpsStatus = status);
  }

  Future<void> _loadClients() async {
    final api = context.read<ApiClient>();
    try {
      final r = await api.dio.get('/api/v1/clients');
      if (r.data['success'] == true && mounted) {
        setState(() => _clients = r.data['data'] ?? []);
      }
    } catch (_) {}
  }

  Future<void> _checkStatus() async {
    final api = context.read<ApiClient>();
    try {
      final r = await api.dio.get('/api/v1/attendance/status/today');
      if (r.data['success'] == true && mounted) {
        final s = r.data['data'];
        setState(() {
          _session = s;
          _statusText = 'Hadir \u00b7 ${DateFormat('HH:mm').format(DateTime.tryParse(s['clock_in'] ?? '') ?? DateTime.now())}';
          _statusColor = Colors.green;
        });
      }
    } catch (_) {
      if (mounted) setState(() { _statusText = 'Belum Presensi'; _statusColor = Colors.grey; });
    }
  }

  Future<void> _clockIn() async {
    setState(() => _loading = true);

    try {
      final position = await _gpsService.getCurrentPosition();

      if (_gpsMode == 'Spesifik' && _selectedClient != null) {
        final lat = double.tryParse(_selectedClient!['gps_lat']?.toString() ?? '') ?? 0;
        final lng = double.tryParse(_selectedClient!['gps_lng']?.toString() ?? '') ?? 0;
        final radius = double.tryParse(_selectedClient!['radius_meters']?.toString() ?? '10') ?? 10;

        final within = _gpsService.isWithinRadius(position, lat, lng, radius);
        if (!within && mounted) {
          _showGpsWarning();
          setState(() => _loading = false);
          return;
        }
      }

      final selfie = await _cameraService.takeSelfie();
      if (selfie == null) {
        if (mounted) setState(() => _loading = false);
        return;
      }

      await _uploadClockIn(selfie, position);
    } catch (e) {
      if (mounted) {
        setState(() => _loading = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal: $e'), backgroundColor: Colors.red),
        );
      }
    }
  }

  void _showGpsWarning() {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Di luar radius'),
        content: Text('Anda berada di luar radius ${_selectedClient!['name']} (${_selectedClient!['radius_meters']}m).'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Batal')),
          FilledButton(onPressed: () {
            Navigator.pop(ctx);
            ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Laporan dikirim ke HR')));
          }, child: const Text('Laporkan Masalah')),
        ],
      ),
    );
  }

  Future<void> _uploadClockIn(File selfie, dynamic position) async {
    final api = context.read<ApiClient>();
    try {
      final formData = FormData.fromMap({
        'image': await MultipartFile.fromFile(selfie.path, filename: 'selfie.jpg'),
        'gps_lat': position.latitude,
        'gps_lng': position.longitude,
        'device_id': 'flutter-device',
      });

      await api.dio.post('/api/v1/attendance/clock-in', data: formData);

      if (mounted) {
        setState(() {
          _statusText = 'Hadir \u00b7 ${DateFormat('HH:mm').format(DateTime.now())}';
          _statusColor = Colors.green;
          _loading = false;
        });
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Presensi berhasil. $_statusText'), backgroundColor: Colors.green));
      }
    } catch (e) {
      if (mounted) {
        setState(() => _loading = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: const Text('Gagal upload presensi'), backgroundColor: Colors.red),
        );
      }
    }
  }

  Future<void> _clockOut() async {
    setState(() => _loading = true);
    final api = context.read<ApiClient>();
    try {
      await api.dio.post('/api/v1/attendance/clock-out');
      if (mounted) {
        setState(() { _statusText = 'Belum Presensi'; _statusColor = Colors.grey; _session = null; _loading = false; });
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Clock-out berhasil'), backgroundColor: Colors.green));
      }
    } catch (e) {
      if (mounted) {
        setState(() => _loading = false);
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Gagal clock-out'), backgroundColor: Colors.red));
      }
    }
  }

  void _showGpsModeSheet() {
    showModalBottomSheet(
      context: context,
      builder: (ctx) => SafeArea(
        child: Column(mainAxisSize: MainAxisSize.min, children: [
          const Padding(padding: EdgeInsets.all(16), child: Text('Pilih Mode GPS', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold))),
          ListTile(
            leading: const Icon(Icons.gps_off),
            title: const Text('Mode Bebas'),
            subtitle: const Text('GPS direkam tanpa verifikasi klien'),
            selected: _gpsMode == 'Bebas',
            onTap: () { setState(() => _gpsMode = 'Bebas'); Navigator.pop(ctx); },
          ),
          ListTile(
            leading: const Icon(Icons.gps_fixed),
            title: const Text('Mode Spesifik'),
            subtitle: const Text('GPS diverifikasi dalam radius klien'),
            selected: _gpsMode == 'Spesifik',
            onTap: () { setState(() => _gpsMode = 'Spesifik'); Navigator.pop(ctx); },
          ),
          if (_gpsMode == 'Spesifik') ...[
            const Divider(),
            const Padding(padding: EdgeInsets.symmetric(horizontal: 16), child: Text('Pilih Klien', style: TextStyle(fontSize: 16))),
            ..._clients.map((c) => ListTile(
              title: Text(c['name'] ?? ''),
              subtitle: Text('${c['address'] ?? ''}  |  ${c['radius_meters']}m'),
              selected: _selectedClient?['id'] == c['id'],
              onTap: () { setState(() => _selectedClient = c); Navigator.pop(ctx); },
            )),
          ],
          const SizedBox(height: 16),
        ]),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isClockedIn = _session != null && (_session!['status'] == 'hadir' || _session!['status'] == 'lembur');
    final theme = Theme.of(context);

    return Scaffold(
      appBar: AppBar(title: const Text('Absenin'), centerTitle: true, actions: [
        IconButton(icon: const Icon(Icons.refresh), onPressed: _init),
      ]),
      body: Column(children: [
        const SizedBox(height: 32),
        Container(
          margin: const EdgeInsets.symmetric(horizontal: 24),
          padding: const EdgeInsets.all(24),
          decoration: BoxDecoration(
            color: (_statusColor ?? Colors.grey).withValues(alpha: 0.1),
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: (_statusColor ?? Colors.grey).withValues(alpha: 0.3)),
          ),
          child: Column(children: [
            Icon(isClockedIn ? Icons.access_time : Icons.access_time_outlined, size: 48, color: _statusColor),
            const SizedBox(height: 8),
            Text(_statusText ?? 'Memuat...', style: theme.textTheme.headlineSmall?.copyWith(fontWeight: FontWeight.bold)),
            const SizedBox(height: 4),
            Text(DateFormat('EEEE, dd MMMM yyyy', 'id_ID').format(DateTime.now()),
              style: theme.textTheme.bodySmall?.copyWith(color: theme.colorScheme.onSurfaceVariant)),
          ]),
        ),
        const SizedBox(height: 16),
        if (!isClockedIn) ...[
          Row(mainAxisAlignment: MainAxisAlignment.center, children: [
            Icon(_gpsStatus == 'accurate' ? Icons.gps_fixed : _gpsStatus == 'searching' ? Icons.gps_not_fixed : Icons.gps_off,
              color: _gpsStatus == 'accurate' ? Colors.green : _gpsStatus == 'searching' ? Colors.orange : Colors.red, size: 16),
            const SizedBox(width: 8),
            Text('GPS: $_gpsStatus', style: TextStyle(color: _gpsStatus == 'accurate' ? Colors.green : Colors.orange, fontSize: 12)),
            const SizedBox(width: 16),
            Text('Mode: $_gpsMode', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w500)),
            if (_selectedClient != null) Text(' \u00b7 ${_selectedClient!['name']}', style: const TextStyle(fontSize: 12)),
          ]),
          TextButton(onPressed: _showGpsModeSheet, child: const Text('Ubah mode')),
        ],
        const Spacer(),
        if (isClockedIn) Text('Sesi aktif sejak ${_session!['clock_in']?.toString().substring(11, 16) ?? '-'}',
          style: theme.textTheme.bodySmall?.copyWith(color: theme.colorScheme.onSurfaceVariant)),
        const SizedBox(height: 24),
        FloatingActionButton.extended(
          onPressed: _loading ? null : (isClockedIn ? _clockOut : _clockIn),
          icon: _loading ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
            : Icon(isClockedIn ? Icons.logout : Icons.camera_alt),
          label: Text(isClockedIn ? 'Clock Out' : 'Clock In'),
          backgroundColor: isClockedIn ? Colors.red.shade600 : theme.colorScheme.primary,
          foregroundColor: Colors.white,
        ),
        const SizedBox(height: 32),
      ]),
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
