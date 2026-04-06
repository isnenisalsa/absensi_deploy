import 'package:flutter/material.dart';
import '../components/custom_header.dart';
import '../components/custom_bottom_nav.dart';
import 'home_screen.dart';
import 'attendance_screen.dart';

class MainLayout extends StatefulWidget {
  const MainLayout({super.key});

  @override
  State<MainLayout> createState() => _MainLayoutState();
}

class _MainLayoutState extends State<MainLayout> {
  int _selectedIndex = 0;

  void _onItemTapped(int index) {
    setState(() {
      _selectedIndex = index;
    });
  }

  // Fungsi dinamis pengganti teks Varian Header
  String _getHeaderTitle(int index) {
    switch (index) {
      case 0:
        return "ARIA SUB COUNT";
      case 1:
        return "DAFTAR HADIR KARYAWAN";
      case 2:
        return "PROFILE";
      default:
        return "PAMA ABSENSI";
    }
  }

  // _pages List dihapus karena dirender langsung di dalam Stack

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      extendBody: true, // Konten di bawah Navbar transparan akan terlihat
      backgroundColor: const Color(0xFFF4F5F7), // Apple OS Grouped Background
      body: Stack(
        children: [
          // Lapis Bawah: Halaman Konten (Map, Home, Profil)
          Positioned.fill(
            child: IndexedStack(
              index: _selectedIndex,
              children: [
                // HomeScreen butuh padding ekstra di atas agar tidak tertutup header mengapung
                Padding(
                  padding: const EdgeInsets.only(top: 90.0), // Kompensasi tinggi Header
                  child: const HomeScreen(),
                ),
                const AttendanceScreen(), // Peta Full Screen tanpa batas atas
                const Center(child: Text("Halaman Profil", style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold))),
              ],
            ),
          ),
          
          // Lapis Atas: Header Varian (Mengapung)
          Positioned(
            top: 0,
            left: 0,
            right: 0,
            child: SafeArea(
              bottom: false,
              child: Padding(
                padding: const EdgeInsets.only(top: 24.0, left: 20.0, right: 20.0),
                child: CustomHeader(
                  title: _getHeaderTitle(_selectedIndex),
                  showBackButton: false, // Disini kita pakai varian Avatar, bukan tombol back
                ),
              ),
            ),
          ),
        ],
      ),
      // FOOTER NAV BAR (Kaku/Statis di bawah)
      bottomNavigationBar: CustomBottomNav(
        currentIndex: _selectedIndex,
        onTap: _onItemTapped,
      ),
    );
  }
}
