import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import '../services/auth_service.dart';
import '../services/attendance_service.dart';
import 'package:intl/intl.dart';
import 'shift_detail_screen.dart';
import 'shift_history_screen.dart';

class HomeScreen extends StatefulWidget {
  final Function(int) onTabChange;
  const HomeScreen({super.key, required this.onTabChange});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  Map<String, dynamic>? _userData;
  Map<String, dynamic>? _todayStatus;
  final String _currentDate = DateFormat("EEEE, d MMMM yyyy", "id_ID").format(DateTime.now());

  @override
  void initState() {
    super.initState();
    _loadUser();
  }

  Future<void> _loadUser() async {
    final data = await authService.getUserData();
    final today = await attendanceService.getTodayStatus();
    
    if (mounted) {
      setState(() {
        _userData = data;
        _todayStatus = today;
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
                    MaterialPageRoute(builder: (context) => const ShiftHistoryScreen()),
                  );
                },
                child: Text(
                  "LIHAT SEMUA",
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.bold,
                    color: const Color(0xFF007AFF),
                    letterSpacing: 0.5,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),

          // 6. HISTORY LIST
          _buildHistoryItem(
            title: "Check - In Gagal",
            date: "Kamis, 26 Maret 2026 • 10:15 WITA",
            statusLabel: "GAGAL",
            isSuccess: false,
            iconWidget: const FaIcon(FontAwesomeIcons.triangleExclamation, color: Colors.white, size: 20),
          ),
          const SizedBox(height: 16),
          _buildHistoryItem(
            title: "Check - Out Kemarin",
            date: "Kamis, 26 Maret 2026 • 17:15 WITA",
            statusLabel: "SUKSES",
            isSuccess: true,
            iconWidget: const FaIcon(FontAwesomeIcons.clockRotateLeft, color: Colors.white, size: 20),
          ),

          const SizedBox(height: 120), // Bottom Breathing Space (Aman dari Blurred Footer)
        ],
      ),
    );
  }

  // Widget Builder Cepat untuk Item Riwayat Kehadiran
  Widget _buildHistoryItem({
    required String title,
    required String date,
    required String statusLabel,
    required bool isSuccess,
    required Widget iconWidget,
  }) {
    final Color mainColor = isSuccess ? const Color(0xFF34C759) : const Color(0xFFFF3B30);

    return Row(
      children: [
        // Prefix Icon Box
        Container(
          width: 48,
          height: 48,
          decoration: BoxDecoration(
            color: mainColor,
            borderRadius: BorderRadius.circular(12),
          ),
          child: Center(
            child: iconWidget,
          ),
        ),
        const SizedBox(width: 16),
        // Texts
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                title,
                style: const TextStyle(
                  fontWeight: FontWeight.bold,
                  fontSize: 14,
                  letterSpacing: -0.3,
                ),
              ),
              const SizedBox(height: 4),
              Text(
                date,
                style: TextStyle(
                  fontWeight: FontWeight.w500,
                  fontSize: 10,
                  color: Colors.grey.shade500,
                ),
              ),
            ],
          ),
        ),
        // Trailing Marker
        Row(
          children: [
            Text(
              statusLabel,
              style: TextStyle(
                color: mainColor,
                fontWeight: FontWeight.bold,
                fontSize: 10,
                letterSpacing: 0.5,
              ),
            ),
            const SizedBox(width: 4),
            FaIcon(
              FontAwesomeIcons.angleRight,
              color: mainColor,
              size: 14,
            ),
          ],
        )
      ],
    );
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
