import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import '../screens/notification_screen.dart';

class CustomHeader extends StatelessWidget {
  final String title;
  final bool showBackButton;
  final VoidCallback? onBackPressed;
  final String? photoUrl;

  const CustomHeader({
    super.key,
    required this.title,
    this.showBackButton = false,
    this.onBackPressed,
    this.photoUrl,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(30),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.03),
            blurRadius: 10,
            offset: const Offset(0, 4),
          )
        ],
      ),
      child: Row(
        children: [
          // Dynamic Leading Widget (Panah Back / Avatar Profil)
          if (showBackButton)
            GestureDetector(
              onTap: onBackPressed ?? () {
                if (Navigator.canPop(context)) {
                  Navigator.pop(context);
                }
              },
              child: const Padding(
                padding: EdgeInsets.only(right: 12.0),
                child: FaIcon(
                  FontAwesomeIcons.arrowLeft,
                  color: Colors.black,
                  size: 20,
                ),
              ),
            )
          else
            Padding(
              padding: const EdgeInsets.only(right: 12.0),
              child: CircleAvatar(
                radius: 20,
                backgroundColor: const Color(0xFFE5E5EA), // Offline-safe Gray
                backgroundImage: (photoUrl != null && photoUrl!.isNotEmpty) 
                    ? NetworkImage(photoUrl!) 
                    : null,
                child: (photoUrl == null || photoUrl!.isEmpty)
                    ? const FaIcon(
                        FontAwesomeIcons.solidUser,
                        color: Colors.white,
                        size: 20,
                      )
                    : null,
              ),
            ),
            
          // Middle Section: Dynamic Title
          Expanded(
            child: Text(
              title,
              style: const TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w900,
                letterSpacing: -0.3,
              ),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ),
          
          // Right Section: Notification Bell with Red Dot
          GestureDetector(
            behavior: HitTestBehavior.opaque, // Pastikan area kosong tetap menangkap tap
            onTap: () {
              Navigator.push(
                context,
                MaterialPageRoute(builder: (context) => const NotificationScreen()),
              );
            },
            child: Container(
              width: 44, // Ukuran target sentuh standar (Apple/Google)
              height: 44,
              alignment: Alignment.center,
              child: Stack(
                clipBehavior: Clip.none,
                children: [
                  const FaIcon(
                    FontAwesomeIcons.solidBell,
                    color: Color(0xFF8E8E93), // iOS Systems Gray
                    size: 22, // Sedikit diperbesar
                  ),
                  Positioned(
                    right: -2,
                    top: -2,
                    child: Container(
                      width: 10,
                      height: 10,
                      decoration: BoxDecoration(
                        color: const Color(0xFFFF3B30), // System Red
                        shape: BoxShape.circle,
                        border: Border.all(color: Colors.white, width: 2),
                      ),
                    ),
                  )
                ],
              ),
            ),
          )
        ],
      ),
    );
  }
}
