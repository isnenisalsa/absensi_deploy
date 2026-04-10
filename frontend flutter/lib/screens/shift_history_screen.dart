import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import '../components/glassmorphic_layout.dart';
import 'shift_detail_screen.dart';

class ShiftHistoryScreen extends StatelessWidget {
  const ShiftHistoryScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF4F5F7),
      body: GlassmorphicLayout(
        title: "ROSTER KERJA",
        showBackButton: true,
        onBackPressed: () => Navigator.pop(context),
        child: SingleChildScrollView(
          padding: const EdgeInsets.only(top: 130, bottom: 40, left: 20, right: 20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // 1. MONTH HEADER
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                   const Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        "April 2026",
                        style: TextStyle(
                          fontSize: 20,
                          fontWeight: FontWeight.w900,
                          letterSpacing: -0.5,
                        ),
                      ),
                      Text(
                        "Showing roster for this month",
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w600,
                          color: Colors.grey,
                        ),
                      ),
                    ],
                  ),
                  Container(
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: const FaIcon(FontAwesomeIcons.calendarDay, size: 18, color: Color(0xFF007AFF)),
                  )
                ],
              ),
              
              const SizedBox(height: 24),
              
              // 2. ROSTER LIST
              _buildRosterItem(context, "Senin, 07 April", "Shift 1", "07:15 - 17:15", true),
              _buildRosterItem(context, "Selasa, 08 April", "Shift 1", "07:15 - 17:15", false),
              _buildRosterItem(context, "Rabu, 09 April", "Day Off", "-", false, isOff: true),
              _buildRosterItem(context, "Kamis, 10 April", "Shift 2", "19:15 - 05:15", false),
              _buildRosterItem(context, "Jumat, 11 April", "Shift 2", "19:15 - 05:15", false),
              _buildRosterItem(context, "Sabtu, 12 April", "Day Off", "-", false, isOff: true),
              
              const SizedBox(height: 40),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildRosterItem(BuildContext context, String date, String shift, String hours, bool isCurrent, {bool isOff = false}) {
    return GestureDetector(
      onTap: isOff ? null : () {
        Navigator.push(
          context,
          MaterialPageRoute(builder: (context) => const ShiftDetailScreen()),
        );
      },
      child: Container(
        margin: const EdgeInsets.only(bottom: 16),
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: isCurrent ? Colors.white : Colors.white.withValues(alpha: 0.6),
          borderRadius: BorderRadius.circular(16),
          border: isCurrent ? Border.all(color: const Color(0xFF007AFF), width: 1.5) : Border.all(color: Colors.white),
          boxShadow: [
            if (isCurrent)
              BoxShadow(
                color: const Color(0xFF007AFF).withValues(alpha: 0.1),
                blurRadius: 10,
                offset: const Offset(0, 4),
              )
          ],
        ),
        child: Row(
          children: [
            // Date Circle
            Container(
              width: 44,
              height: 44,
              decoration: BoxDecoration(
                color: isOff ? Colors.grey.shade100 : (isCurrent ? const Color(0xFF007AFF) : const Color(0xFF007AFF).withValues(alpha: 0.1)),
                shape: BoxShape.circle,
              ),
              child: Center(
                child: Text(
                  date.split(" ").last,
                  style: TextStyle(
                    color: isOff ? Colors.grey : (isCurrent ? Colors.white : const Color(0xFF007AFF)),
                    fontWeight: FontWeight.bold,
                    fontSize: 16,
                  ),
                ),
              ),
            ),
            const SizedBox(width: 16),
            // Info
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    date,
                    style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w700,
                      color: isOff ? Colors.grey.shade400 : Colors.black87,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    shift,
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w800,
                      color: isOff ? Colors.grey.shade400 : Colors.grey.shade600,
                      letterSpacing: 0.5,
                    ),
                  ),
                ],
              ),
            ),
            // Status/Hours
            Column(
              crossAxisAlignment: CrossAxisAlignment.end,
              children: [
                if (isCurrent)
                  Container(
                    margin: const EdgeInsets.only(bottom: 4),
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                    decoration: BoxDecoration(
                      color: const Color(0xFF007AFF),
                      borderRadius: BorderRadius.circular(4),
                    ),
                    child: const Text(
                      "TODAY",
                      style: TextStyle(color: Colors.white, fontSize: 8, fontWeight: FontWeight.bold),
                    ),
                  ),
                Text(
                  hours,
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.bold,
                    color: isOff ? Colors.grey.shade300 : Colors.black54,
                  ),
                ),
              ],
            ),
            const SizedBox(width: 12),
            if (!isOff)
              const FaIcon(FontAwesomeIcons.angleRight, size: 14, color: Colors.grey),
          ],
        ),
      ),
    );
  }
}
