import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import '../services/auth_service.dart';
import 'package:dio/dio.dart';
import 'main_layout.dart';
import '../services/biometric_service.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  bool _obscurePassword = true;
  bool _isLoading = false;
  final _nrpController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _isBiometricSupported = false;
  bool _isBiometricEnabled = false;

  @override
  void initState() {
    super.initState();
    _checkBiometricSupport();
  }

  Future<void> _checkBiometricSupport() async {
    final available = await biometricService.isBiometricAvailable();
    final enabled = await biometricService.isBiometricEnabled();
    if (mounted) {
      setState(() {
        _isBiometricSupported = available;
        _isBiometricEnabled = enabled;
      });
    }
    
    // Auto-prompt biometric if enabled
    if (enabled) {
      _handleBiometricLogin();
    }
  }

  Future<void> _handleBiometricLogin() async {
    final authenticated = await biometricService.authenticate();
    if (authenticated) {
      final creds = await biometricService.getCredentials();
      if (creds['nrp'] != null && creds['password'] != null) {
        _nrpController.text = creds['nrp']!;
        _passwordController.text = creds['password']!;
        _handleLogin();
      } else {
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text("Data login biometrik tidak ditemukan, silakan login manual")),
        );
      }
    }
  }

  Future<void> _handleLogin() async {
    if (_nrpController.text.isEmpty || _passwordController.text.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text("Silahkan isi NRP dan Password")),
      );
      return;
    }

    setState(() => _isLoading = true);

    try {
      final user = await authService.login(
        _nrpController.text, 
        _passwordController.text
      );

      if (user != null) {
        // If login successful and biometrics not enabled, ask to enable
        if (!_isBiometricEnabled && _isBiometricSupported) {
          _showEnableBiometricDialog(_nrpController.text, _passwordController.text);
        }

        if (!mounted) return;
        Navigator.pushReplacement(
          context, 
          MaterialPageRoute(builder: (context) => const MainLayout()),
        );
      } else {
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text("NRP atau Password salah")),
        );
      }
    } on DioException catch (e) {
      if (!mounted) return;
      final errorMsg = e.response?.data['error'] ?? "Gagal terhubung ke server";
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(errorMsg)),
      );
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  void _showEnableBiometricDialog(String nrp, String password) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text("Aktifkan Biometrik?", style: TextStyle(fontWeight: FontWeight.bold)),
        content: const Text("Apakah Anda ingin mengaktifkan Fingerprint/FaceID untuk login berikutnya?"),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text("Nanti Saja"),
          ),
          ElevatedButton(
            onPressed: () async {
              await biometricService.storeCredentials(nrp, password);
              if (!mounted) return;
              Navigator.pop(context);
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text("Biometrik berhasil diaktifkan")),
              );
              setState(() => _isBiometricEnabled = true);
            },
            child: const Text("Ya, Aktifkan"),
          ),
        ],
      ),
    );
  }

  @override
  void dispose() {
    _nrpController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

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
                    color: const Color(0xFF9E9E9E), // Colors.grey.shade500
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
                  controller: _nrpController,
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
                  controller: _passwordController,
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
                onPressed: _isLoading ? null : _handleLogin,
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
                child: _isLoading 
                    ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                    : const Row(
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
                            FontAwesomeIcons.arrowRightToBracket,
                            size: 18,
                          ),
                        ],
                      ),
              ),
              
              const SizedBox(height: 24),
              
              // BIOMETRIC BUTTON (RE-ADDED TO ORIGINAL DESIGN)
              if (_isBiometricSupported && _isBiometricEnabled)
                Center(
                  child: IconButton(
                    onPressed: _isLoading ? null : _handleBiometricLogin,
                    iconSize: 48,
                    icon: const Icon(
                      Icons.fingerprint,
                      color: Color(0xFF007AFF),
                    ),
                  ),
                ),

              const SizedBox(height: 48),

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
