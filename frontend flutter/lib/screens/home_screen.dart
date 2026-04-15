import 'dart:io';
import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import '../services/auth_service.dart';
import '../services/attendance_service.dart';
import 'package:intl/intl.dart';
import 'shift_detail_screen.dart';
import 'attendance_history_screen.dart';

class HomeScreen extends StatefulWidget {
  final Function(int) onTabChange;
  const HomeScreen({super.key, required this.onTabChange});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  Map<String, dynamic>? _userData;
  Map<String, dynamic>? _todayStatus;
  List<dynamic> _recentHistory = [];
  final String _currentDate = DateFormat("EEEE, d MMMM yyyy", "id_ID").format(DateTime.now());

  @override
  void initState() {
    super.initState();
    _loadUser();
  }

  Future<void> _loadUser() async {
    final data = await authService.getUserData();
    final today = await attendanceService.getTodayStatus();
    final history = await attendanceService.getAttendanceHistory();
    
    if (mounted) {
      setState(() {
        _userData = data;
        _todayStatus = today;
        // Ambil 3 record terbaru untuk preview di home
        _recentHistory = history.take(3).toList();
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      physics: const BouncingScrollPhysics(),
      padding: const EdgeInsets.only(left: 20, right: 20, top: 16, bottom: 120),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          const SizedBox(height: 110), // Aman dari Fixed Glassmorphic Header

          // 1. GREETINGS & DATE ROW
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                "Selamat Datang,",
                style: TextStyle(
                  fontSize: 14,
                  color: Colors.grey.shade500,
                  fontWeight: FontWeight.w500,
                  letterSpacing: 0.5,
                ),
              ),
              const SizedBox(height: 2),
              Text(
                _userData?['employee_data']?['full_name'] ?? 'User',
                style: const TextStyle(
                  fontSize: 28,
                  fontWeight: FontWeight.w900,
                  letterSpacing: -1.0,
                  height: 1.1,
                ),
              ),
              const SizedBox(height: 12),
              Text(
                _currentDate,
                style: TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.bold,
                  color: Colors.grey.shade600,
                  letterSpacing: -0.2,
                ),
              ),
            ],
          ),

          const SizedBox(height: 24),

