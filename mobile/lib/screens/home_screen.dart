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

class _HomeScreenState extends State<HomeScreen> with TickerProviderStateMixin {
  final _gpsService = GpsService();
  final _cameraService = CameraService();

  Map<String, dynamic>? _session;
  bool _loading = false;
  String _statusText = 'Memuat...';
  String _gpsStatus = 'accurate';
  String _gpsMode = 'Bebas';
  Map<String, dynamic>? _selectedClient;
  List<dynamic> _clients = [];
  String _elapsed = '';
  late AnimationController _pulseCtrl;

  @override
  void initState() {
    super.initState();
    _pulseCtrl = AnimationController(vsync: this, duration: const Duration(seconds: 1))..repeat(reverse: true);
    _init();
  }

  @override
  void dispose() {
    _pulseCtrl.dispose();
    super.dispose();
  }

  Future<void> _init() async {
    _updateGpsStatus();
    _loadClients();
    _checkStatus();
  }

  Future<void> _updateGpsStatus() async {
    final s = await _gpsService.getGpsStatus();
    if (mounted) setState(() => _gpsStatus = s);
  }

  Future<void> _loadClients() async {
    try {
      final r = await context.read<ApiClient>().dio.get('/api/v1/clients');
      if (r.data['success'] == true && mounted) setState(() => _clients = r.data['data'] ?? []);
    } catch (_) {}
  }

  Future<void> _checkStatus() async {
    try {
      final r = await context.read<ApiClient>().dio.get('/api/v1/attendance/status/today');
      if (r.data['success'] == true && mounted) {
        final s = r.data['data'];
        setState(() {
          _session = s;
          _statusText = 'Hadir';
          _elapsed = _calcElapsed(s['clock_in']);
        });
        _pulseCtrl.stop();
      }
    } catch (_) {
      if (mounted) setState(() { _statusText = 'Belum Presensi'; _session = null; });
    }
  }

  String _calcElapsed(String? clockIn) {
    if (clockIn == null) return '';
    final start = DateTime.tryParse(clockIn);
    if (start == null) return '';
    final diff = DateTime.now().difference(start);
    return '${diff.inHours}j ${diff.inMinutes % 60}m';
  }

  Future<void> _clockIn() async {
    setState(() => _loading = true);
    try {
      final pos = await _gpsService.getCurrentPosition();
      if (_gpsMode == 'Spesifik' && _selectedClient != null) {
        final lat = double.tryParse(_selectedClient!['gps_lat']?.toString() ?? '') ?? 0;
        final lng = double.tryParse(_selectedClient!['gps_lng']?.toString() ?? '') ?? 0;
        final radius = double.tryParse(_selectedClient!['radius_meters']?.toString() ?? '10') ?? 10;
        if (!_gpsService.isWithinRadius(pos, lat, lng, radius)) {
          _showGpsWarning();
          setState(() => _loading = false);
          return;
        }
      }
      final selfie = await _cameraService.takeSelfie();
      if (selfie == null) { setState(() => _loading = false); return; }
      await _uploadClockIn(selfie, pos);
    } catch (e) {
      if (mounted) {
        setState(() => _loading = false);
        _showSnackBar('Gagal presensi', Colors.red);
      }
    }
  }

  void _showGpsWarning() {
    showDialog(context: context, builder: (ctx) => AlertDialog(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
      title: const Text('Di Luar Radius'),
      content: Text('Anda berada di luar radius ${_selectedClient!['name']} (${_selectedClient!['radius_meters']}m).'),
      actions: [
        TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Batal')),
        FilledButton(onPressed: () { Navigator.pop(ctx); _showSnackBar('Laporan dikirim ke HR', Colors.orange); }, child: const Text('Laporkan')),
      ],
    ));
  }

  Future<void> _uploadClockIn(File selfie, dynamic pos) async {
    final api = context.read<ApiClient>();
    try {
      final fd = FormData.fromMap({
        'image': await MultipartFile.fromFile(selfie.path, filename: 'selfie.jpg'),
        'gps_lat': pos.latitude, 'gps_lng': pos.longitude, 'device_id': 'flutter-device',
      });
      await api.dio.post('/api/v1/attendance/clock-in', data: fd);
      if (mounted) {
        setState(() { _statusText = 'Hadir'; _loading = false; _elapsed = '0j 0m'; });
        _showSnackBar('Presensi berhasil!', Colors.green);
      }
    } catch (_) {
      if (mounted) { setState(() => _loading = false); _showSnackBar('Gagal upload', Colors.red); }
    }
  }

