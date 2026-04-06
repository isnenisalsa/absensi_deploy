import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';

class HomeScreen extends StatelessWidget {
  const HomeScreen({super.key});

  @override
  Widget build(BuildContext context) {
    // Karena Scaffold dan SafeArea sudah diamankan di MainLayout, 
    // HomeScreen sekarang murni bertugas mengatur deretan konten utamanya.
    return SingleChildScrollView(
      padding: const EdgeInsets.symmetric(horizontal: 20.0, vertical: 16.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // 1. GREETINGS & DATE ROW
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    "Selamat Datang, USER",
                    style: TextStyle(
                      fontSize: 12,
                      color: Colors.grey.shade500,
                      letterSpacing: 0.5,
                    ),
                  ),
                  const SizedBox(height: 4),
                  const Text(
                    "Jum'at, 27 Maret 2027",
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w800,
                      letterSpacing: -0.5,
                    ),
                  ),
                ],
              ),
              Row(
                children: [
                  Container(
                    width: 8,
                    height: 8,
                    decoration: const BoxDecoration(
                      color: Color(0xFFFF3B30), // Red Dot
                      shape: BoxShape.circle,
                    ),
                  ),
                  const SizedBox(width: 6),
                  const Text(
                    "Belum Check - In",
                    style: TextStyle(
                      color: Color(0xFFFF3B30),
                      fontSize: 11,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ],
              )
            ],
          ),

          const SizedBox(height: 24),

          // 2. ATTENDANCE STATUS CARD
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
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  "ATTENDANCE STATUS",
                  style: TextStyle(
                    fontSize: 10,
                    fontWeight: FontWeight.w800,
                    color: Colors.grey.shade500,
                    letterSpacing: 1.5,
                  ),
                ),
                const SizedBox(height: 8),
                const Text(
                  "Menunggu Check - In",
                  style: TextStyle(
                    fontSize: 22,
                    fontWeight: FontWeight.w900,
                    color: Color(0xFF007AFF), // Apple Blue
                    letterSpacing: -0.5,
                  ),
                ),
                const SizedBox(height: 6),
                Row(
                  children: [
                    FaIcon(
                      FontAwesomeIcons.clock,
                      size: 14,
                      color: Colors.grey.shade400,
                    ),
                    const SizedBox(width: 8),
                    Text(
                      "Next limit: 07:15 WITA",
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.grey.shade500,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ],
                )
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
                    Container(
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
                    )
                  ],
                ),
                const SizedBox(height: 20),
                // Row 1: Shift Code
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text("Shift Code", style: TextStyle(color: Colors.grey.shade600, fontSize: 13)),
                    const Text("Shift 1", style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                  ],
                ),
                const SizedBox(height: 16),
                // Row 2: Check-in
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text("Check-in", style: TextStyle(color: Colors.grey.shade600, fontSize: 13)),
                    const Text("07:15 WITA", style: TextStyle(color: Color(0xFF34C759), fontWeight: FontWeight.bold, fontSize: 13)), // System Green
                  ],
                ),
                const SizedBox(height: 16),
                // Row 3: Check-out
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text("Check-out", style: TextStyle(color: Colors.grey.shade600, fontSize: 13)),
                    const Text("17:15 WITA", style: TextStyle(color: Color(0xFFFF3B30), fontWeight: FontWeight.bold, fontSize: 13)), // System Red
                  ],
                ),
              ],
            ),
          ),

          const SizedBox(height: 24),

          // 4. GIANT "ABSEN" BUTTON
          ElevatedButton(
            onPressed: () {},
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF007AFF), // Apple Blue
              foregroundColor: Colors.white,
              elevation: 0,
              shadowColor: const Color(0xFF007AFF).withValues(alpha: 0.3),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
              ),
              padding: const EdgeInsets.symmetric(vertical: 24),
            ),
            child: const Text(
              "A B S E N",
              style: TextStyle(
                fontSize: 28,
                fontWeight: FontWeight.w900,
                letterSpacing: 4.0,
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
              Text(
                "LIHAT SEMUA",
                style: TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.bold,
                  color: const Color(0xFF007AFF),
                  letterSpacing: 0.5,
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

          const SizedBox(height: 48), // Bottom Breathing Space
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
}