          // 2. ATTENDANCE STATUS CARD
          Container(
            padding: const EdgeInsets.all(22),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(20),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.03),
                  blurRadius: 15,
                  offset: const Offset(0, 8),
                )
              ],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      "ATTENDANCE STATUS",
                      style: TextStyle(
                        fontSize: 10,
                        fontWeight: FontWeight.w900,
                        color: Colors.grey.shade400,
                        letterSpacing: 2.0,
                      ),
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: _getStatusColor().withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(20),
                        border: Border.all(color: _getStatusColor().withValues(alpha: 0.3)),
                      ),
                      child: Text(
                        _getDisplayStatus().toUpperCase(),
                        style: TextStyle(
                          fontSize: 9,
                          fontWeight: FontWeight.w900,
                          color: _getStatusColor(),
                          letterSpacing: 0.5,
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 20),
                Row(
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text("CHECK - IN", style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Colors.grey.shade500)),
                          const SizedBox(height: 4),
                          Text(
                            _todayStatus?['attendance']?['check_in'] != null 
                                ? _formatShiftTime(_todayStatus!['attendance']['check_in']['time_wita'])
                                : "--:--",
                            style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w900, color: Colors.black87),
                          ),
                          if (_todayStatus?['attendance']?['check_in'] != null)
                            Padding(
                              padding: const EdgeInsets.only(top: 4.0),
                              child: Text(
                                _todayStatus!['attendance']['check_in']['work_location'] ?? "Syncing Location...",
                                style: const TextStyle(fontSize: 9, fontWeight: FontWeight.bold, color: Color(0xFF007AFF)),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            )
                        ],
                      ),
                    ),
                    Container(
                      height: 40,
                      width: 1,
                      color: Colors.grey.shade200,
                    ),
                    const SizedBox(width: 20),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text("CHECK - OUT", style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Colors.grey.shade500)),
                          const SizedBox(height: 4),
                          Text(
                            _todayStatus?['attendance']?['check_out'] != null 
                                ? _formatShiftTime(_todayStatus!['attendance']['check_out']['time_wita'])
                                : "--:--",
                            style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w900, color: Colors.black87),
                          ),
                          if (_todayStatus?['attendance']?['check_out'] != null)
                            Padding(
                              padding: const EdgeInsets.only(top: 4.0),
                              child: Text(
                                _todayStatus!['attendance']['check_out']['work_location'] ?? "Syncing Location...",
                                style: const TextStyle(fontSize: 9, fontWeight: FontWeight.bold, color: Color(0xFF007AFF)),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            )
                        ],
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),

          const SizedBox(height: 16),

          // 3. DETAIL SHIFT CARD
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.02),
                  blurRadius: 10,
                  offset: const Offset(0, 4),
                )
              ],
            ),
            child: Column(
              children: [
                // Header Detail Shift
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text(
                      "DETAIL SHIFT",
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w900,
                        letterSpacing: 1.5,
                      ),
                    ),
                    GestureDetector(
                      onTap: () {
                        Navigator.push(
                          context,
                          MaterialPageRoute(builder: (context) => const ShiftDetailScreen()),
                        );
                      },
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                        decoration: BoxDecoration(
                          color: const Color(0xFF007AFF),
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: const Text(
                          "Lihat",
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 10,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                    )
                  ],
                ),
                const SizedBox(height: 20),
                // Row 1: Shift Code
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text("Shift Code", style: TextStyle(color: Colors.grey.shade600, fontSize: 13)),
                    Text(
                      _todayStatus?['roster']?['shift']?['shift_code'] ?? "-",
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)
                    ),
                  ],
                ),
                const SizedBox(height: 16),
                // Row 2: Check-in
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text("Check-in", style: TextStyle(color: Colors.grey.shade600, fontSize: 13)),
                    Text(
                      _formatShiftTime(_todayStatus?['roster']?['shift']?['time_in_expected']),
                      style: const TextStyle(color: Color(0xFF34C759), fontWeight: FontWeight.bold, fontSize: 13)
                    ),
                  ],
                ),
                const SizedBox(height: 16),
                // Row 3: Check-out
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text("Check-out", style: TextStyle(color: Colors.grey.shade600, fontSize: 13)),
                    Text(
                      _formatShiftTime(_todayStatus?['roster']?['shift']?['time_out_expected']),
                      style: const TextStyle(color: Color(0xFFFF3B30), fontWeight: FontWeight.bold, fontSize: 13)
                    ),
                  ],
                ),
                const SizedBox(height: 16),
                // Row 4: Plan Location
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text("Plan Location (Roster)", style: TextStyle(color: Colors.grey.shade600, fontSize: 13)),
                    Text(
                      _todayStatus?['roster']?['work_location'] ?? "-",
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)
                    ),
                  ],
                ),
                const SizedBox(height: 16),
                // Row 5: Actual Location
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text("Actual Location", style: TextStyle(color: Colors.grey.shade600, fontSize: 13)),
                    Text(
                      _todayStatus?['roster']?['actual_location'] ?? "-",
                      style: TextStyle(
                        fontWeight: FontWeight.bold, 
                        fontSize: 13,
                        color: _getActualLocationColor(),
                      )
                    ),
                  ],
                ),
              ],
            ),
          ),

          const SizedBox(height: 32),

          // 4. GIANT "ABSEN" BUTTON
          Container(
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(20),
              boxShadow: [
                BoxShadow(
                  color: const Color(0xFF007AFF).withValues(alpha: 0.3),
                  blurRadius: 20,
                  offset: const Offset(0, 10),
                )
              ],
            ),
            child: ElevatedButton(
              onPressed: () => widget.onTabChange(1),
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF007AFF), // Apple Blue
                foregroundColor: Colors.white,
                elevation: 0,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(20),
                ),
                padding: const EdgeInsets.symmetric(vertical: 20),
              ),
              child: const Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  FaIcon(FontAwesomeIcons.fingerprint, size: 26),
                  SizedBox(width: 16),
                  Text(
                    "ABSEN SEKARANG",
                    style: TextStyle(
                      fontSize: 22, 
                      fontWeight: FontWeight.w900,
                      letterSpacing: 1.5,
                    ),
                  ),
                ],
              ),
            ),
          ),

          const SizedBox(height: 32),

          // 5. HISTORY HEADER
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                "RIWAYAT KEHADIRAN",
                style: TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.w900,
                ),
              ),
              GestureDetector(
                onTap: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(builder: (context) => const AttendanceHistoryScreen()),
                  );
                },
                child: const Text(
                  "LIHAT SEMUA",
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.bold,
                    color: Color(0xFF007AFF),
                    letterSpacing: 0.5,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),

          // 6. HISTORY LIST — Real data dari API
          if (_recentHistory.isEmpty)
            Container(
              padding: const EdgeInsets.symmetric(vertical: 24),
              alignment: Alignment.center,
              child: Text(
                "Belum ada riwayat absensi",
                style: TextStyle(color: Colors.grey.shade400, fontSize: 13),
              ),
            )
          else
            ..._recentHistory.map((item) {
              final isCheckIn = item['trans_type'] == 'Check_in';
              final hasPhoto = item['photo_evidence'] != null &&
                  item['photo_evidence'].toString().isNotEmpty &&
                  !item['photo_evidence'].toString().contains('dummy');
              final time = _formatHistoryTime(item['time_wita']);
              final date = _formatHistoryDate(item['attendance_date']);
              return Padding(
                padding: const EdgeInsets.only(bottom: 12),
                child: GestureDetector(
                  onTap: () => Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const AttendanceHistoryScreen()),
                  ),
                  child: _buildRealHistoryItem(
                    isCheckIn: isCheckIn,
                    time: time,
                    date: date,
                    location: item['work_location'] ?? '-',
                    hasPhoto: hasPhoto,
                    photoUrl: hasPhoto
                        ? _getPhotoUrl(item['photo_evidence'])
                        : null,
                  ),
                ),
              );
            }),

          const SizedBox(height: 120), // Bottom Breathing Space (Aman dari Blurred Footer)
        ],
      ),
    );
  }

  // ─── Helper: Real history card ───────────────────────────────────────────
  Widget _buildRealHistoryItem({
    required bool isCheckIn,
    required String time,
    required String date,
    required String location,
    required bool hasPhoto,
    String? photoUrl,
  }) {
    final color = isCheckIn ? const Color(0xFF007AFF) : const Color(0xFFFF9500);
    final label = isCheckIn ? "CHECK-IN" : "CHECK-OUT";
    final icon = isCheckIn ? Icons.login_rounded : Icons.logout_rounded;

    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.04),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Row(
        children: [
          // Foto thumbnail atau icon
          Container(
            width: 52,
            height: 52,
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(12),
              color: color.withValues(alpha: 0.1),
            ),
            clipBehavior: Clip.antiAlias,
            child: hasPhoto && photoUrl != null
                ? Image.network(
                    photoUrl,
                    fit: BoxFit.cover,
                    errorBuilder: (_, __, ___) => Icon(icon, color: color, size: 22),
                  )
                : Icon(icon, color: color, size: 22),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                      decoration: BoxDecoration(
                        color: color,
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Text(
                        label,
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 8,
                          fontWeight: FontWeight.w900,
                          letterSpacing: 0.8,
                        ),
                      ),
                    ),
                    if (hasPhoto) ...[  
                      const SizedBox(width: 6),
                      Icon(Icons.camera_alt_rounded, size: 11, color: Colors.green.shade500),
                    ],
                  ],
                ),
                const SizedBox(height: 4),
                Text(
                  time,
                  style: TextStyle(
                    fontWeight: FontWeight.w900,
                    fontSize: 18,
                    color: color,
                    letterSpacing: -0.5,
                  ),
                ),
                Text(
                  "$date · $location",
                  style: TextStyle(
                    fontSize: 10,
                    color: Colors.grey.shade500,
                    fontWeight: FontWeight.w500,
                  ),
                  overflow: TextOverflow.ellipsis,
                ),
              ],
            ),
          ),
          Icon(Icons.chevron_right_rounded, color: Colors.grey.shade300, size: 20),
        ],
      ),
    );
  }

  String _formatHistoryTime(String? raw) {
    if (raw == null) return '--:--';
    try {
      return DateFormat('HH:mm').format(DateTime.parse(raw));
    } catch (_) {
      if (raw.contains('T')) return raw.split('T')[1].substring(0, 5);
      return raw.substring(0, 5);
    }
  }

  String _formatHistoryDate(String? raw) {
    if (raw == null) return '-';
    try {
      return DateFormat('EEE, dd MMM', 'id').format(DateTime.parse(raw));
    } catch (_) {
      return raw.split('T')[0];
    }
  }

  String _getPhotoUrl(String? filename) {
    if (filename == null) return '';
    if (filename.startsWith('http')) return filename;
    final base = Platform.isAndroid ? 'http://10.0.2.2:3000' : 'http://127.0.0.1:3000';
    return '$base/uploads/attendance/$filename';
  }

  String _getDisplayStatus() {
    final status = _todayStatus?['status'];
    switch (status) {
      case 'checked_in':
        return "Sudah Check - In";
      case 'completed':
        return "Tugas Selesai";
      case 'waiting':
      default:
        return "Menunggu Check - In";
    }
  }

  Color _getStatusColor() {
    final status = _todayStatus?['status'];
    switch (status) {
      case 'checked_in':
        return const Color(0xFF34C759); // Green
      case 'completed':
        return const Color(0xFF8E8E93); // Grey
      case 'waiting':
      default:
        return const Color(0xFF007AFF); // Blue
    }
  }

  Color _getActualLocationColor() {
    final actual = _todayStatus?['roster']?['actual_location'];
    final plan = _todayStatus?['roster']?['work_location'];
    
    if (actual == null || actual == "-" || plan == null) {
      return Colors.grey.shade600;
    }
    
    if (actual == plan) {
      return const Color(0xFF007AFF); // Blue (match)
    } else {
      return const Color(0xFFFF3B30); // Red (mismatch)
    }
  }

  String _formatShiftTime(String? timeStr) {
    if (timeStr == null) return "-";
    try {
      // If it contains a 'Z', it might be UTC from Prisma/MySQL TIME column
      // we want to display the literal hour regardless of timezone shifts 
      // because we've adjusted the backend to store literal local hours.
      final dateTime = DateTime.parse(timeStr);
      // We format the raw hour/minute from the object
      return "${DateFormat("HH:mm").format(dateTime)} WITA";
    } catch (e) {
      if (timeStr.contains(":")) {
        final parts = timeStr.split(":");
        if (parts.length >= 2) {
          return "${parts[0].padLeft(2, '0')}:${parts[1].padLeft(2, '0')} WITA";
        }
      }
      return timeStr;
    }
  }
}
