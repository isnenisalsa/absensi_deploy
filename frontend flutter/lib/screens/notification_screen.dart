import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import '../components/glassmorphic_layout.dart';
import '../components/custom_bottom_nav.dart';

class NotificationScreen extends StatefulWidget {
  const NotificationScreen({super.key});

  @override
  State<NotificationScreen> createState() => _NotificationScreenState();
}

class _NotificationScreenState extends State<NotificationScreen> {
  int _activeTab = 0; // 0: All, 1: Unread, 2: System

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF4F5F7),
      body: GlassmorphicLayout(
        title: "NOTIFIKASI",
        showBackButton: true,
        onBackPressed: () => Navigator.pop(context),
        bottomAction: CustomBottomNav(
          currentIndex: 1, // Stay on Absensi context
          onTap: (index) => Navigator.pop(context),
        ),
        child: Column(
          children: [
            const SizedBox(height: 125), // Spacer for header
            // 2. FILTER TABS
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 20.0, vertical: 8.0),
              child: Row(
                children: [
                  _buildTabItem("All", 0),
                  const SizedBox(width: 8),
                  _buildTabItem("Unread", 1),
                  const SizedBox(width: 8),
                  _buildTabItem("System", 2),
                ],
              ),
            ),

            // 3. NOTIFICATION LIST
            Expanded(
              child: ListView(
                padding: const EdgeInsets.symmetric(horizontal: 20.0, vertical: 16.0),
                children: [
                  // CARD 1: Absensi Berhasil
                  _buildNotificationCard(
                    title: "Absensi Berhasil",
                    description: "Anda telah berhasil melakukan check-in di Office Mulia pada pukul 07:15 WITA.",
                    time: "2 Menit Lalu",
                    icon: FontAwesomeIcons.check,
                    iconColor: const Color(0xFF34C759),
                    accentColor: const Color(0xFF34C759),
                    isUnread: true,
                  ),
                  const SizedBox(height: 16),
                  
                  const Padding(
                    padding: EdgeInsets.symmetric(vertical: 8.0),
                    child: Text("HARI INI", style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Colors.grey, letterSpacing: 1.2)),
                  ),
                  
                  // CARD 2: Pengingat Shift
                  _buildNotificationCard(
                    title: "Pengingat Shift",
                    description: "Shift Sore Anda akan dimulai dalam 30 menit. Pastikan koneksi GPS aktif.",
                    time: "10:30 AM",
                    icon: FontAwesomeIcons.clock,
                    iconColor: Colors.black87,
                    accentColor: Colors.transparent,
                    isUnread: false,
                    highlightText: "30 menit",
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildTabItem(String label, int index) {
    bool isActive = _activeTab == index;
    return GestureDetector(
      onTap: () => setState(() => _activeTab = index),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
        decoration: BoxDecoration(
          color: isActive ? const Color(0xFF007AFF) : Colors.white.withValues(alpha: 0.5),
          borderRadius: BorderRadius.circular(12),
        ),
        child: Text(
          label,
          style: TextStyle(
            color: isActive ? Colors.white : Colors.grey.shade600,
            fontWeight: FontWeight.bold,
            fontSize: 13,
          ),
        ),
      ),
    );
  }

  Widget _buildNotificationCard({
    required String title,
    required String description,
    required String time,
    required dynamic icon,
    required Color iconColor,
    required Color accentColor,
    required bool isUnread,
    String? highlightText,
  }) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(color: Colors.black.withValues(alpha: 0.03), blurRadius: 10, offset: const Offset(0, 4))
        ],
      ),
      child: Stack(
        children: [
          // Green Accent Line (Optional per type)
          if (accentColor != Colors.transparent)
            Positioned(
              left: 0, top: 12, bottom: 12,
              child: Container(width: 4, decoration: BoxDecoration(color: accentColor, borderRadius: BorderRadius.circular(2))),
            ),
          
          Padding(
            padding: const EdgeInsets.all(16.0),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Icon Block
                Container(
                  width: 44, height: 44,
                  decoration: BoxDecoration(
                    color: iconColor.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Center(child: FaIcon(icon, color: iconColor, size: 18)),
                ),
                const SizedBox(width: 16),
                
                // Content
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(title, style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w900)),
                          Text(time, style: const TextStyle(fontSize: 10, color: Color(0xFF007AFF), fontWeight: FontWeight.bold)),
                        ],
                      ),
                      const SizedBox(height: 6),
                      _buildDescriptionText(description, highlightText),
                    ],
                  ),
                ),
              ],
            ),
          ),
          
          // Unread Dot
          if (isUnread)
            Positioned(
              right: 12, bottom: 12,
              child: Container(width: 8, height: 8, decoration: const BoxDecoration(color: Color(0xFF003B70), shape: BoxShape.circle)),
            ),
        ],
      ),
    );
  }

  Widget _buildDescriptionText(String text, String? highlight) {
    if (highlight == null || !text.contains(highlight)) {
      return Text(text, style: TextStyle(fontSize: 12, color: Colors.grey.shade600, height: 1.4));
    }
    
    final parts = text.split(highlight);
    return RichText(
      text: TextSpan(
        style: TextStyle(fontSize: 12, color: Colors.grey.shade600, height: 1.4, fontFamily: 'Inter'), // Safe font
        children: [
          TextSpan(text: parts[0]),
          TextSpan(text: highlight, style: const TextStyle(color: Color(0xFFFF3B30), fontWeight: FontWeight.bold)),
          TextSpan(text: parts[1]),
        ],
      ),
    );
  }
}
