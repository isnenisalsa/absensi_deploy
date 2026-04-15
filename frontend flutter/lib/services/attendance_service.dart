import 'dart:io';
import 'package:dio/dio.dart';
import 'api_service.dart';

class AttendanceService {
  Future<Map<String, dynamic>?> getTodayStatus() async {
    try {
      final response = await apiService.instance.get('attendance/today-status');
      if (response.statusCode == 200) {
        return response.data;
      }
      return null;
    } catch (e) {
      print("Error fetching today status: $e");
      return null;
    }
  }

  /// Mengambil riwayat absensi milik sendiri (employee) atau semua (admin)
  Future<List<dynamic>> getAttendanceHistory() async {
    try {
      final response = await apiService.instance.get('attendance/history');
      if (response.statusCode == 200) {
        return response.data;
      }
      return [];
    } catch (e) {
      print("Error fetching attendance history: $e");
      return [];
    }
  }

  Future<Map<String, dynamic>?> submitFtwReport(Map<String, dynamic> data) async {
    try {
      final response = await apiService.instance.post('attendance/ftw', data: data);
      if (response.statusCode == 201) return response.data;
      return null;
    } catch (e) {
      print("Error submitting FTW: $e");
      return null;
    }
  }

  Future<Map<String, dynamic>?> checkIn(Map<String, dynamic> data) async {
    try {
      final response = await apiService.instance.post('attendance/check-in', data: data);
      if (response.statusCode == 201) return response.data;
      return null;
    } catch (e) {
      print("Error check-in: $e");
      return null;
    }
  }

  Future<Map<String, dynamic>?> checkOut(Map<String, dynamic> data) async {
    try {
      final response = await apiService.instance.post('attendance/check-out', data: data);
      if (response.statusCode == 201) return response.data;
      return null;
    } catch (e) {
      print("Error check-out: $e");
      return null;
    }
  }

  /// Upload foto bukti absensi dari kamera ke backend.
  /// Mengembalikan [filename] yang disimpan server, atau null jika gagal.
  Future<String?> uploadAttendancePhoto(File photoFile) async {
    try {
      final fileName = photoFile.path.split('/').last;
      final formData = FormData.fromMap({
        'photo': await MultipartFile.fromFile(
          photoFile.path,
          filename: fileName,
        ),
      });

      final response = await apiService.instance.post(
        'attendance/upload-photo',
        data: formData,
        options: Options(
          headers: {'Content-Type': 'multipart/form-data'},
        ),
      );

      if (response.statusCode == 200) {
        final filename = response.data['filename'];
        print("✅ [Photo Upload] Success: $filename");
        return filename as String?;
      }
      return null;
    } catch (e) {
      print("❌ [Photo Upload] Error: $e");
      return null;
    }
  }

  Future<List<dynamic>> getLocations() async {
    try {
      final response = await apiService.instance.get('master/locations');
      if (response.statusCode == 200) return response.data;
      return [];
    } catch (e) {
      print("Error fetching locations: $e");
      return [];
    }
  }

  Future<List<dynamic>> getShifts() async {
    try {
      final response = await apiService.instance.get('master/shifts');
      if (response.statusCode == 200) return response.data;
      return [];
    } catch (e) {
      print("Error fetching shifts: $e");
      return [];
    }
  }
}

final attendanceService = AttendanceService();

