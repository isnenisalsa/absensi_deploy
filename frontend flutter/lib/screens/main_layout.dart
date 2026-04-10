import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import '../services/auth_service.dart';
import '../components/custom_bottom_nav.dart';
import '../components/glassmorphic_layout.dart';
import 'home_screen.dart';
import 'attendance_screen.dart';
import 'profile_screen.dart';

class MainLayout extends StatefulWidget {
  const MainLayout({super.key});

  @override
  State<MainLayout> createState() => _MainLayoutState();
}

class _MainLayoutState extends State<MainLayout> {
  int _selectedIndex = 0;
  late PageController _pageController;
  Map<String, dynamic>? _userData;

  // Header and Layout Overrides
  String? _titleOverride;
  bool _showBackOverride = false;
  bool _showFooter = true;
  VoidCallback? _customBackAction;

  @override
  void initState() {
    super.initState();
    _pageController = PageController(initialPage: _selectedIndex);
    _loadUser();
  }

  Future<void> _loadUser() async {
    final data = await authService.getUserData();
    if (mounted) {
      setState(() {
        _userData = data;
      });
    }
  }

  @override
  void dispose() {
    _pageController.dispose();
    super.dispose();
  }

  void _updateLayout(String? title, bool showBack, bool showFooter, {VoidCallback? onBack}) {
    setState(() {
      _titleOverride = title;
      _showBackOverride = showBack;
      _showFooter = showFooter;
      _customBackAction = onBack;
    });
  }

  void _onItemTapped(int index) {
    if (_selectedIndex != index) {
      setState(() {
        _selectedIndex = index;
        _titleOverride = null;
        _showBackOverride = false;
        _showFooter = true;
        _customBackAction = null;
      });
      _pageController.animateToPage(
        index,
        duration: const Duration(milliseconds: 400),
        curve: Curves.easeOutQuint,
      );
    }
  }

  String _getHeaderTitle(int index) {
    if (_titleOverride != null && index == 1) return _titleOverride!;
    if (index == 0) return "HOME";
    if (index == 1) return "ABSENSI";
    return "PROFILE";
  }

  @override
  Widget build(BuildContext context) {
    // Construct photoUrl from userData
    final employeeData = _userData?['employee_data'];
    final photoPath = employeeData?['photo_profile'];
    String? fullPhotoUrl;
    
    if (photoPath != null) {
      if (photoPath.startsWith('http')) {
        fullPhotoUrl = photoPath;
      } else {
        final String baseUrl = kIsWeb ? 'http://localhost:3000' : (Platform.isAndroid ? 'http://10.0.2.2:3000' : 'http://localhost:3000');
        fullPhotoUrl = '$baseUrl$photoPath';
      }
    }

    return Scaffold(
      backgroundColor: const Color(0xFFF4F5F7),
      body: GlassmorphicLayout(
        title: _getHeaderTitle(_selectedIndex),
        photoUrl: fullPhotoUrl,
        showBackButton: _showBackOverride || (_selectedIndex != 0 && _selectedIndex != 1 && _selectedIndex != 2), 
        onBackPressed: () {
          if (_customBackAction != null) {
            _customBackAction!();
          } else {
            _onItemTapped(0);
          }
        },
        bottomAction: _showFooter 
          ? CustomBottomNav(
              currentIndex: _selectedIndex,
              onTap: _onItemTapped,
            )
          : null,
        child: PageView(
          controller: _pageController,
          onPageChanged: (index) {
            setState(() {
              _selectedIndex = index;
              _titleOverride = null;
              _showBackOverride = false;
              _showFooter = true;
              _customBackAction = null;
            });
          },
          physics: const NeverScrollableScrollPhysics(),
          children: [
            HomeScreen(onTabChange: _onItemTapped),
            AttendanceScreen(onLayoutChange: _updateLayout),
            ProfileScreen(onPhotoUpdated: _loadUser),
          ],
        ),
      ),
    );
  }
}
