import 'package:flutter/material.dart';
import 'package:dio/dio.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:io';
import 'package:frontend_flutter/constants.dart';
import 'package:flutter_jailbreak_detection/flutter_jailbreak_detection.dart';
import 'package:dio/io.dart';
import 'package:crypto/crypto.dart';
import '../screens/login_screen.dart';

class ApiService {
  late Dio _dio;

  // Use 10.0.2.2 for Android Emulator, 127.0.0.1 for iOS and others
  static final String _baseUrl = Platform.isAndroid
      ? 'http://10.0.2.2:3000/api/'
      : 'http://127.0.0.1:3000/api/';

  // SHA-256 Fingerprint dari sertifikat SSL server Anda
  // Cara mendapatkan: openssl x509 -noout -fingerprint -sha256 -in server.crt
  static const String _serverFingerprint = "YOUR_SHA256_FINGERPRINT_HERE";

  ApiService() {
    _dio = Dio(
      BaseOptions(
        baseUrl: _baseUrl,
        connectTimeout: const Duration(seconds: 15),
        receiveTimeout: const Duration(seconds: 15),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
      ),
    );

    // [SECURITY] Implementasi SSL Pinning (Hanya aktif jika HTTPS)
    if (_baseUrl.startsWith('https')) {
      _dio.httpClientAdapter = IOHttpClientAdapter(
        createHttpClient: () {
          final client = HttpClient();
          client.badCertificateCallback =
              (X509Certificate cert, String host, int port) {
                // Verifikasi fingerprint sertifikat
                final serverCertHash = sha256
                    .convert(cert.der)
                    .toString()
                    .toUpperCase()
                    .replaceAll(':', '');
                final pinnedHash = _serverFingerprint.toUpperCase().replaceAll(
                  ':',
                  '',
                );

                if (serverCertHash == pinnedHash) {
                  return true; // Cocok, izinkan koneksi
                }

                print("❌ SECURITY ALERT: SSL Certificate Mismatch!");
                print("Expected: $pinnedHash");
                print("Received: $serverCertHash");
                return false; // Tidak cocok, blokir (Burp Suite akan gagal di sini)
              };
          return client;
        },
      );
    }

    // Jalankan pengecekan keamanan perangkat
    _checkSecurity();

    // Add interceptor for Authorization Header
    _dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          final prefs = await SharedPreferences.getInstance();
          final token = prefs.getString('auth_token');
          if (token != null) {
            options.headers['Authorization'] = 'Bearer $token';
          }
          return handler.next(options);
        },
        onError: (DioException e, handler) async {
          // Log errors or handle 401 Unauthorized globally if needed
          final responseData = e.response?.data;
          print("❌ API ERROR [${e.response?.statusCode}]: $responseData");

          if (e.response?.statusCode == 401) {
            final prefs = await SharedPreferences.getInstance();

            // Targeted cleanup instead of prefs.clear() to preserve other settings (biometrics, etc)
            await prefs.remove('auth_token');
            await prefs.remove('user_data');

            print(
              "👤 Session cleared due to auth error. Redirecting to login...",
            );

            // Auto-redirect to Login page using the global navigatorKey
            if (navigatorKey.currentState != null) {
              navigatorKey.currentState!.pushAndRemoveUntil(
                MaterialPageRoute(builder: (context) => const LoginScreen()),
                (route) => false,
              );
            }
          }
          return handler.next(e);
        },
      ),
    );
  }

  Dio get instance => _dio;

  // [SECURITY] Cek apakah perangkat di-root atau jailbreak (Sering digunakan untuk PenTest)
  Future<void> _checkSecurity() async {
    try {
      bool isJailbroken = await FlutterJailbreakDetection.jailbroken;
      bool isDeveloperMode = await FlutterJailbreakDetection.developerMode;

      if (isJailbroken || isDeveloperMode) {
        print(
          "❌ SECURITY ALERT: Device is Jailbroken/Rooted or in Developer Mode!",
        );
      }
    } catch (e) {
      print("Security check failed: $e");
    }
  }
}

final apiService = ApiService();
