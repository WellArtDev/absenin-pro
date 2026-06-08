import 'package:shared_preferences/shared_preferences.dart';

class AuthStorage {
  static const _accessKey = 'access_token';
  static const _refreshKey = 'refresh_token';
  static const _expiresKey = 'expires_in';
  static const _userIdKey = 'user_id';

  static Future<void> saveTokens({
    required String accessToken,
    required String refreshToken,
    required int expiresIn,
    required String userId,
  }) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_accessKey, accessToken);
    await prefs.setString(_refreshKey, refreshToken);
    await prefs.setInt(_expiresKey, DateTime.now().millisecondsSinceEpoch + expiresIn * 1000);
    await prefs.setString(_userIdKey, userId);
  }

  static Future<String?> getAccessToken() async {
    final prefs = await SharedPreferences.getInstance();
    final expiry = prefs.getInt(_expiresKey) ?? 0;
    if (DateTime.now().millisecondsSinceEpoch > expiry) return null;
    return prefs.getString(_accessKey);
  }

  static Future<String?> getRefreshToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(_refreshKey);
  }

  static Future<void> updateAccessToken(String token, int expiresIn) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_accessKey, token);
    await prefs.setInt(_expiresKey, DateTime.now().millisecondsSinceEpoch + expiresIn * 1000);
  }

  static Future<void> clear() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_accessKey);
    await prefs.remove(_refreshKey);
    await prefs.remove(_expiresKey);
    await prefs.remove(_userIdKey);
  }

  static Future<bool> isLoggedIn() async {
    final token = await getAccessToken();
    return token != null;
  }
}
