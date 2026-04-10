import 'package:local_auth/local_auth.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:flutter/services.dart';

class BiometricService {
  final LocalAuthentication _auth = LocalAuthentication();
  final FlutterSecureStorage _storage = const FlutterSecureStorage();

  // Check if biometrics are available on the device
  Future<bool> isBiometricAvailable() async {
    try {
      final bool canAuthenticateWithBiometrics = await _auth.canCheckBiometrics;
      final bool canAuthenticate = canAuthenticateWithBiometrics || await _auth.isDeviceSupported();
      return canAuthenticate;
    } catch (e) {
      return false;
    }
  }

  // Get list of available biometrics (FaceID, Fingerprint, etc)
  Future<List<BiometricType>> getAvailableBiometrics() async {
    try {
      return await _auth.getAvailableBiometrics();
    } catch (e) {
      return <BiometricType>[];
    }
  }

  // Perform authentication
  Future<bool> authenticate() async {
    try {
      final bool didAuthenticate = await _auth.authenticate(
        localizedReason: 'Silakan verifikasi identitas Anda untuk login otomatis',
        options: const AuthenticationOptions(
          stickyAuth: true,
          biometricOnly: true,
        ),
      );
      return didAuthenticate;
    } on PlatformException catch (e) {
      print("Biometric Auth Error: ${e.code}");
      return false;
    }
  }

  // Store credentials securely
  Future<void> storeCredentials(String nrp, String password) async {
    await _storage.write(key: 'biometric_nrp', value: nrp);
    await _storage.write(key: 'biometric_password', value: password);
    await _storage.write(key: 'biometric_enabled', value: 'true');
  }

  // Retrieve credentials
  Future<Map<String, String?>> getCredentials() async {
    String? nrp = await _storage.read(key: 'biometric_nrp');
    String? password = await _storage.read(key: 'biometric_password');
    return {
      'nrp': nrp,
      'password': password,
    };
  }

  // Check if biometric login is enabled by user
  Future<bool> isBiometricEnabled() async {
    String? enabled = await _storage.read(key: 'biometric_enabled');
    return enabled == 'true';
  }

  // Clear credentials
  Future<void> clearCredentials() async {
    await _storage.delete(key: 'biometric_nrp');
    await _storage.delete(key: 'biometric_password');
    await _storage.write(key: 'biometric_enabled', value: 'false');
  }
}

// Global Singleton
final biometricService = BiometricService();
