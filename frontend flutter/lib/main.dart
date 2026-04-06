import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import 'screens/login_screen.dart';

void main() {
  SystemChrome.setSystemUIOverlayStyle(
    const SystemUiOverlayStyle(
      statusBarColor: Colors.transparent, // StatusBar tembus pandang (iOS feel)
      statusBarIconBrightness: Brightness.dark,
    ),
  );
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'PAMA Absensi',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(
          seedColor: const Color(0xFF007AFF),
          surface: const Color(0xFFF4F5F7),
        ),
        useMaterial3: true,
        // Font family global bisa ditambahkan di sini misal: fontFamily: 'Inter'
        textTheme: GoogleFonts.interTextTheme(
          ThemeData.light().textTheme,
        ).copyWith(
          // Memastikan ada offset letter-spacing jika diperlukan
          bodyMedium: GoogleFonts.inter(letterSpacing: -0.3),
        ),
      ),
      home: const LoginScreen(),
    );
  }
}