  Future<void> _clockOut() async {
    setState(() => _loading = true);
    try {
      await context.read<ApiClient>().dio.post('/api/v1/attendance/clock-out');
      if (mounted) {
        setState(() { _statusText = 'Belum Presensi'; _session = null; _elapsed = ''; _loading = false; });
        _pulseCtrl.repeat(reverse: true);
        _showSnackBar('Clock-out berhasil', Colors.green);
      }
    } catch (_) {
      if (mounted) { setState(() => _loading = false); _showSnackBar('Gagal clock-out', Colors.red); }
    }
  }

  void _showSnackBar(String msg, Color c) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(msg), backgroundColor: c, behavior: SnackBarBehavior.floating,
      margin: const EdgeInsets.all(16), shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      duration: const Duration(seconds: 2),
    ));
  }

  void _showGpsModeSheet() {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (_) => SafeArea(
        child: Padding(padding: const EdgeInsets.all(20), child: Column(mainAxisSize: MainAxisSize.min, children: [
          Container(width: 40, height: 4, decoration: BoxDecoration(color: Colors.grey.shade300, borderRadius: BorderRadius.circular(2))),
          const SizedBox(height: 20),
          const Text('Pilih Mode GPS', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
          const SizedBox(height: 16),
          ListTile(
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
            tileColor: _gpsMode == 'Bebas' ? Theme.of(context).colorScheme.primaryContainer : null,
            leading: CircleAvatar(backgroundColor: Colors.blue.shade50, child: const Icon(Icons.gps_off, color: Colors.blue)),
            title: const Text('Mode Bebas', style: TextStyle(fontWeight: FontWeight.w600)),
            subtitle: const Text('GPS direkam tanpa verifikasi klien'),
            onTap: () { setState(() => _gpsMode = 'Bebas'); Navigator.pop(context); },
          ),
          const SizedBox(height: 8),
          ListTile(
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
            tileColor: _gpsMode == 'Spesifik' ? Theme.of(context).colorScheme.primaryContainer : null,
            leading: CircleAvatar(backgroundColor: Colors.green.shade50, child: const Icon(Icons.gps_fixed, color: Colors.green)),
            title: const Text('Mode Spesifik', style: TextStyle(fontWeight: FontWeight.w600)),
            subtitle: const Text('GPS diverifikasi dalam radius klien'),
            onTap: () { setState(() => _gpsMode = 'Spesifik'); Navigator.pop(context); },
          ),
          if (_gpsMode == 'Spesifik') ...[
            const SizedBox(height: 16),
            const Divider(),
            Text('Pilih Klien', style: TextStyle(fontSize: 15, fontWeight: FontWeight.w600, color: isDark ? Colors.white70 : Colors.grey.shade700)),
            const SizedBox(height: 8),
            if (_clients.isEmpty) const Padding(padding: EdgeInsets.all(16), child: Text('Belum ada klien')),
            ..._clients.map((c) => ListTile(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              tileColor: _selectedClient?['id'] == c['id'] ? Colors.green.shade50 : null,
              leading: CircleAvatar(backgroundColor: Colors.green.shade100, child: Text('${c['name']?[0] ?? '?'}', style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.green))),
              title: Text(c['name'] ?? '', style: const TextStyle(fontWeight: FontWeight.w500)),
              subtitle: Text('${c['address'] ?? ''}  ·  ${c['radius_meters']}m', style: const TextStyle(fontSize: 12)),
              onTap: () { setState(() => _selectedClient = c); Navigator.pop(context); },
            )),
          ],
          const SizedBox(height: 16),
        ])),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isClockedIn = _session != null;
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;

    return Scaffold(
      backgroundColor: isDark ? const Color(0xFF0f0f23) : const Color(0xFFF8FAFC),
      extendBodyBehindAppBar: true,
      appBar: AppBar(
        title: const Text('Absenin', style: TextStyle(fontWeight: FontWeight.bold)),
        centerTitle: true,
        backgroundColor: Colors.transparent,
        elevation: 0,
        actions: [IconButton(icon: const Icon(Icons.refresh_rounded), onPressed: _init)],
      ),
      body: Column(children: [
        const SizedBox(height: 20),
        Container(
          margin: const EdgeInsets.symmetric(horizontal: 20),
          padding: const EdgeInsets.all(28),
          decoration: BoxDecoration(
            gradient: LinearGradient(colors: isClockedIn ? [const Color(0xFF059669), const Color(0xFF047857)] : [const Color(0xFF64748B), const Color(0xFF475569)]),
            borderRadius: BorderRadius.circular(24),
            boxShadow: [BoxShadow(color: (isClockedIn ? Colors.green : Colors.grey).withValues(alpha: 0.3), blurRadius: 20, offset: const Offset(0, 8))],
          ),
          child: Column(children: [
            AnimatedBuilder(
              animation: _pulseCtrl,
              builder: (_, child) => Container(
                width: 64, height: 64,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: Colors.white.withValues(alpha: 0.2 + _pulseCtrl.value * 0.2),
                ),
                child: Icon(isClockedIn ? Icons.access_time_filled : _loading ? Icons.hourglass_top : Icons.fingerprint, size: 32, color: Colors.white),
              ),
            ),
            const SizedBox(height: 16),
            Text(_statusText, style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: Colors.white)),
            if (isClockedIn) ...[
              const SizedBox(height: 4),
              Text(_elapsed, style: TextStyle(fontSize: 14, color: Colors.white.withValues(alpha: 0.9))),
              const SizedBox(height: 8),
              Container(padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4), decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.2), borderRadius: BorderRadius.circular(20)), child: Text('Sesi aktif', style: TextStyle(fontSize: 11, color: Colors.white.withValues(alpha: 0.9)))),
            ],
          ]),
        ),
        if (!isClockedIn) ...[
          const SizedBox(height: 12),
          GestureDetector(
            onTap: _showGpsModeSheet,
            child: Container(
              margin: const EdgeInsets.symmetric(horizontal: 20),
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
              decoration: BoxDecoration(color: isDark ? const Color(0xFF1a1a2e) : Colors.white, borderRadius: BorderRadius.circular(14), border: Border.all(color: Colors.grey.shade200)),
              child: Row(mainAxisSize: MainAxisSize.min, children: [
                Icon(_gpsStatus == 'accurate' ? Icons.gps_fixed : Icons.gps_off, size: 16, color: _gpsStatus == 'accurate' ? Colors.green : Colors.red),
                const SizedBox(width: 8),
                Text('Mode $_gpsMode', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w500)),
                if (_selectedClient != null) Text(' \u00b7 ${_selectedClient!['name']}', style: TextStyle(fontSize: 13, color: Colors.grey.shade500)),
                const Spacer(),
                const Icon(Icons.chevron_right, size: 18),
              ]),
            ),
          ),
        ],
        const Spacer(),
        Padding(
          padding: const EdgeInsets.only(bottom: 24),
          child: Column(children: [
            if (isClockedIn)
              Padding(
                padding: const EdgeInsets.only(bottom: 12),
                child: Text(DateFormat('dd MMMM yyyy', 'id_ID').format(DateTime.now()), style: TextStyle(fontSize: 13, color: Colors.grey.shade500)),
              ),
            SizedBox(
              width: 64, height: 64,
              child: FloatingActionButton(
                onPressed: _loading ? null : (isClockedIn ? _clockOut : _clockIn),
                backgroundColor: isClockedIn ? Colors.red.shade500 : theme.colorScheme.primary,
                elevation: 8,
                shape: const CircleBorder(),
                child: _loading ? const SizedBox(width: 24, height: 24, child: CircularProgressIndicator(strokeWidth: 2.5, color: Colors.white)) : Icon(isClockedIn ? Icons.logout_rounded : Icons.camera_alt_rounded, color: Colors.white, size: 28),
              ),
            ),
            const SizedBox(height: 8),
            Text(isClockedIn ? 'Clock Out' : 'Clock In', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: isClockedIn ? Colors.red.shade400 : theme.colorScheme.primary)),
          ]),
        ),
        const SizedBox(height: 16),
      ]),
      bottomNavigationBar: _buildNav(context, 0),
    );
  }

  NavigationBar _buildNav(BuildContext context, int selected) {
    final theme = Theme.of(context);
    return NavigationBar(
      selectedIndex: selected,
      onDestinationSelected: (i) {
        switch (i) { case 0: break; case 1: context.go('/history'); case 2: context.go('/leave'); case 3: context.go('/profile'); }
      },
      indicatorColor: theme.colorScheme.primary.withValues(alpha: 0.15),
      destinations: const [
        NavigationDestination(icon: Icon(Icons.home_outlined), selectedIcon: Icon(Icons.home_rounded), label: 'Home'),
        NavigationDestination(icon: Icon(Icons.history_outlined), selectedIcon: Icon(Icons.history_rounded), label: 'Riwayat'),
        NavigationDestination(icon: Icon(Icons.edit_note_outlined), selectedIcon: Icon(Icons.edit_note_rounded), label: 'Pengajuan'),
        NavigationDestination(icon: Icon(Icons.person_outlined), selectedIcon: Icon(Icons.person_rounded), label: 'Profil'),
      ],
    );
  }
}
