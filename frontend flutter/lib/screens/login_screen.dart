import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import 'main_layout.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  bool _obscurePassword = true;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF4F5F7), // Apple OS Grouped Background
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 24.0, vertical: 48.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const SizedBox(height: 48),

              // LOGO PLACEHOLDER
              Center(
                child: Container(
                  width: 72,
                  height: 72,
                  decoration: BoxDecoration(
                    color: const Color(0xFF007AFF), // Apple System Blue
                    borderRadius: BorderRadius.circular(16),
                  ),
                  alignment: Alignment.center,
                  child: const Text(
                    "PLACE\nHOLDER",
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      color: Colors.white,
                      fontWeight: FontWeight.w800,
                      fontSize: 10,
                      letterSpacing: 0.5,
                    ),
                  ),
                ),
              ),
              const SizedBox(height: 24),

              // HEADER TITLES
              const Center(
                child: Text(
                  "PT. PAMA SITE ARIA",
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    fontSize: 26,
                    fontWeight: FontWeight.w900,
                    color: Colors.black,
                    letterSpacing: -0.5,
                  ),
                ),
              ),
              const SizedBox(height: 8),
              Center(
                child: Text(
                  "ABSENSI SUB COUNT",
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w600,
                    color: Color(0xFF9E9E9E), // Colors.grey.shade500
                    letterSpacing: 1.5,
                  ),
                ),
              ),

              const SizedBox(height: 64),

              // FORM NRP
              const Text(
                "NRP",
                style: TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.bold,
                  color: Colors.black,
                  letterSpacing: 0.5,
                ),
              ),
              const SizedBox(height: 8),
              Container(
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(12),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.02),
                      blurRadius: 10,
                      offset: const Offset(0, 4),
                    )
                  ],
                ),
                child: TextFormField(
                  decoration: InputDecoration(
                    hintText: "Masukkan NRP Anda",
                    hintStyle: const TextStyle(color: Color(0xFFBDBDBD), fontSize: 14), // Colors.grey.shade400
                    prefixIcon: const Padding(
                      padding: EdgeInsets.only(top: 15, bottom: 15, left: 16, right: 12),
                      child: FaIcon(FontAwesomeIcons.idBadge, color: Color(0xFFBDBDBD), size: 20),
                    ),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: BorderSide.none,
                    ),
                    filled: true,
                    fillColor: Colors.white,
                    contentPadding: const EdgeInsets.symmetric(vertical: 20),
                  ),
                  keyboardType: TextInputType.number,
                ),
              ),

              const SizedBox(height: 24),

              // FORM PASSWORD
              const Text(
                "PASSWORD",
                style: TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.bold,
                  color: Colors.black,
                  letterSpacing: 0.5,
                ),
              ),
              const SizedBox(height: 8),
              Container(
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(12),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.02),
                      blurRadius: 10,
                      offset: const Offset(0, 4),
                    )
                  ],
                ),
                child: TextFormField(
                  obscureText: _obscurePassword,
                  decoration: InputDecoration(
                    hintText: "••••••••",
                    hintStyle: const TextStyle(color: Color(0xFFBDBDBD), fontSize: 14),
                    prefixIcon: const Padding(
                      padding: EdgeInsets.only(top: 15, bottom: 15, left: 16, right: 12),
                      child: FaIcon(FontAwesomeIcons.lock, color: Color(0xFFBDBDBD), size: 18),
                    ),
                    suffixIcon: IconButton(
                      icon: FaIcon(
                        _obscurePassword ? FontAwesomeIcons.eye : FontAwesomeIcons.eyeSlash, 
                        color: const Color(0xFFBDBDBD),
                        size: 18,
                      ),
                      onPressed: () {
                        setState(() { _obscurePassword = !_obscurePassword; });
                      },
                    ),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: BorderSide.none,
                    ),
                    filled: true,
                    fillColor: Colors.white,
                    contentPadding: const EdgeInsets.symmetric(vertical: 20),
                  ),
                ),
              ),

              const SizedBox(height: 32),

              // LOGIN BUTTON
              ElevatedButton(
                onPressed: () {
                  Navigator.pushReplacement(
                    context, 
                    MaterialPageRoute(builder: (context) => const MainLayout()),
                  );
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF007AFF), // Apple System Blue
                  foregroundColor: Colors.white,
                  elevation: 0,
                  shadowColor: const Color(0xFF007AFF).withValues(alpha: 0.5),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                  padding: const EdgeInsets.symmetric(vertical: 18),
                ),
                child: const Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Text(
                      "Login",
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                        letterSpacing: 0.5,
                      ),
                    ),
                    SizedBox(width: 8),
                    FaIcon(
                      FontAwesomeIcons.arrowRightToBracket, // Arrow entering door
                      size: 18,
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 56),

              // DECORATIVE FAINT BLOCKS
              Row(
                children: [
                  Expanded(
                    child: Container(
                      height: 18,
                      color: Colors.white.withValues(alpha: 0.4),
                    ),
                  ),
                  const SizedBox(width: 2),
                  Expanded(
                    child: Container(
                      height: 18,
                      color: Colors.white.withValues(alpha: 0.4),
                    ),
                  ),
                  const SizedBox(width: 2),
                  Expanded(
                    child: Container(
                      height: 18,
                      color: Colors.white.withValues(alpha: 0.4),
                    ),
                  ),
                ],
              ),

              const SizedBox(height: 48),

              // FOOTER TEXT
              const Text(
                "PROFESSIONAL MINING & CONSTRUCTION EQUIPMENT\nMANAGEMENT\n© 2026 PAMA PERSADA NUSANTARA SITE ARIA.\nALL RIGHTS RESERVED.",
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 8.5,
                  fontWeight: FontWeight.w400,
                  color: Color(0xFFBDBDBD),
                  height: 1.5,
                  letterSpacing: 0.5,
                ),
              ),
              const SizedBox(height: 24),
            ],
          ),
        ),
      ),
    );
  }
}
