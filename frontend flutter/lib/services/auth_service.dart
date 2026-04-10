import 'dart:convert';
import 'package:dio/dio.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'api_service.dart';

class AuthService {
  
  Future<Map<String, dynamic>?> login(String nrp, String password) async {
    try {
      final response = await apiService.instance.post('auth/login', data: {
        'nrp': nrp,
        'password': password,
      });

      if (response.statusCode == 200) {
        final data = response.data;
        final token = data['token'];
        final user = data['user'];

        // Save session data
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('auth_token', token);
        await prefs.setString('user_data', jsonEncode(user));
        
        return user;
      }
      return null;
    } catch (e) {
      print("Login Error: $e");
      rethrow;
    }
  }

  Future<void> logout() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_token');
    await prefs.remove('user_data');
  }

  Future<bool> isLoggedIn() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.containsKey('auth_token');
  }

  Future<Map<String, dynamic>?> getUserData() async {
    final prefs = await SharedPreferences.getInstance();
    final data = prefs.getString('user_data');
    if (data != null) {
      return jsonDecode(data);
    }
    return null;
  }

  Future<void> updateUserData(Map<String, dynamic> newUser) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('user_data', jsonEncode(newUser));
  }

  Future<String?> uploadProfilePhoto(String filePath) async {
    try {
      final fileName = filePath.split('/').last;
      final formData = FormData.fromMap({
        "photo": await MultipartFile.fromFile(filePath, filename: fileName),
      });

      final response = await apiService.instance.post(
        'employees/update-photo',
        data: formData,
      );

      if (response.statusCode == 200) {
        return response.data['photo_url'];
      }
      return null;
    } catch (e) {
      print("Upload Photo Error: $e");
      return null;
    }
  }
}

// Global Singleton
final authService = AuthService();
