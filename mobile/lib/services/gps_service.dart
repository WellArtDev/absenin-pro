import 'package:geolocator/geolocator.dart';

class GpsService {
  Future<Position> getCurrentPosition() async {
    final serviceEnabled = await Geolocator.isLocationServiceEnabled();
    if (!serviceEnabled) throw Exception('GPS tidak aktif. Nyalakan GPS.');

    var permission = await Geolocator.checkPermission();
    if (permission == LocationPermission.denied) {
      permission = await Geolocator.requestPermission();
      if (permission == LocationPermission.denied) throw Exception('Izin GPS ditolak');
    }
    if (permission == LocationPermission.deniedForever) {
      throw Exception('Izin GPS ditolak permanen. Buka pengaturan.');
    }

    return Geolocator.getCurrentPosition(
      locationSettings: const LocationSettings(
        accuracy: LocationAccuracy.high,
        timeLimit: Duration(seconds: 10),
      ),
    );
  }

  Future<String> getGpsStatus() async {
    try {
      final serviceEnabled = await Geolocator.isLocationServiceEnabled();
      if (!serviceEnabled) return 'unavailable';
      final permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied || permission == LocationPermission.deniedForever) {
        return 'unavailable';
      }
      return 'accurate';
    } catch (_) {
      return 'searching';
    }
  }

  bool isWithinRadius(Position current, double targetLat, double targetLng, double radiusMeters) {
    final distance = Geolocator.distanceBetween(
      current.latitude, current.longitude, targetLat, targetLng,
    );
    return distance <= radiusMeters;
  }
}
