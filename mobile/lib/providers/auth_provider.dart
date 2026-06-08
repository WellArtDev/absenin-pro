import 'package:flutter/foundation.dart';
import 'package:dio/dio.dart';
import '../services/api_client.dart';
import '../services/auth_storage.dart';
import 'package:device_info_plus/device_info_plus.dart';

class AuthProvider extends ChangeNotifier {
  final ApiClient api;
  bool _isLoading = false;
  String? _error;

  bool get isLoading => _isLoading;
  String? get error => _error;

  AuthProvider(this.api);

  Future<bool> login(String email, String password) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final response = await api.dio.post('/api/v1/auth/login', data: {
        'email': email,
        'password': password,
      });

      final data = response.data['data'];
      await AuthStorage.saveTokens(
        accessToken: data['access_token'],
        refreshToken: data['refresh_token'],
        expiresIn: data['expires_in'],
        userId: data['user']['id'],
      );

      _isLoading = false;
      notifyListeners();

      _registerDevice();
      return true;
    } catch (e) {
      _error = _parseError(e);
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<void> logout() async {
    await AuthStorage.clear();
    notifyListeners();
  }

  String _parseError(dynamic e) {
    if (e is DioException && e.response?.data != null) {
      return e.response?.data['message'] ?? 'Login gagal';
    }
    return 'Gagal terhubung ke server';
  }

  Future<void> _registerDevice() async {
    try {
      final deviceInfo = DeviceInfoPlugin();
      String deviceId;
      String platform;

      if (defaultTargetPlatform == TargetPlatform.android) {
        final android = await deviceInfo.androidInfo;
        deviceId = android.id;
        platform = 'android';
      } else {
        final ios = await deviceInfo.iosInfo;
        deviceId = ios.identifierForVendor ?? 'unknown';
        platform = 'ios';
      }

      await api.dio.post('/api/v1/devices/register', data: {
        'device_id': deviceId,
        'platform': platform,
      });
    } catch (_) {}
  }
}
