import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import '../components/glassmorphic_layout.dart';

class ShiftDetailScreen extends StatelessWidget {
  const ShiftDetailScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF4F5F7),
      body: GlassmorphicLayout(
        title: "DETAIL SHIFT",
        showBackButton: true,
        onBackPressed: () => Navigator.pop(context),
        child: SingleChildScrollView(
          padding: const EdgeInsets.only(top: 130, bottom: 40, left: 20, right: 20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // 1. MAIN SHIFT CARD
              Container(
                padding: const EdgeInsets.all(24),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(24),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.05),
                      blurRadius: 20,
                      offset: const Offset(0, 10),
                    )
                  ],
                ),
                child: Column(
                  children: [
                    const CircleAvatar(
                      radius: 35,
                      backgroundColor: Color(0xFF007AFF),
                      child: FaIcon(
                        FontAwesomeIcons.calendarCheck,
                        color: Colors.white,
                        size: 30,
                      ),
                    ),
                    const SizedBox(height: 16),
                    const Text(
                      "Shift 1 (Pagi)",
                      style: TextStyle(
                        fontSize: 22,
                        fontWeight: FontWeight.w900,
                        letterSpacing: -0.5,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      "Senin, 07 April 2026",
                      style: TextStyle(
                        fontSize: 14,
                        color: Colors.grey.shade500,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                    const SizedBox(height: 24),
                    const Divider(),
                    const SizedBox(height: 24),
                    
                    // Detail Rows
                    _buildDetailRow("Jam Kerja", "07:15 - 17:15 WITA", FontAwesomeIcons.clock, Colors.blue),
                    const SizedBox(height: 20),
                    _buildDetailRow("Istirahat", "12:00 - 13:00 WITA", FontAwesomeIcons.utensils, Colors.orange),
                    const SizedBox(height: 20),
                    _buildDetailRow("Lokasi", "OFFICE PAMA MULIA", FontAwesomeIcons.locationDot, Colors.red),
                  ],
                ),
              ),
              
              const SizedBox(height: 24),
              
              // 2. ADDITIONAL INFO SECTION
              const Text(
                "INFORMASI TAMBAHAN",
                style: TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.w900,
                  letterSpacing: 1.5,
                  color: Colors.grey,
                ),
              ),
              const SizedBox(height: 12),
              
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.6),
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: Colors.white),
                ),
                child: Column(
                  children: [
                    _buildInfoTile("Department", "HCGS - Human Capital"),
                    const Divider(height: 30),
                    _buildInfoTile("Site Code", "PAMA - SITE ARIA"),
                    const Divider(height: 30),
                    _buildInfoTile("Catatan", "Pastikan melakukan check-in tepat waktu sebelum jam 07:15 untuk menghindari keterlambatan."),
                  ],
                ),
              ),
              
              const SizedBox(height: 40),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildDetailRow(String label, String value, dynamic icon, Color iconColor) {
    return Row(
      children: [
        Container(
          width: 40,
          height: 40,
          decoration: BoxDecoration(
            color: iconColor.withValues(alpha: 0.1),
            borderRadius: BorderRadius.circular(10),
          ),
          child: Center(
            child: FaIcon(icon, color: iconColor, size: 18),
          ),
        ),
        const SizedBox(width: 16),
        Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              label,
              style: TextStyle(
                fontSize: 12,
                color: Colors.grey.shade500,
                fontWeight: FontWeight.w600,
              ),
            ),
            const SizedBox(height: 2),
            Text(
              value,
              style: const TextStyle(
                fontSize: 15,
                fontWeight: FontWeight.bold,
              ),
            ),
          ],
        ),
      ],
    );
  }

  static Widget _buildInfoTile(String label, String value) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: TextStyle(
            fontSize: 11,
            color: Colors.grey.shade500,
            fontWeight: FontWeight.w800,
          ),
        ),
        const SizedBox(height: 6),
        Text(
          value,
          style: const TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.w600,
            height: 1.5,
          ),
        ),
      ],
    );
  }
}
