import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import 'dart:ui';
import '../components/glassmorphic_layout.dart';

class FtwSurveyScreen extends StatefulWidget {
  const FtwSurveyScreen({super.key});

  @override
  State<FtwSurveyScreen> createState() => _FtwSurveyScreenState();
}

class _FtwSurveyScreenState extends State<FtwSurveyScreen> {
  bool? _hasSymptoms = false; // false = TIDAK, true = YA, null = belum pilih

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF4F5F7),
      body: GlassmorphicLayout(
        title: "KESEHATAN",
        showBackButton: true,
        onBackPressed: () => Navigator.pop(context),
        child: SingleChildScrollView(
          padding: const EdgeInsets.only(top: 130, bottom: 120, left: 20, right: 20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // 2. DARK USER INFO CARD
              _buildDarkInfoCard(),
              const SizedBox(height: 16),

              // 3. PERUSAHAAN SECTION
              _buildPerusahaanSection(),
              const SizedBox(height: 16),

              // 4. SYMPTOMS SURVEY CARD
              _buildSymptomsCard(),
              const SizedBox(height: 24),

              // 5. SUBMIT BUTTON
              ElevatedButton.icon(
                onPressed: () {
                  if (_hasSymptoms == null) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(content: Text("Pilih salah satu jawaban")),
                    );
                    return;
                  }
                  // Success flow
                  _showGlassDialog(
                    AlertDialog(
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
                      content: Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const SizedBox(height: 16),
                          Container(
                            width: 80, height: 80,
                            decoration: const BoxDecoration(color: Color(0xFF34C759), shape: BoxShape.circle),
                            child: const Icon(Icons.check, color: Colors.white, size: 50),
                          ),
                          const SizedBox(height: 24),
                          const Text(
                            "Berhasil!",
                            style: TextStyle(fontSize: 22, fontWeight: FontWeight.w900),
                          ),
                          const SizedBox(height: 8),
                          Text(
                            "Jawaban FTW Anda telah dikirim. Tetap utamakan kesehatan!",
                            textAlign: TextAlign.center,
                            style: TextStyle(fontSize: 14, color: Colors.grey.shade600, fontWeight: FontWeight.w500),
                          ),
                          const SizedBox(height: 24),
                          ElevatedButton(
                            onPressed: () => Navigator.of(context).popUntil((route) => route.isFirst),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: const Color(0xFF007AFF),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                              minimumSize: const Size(double.infinity, 50),
                            ),
                            child: const Text("OK", style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
                          ),
                        ],
                      ),
                    ),
                  );
                },
                icon: const FaIcon(FontAwesomeIcons.paperPlane, size: 16),
                label: const Text("KIRIM JAWABAN", style: TextStyle(fontSize: 16, fontWeight: FontWeight.w900)),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF0052cc),
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 20),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  elevation: 4,
                  shadowColor: const Color(0xFF0052cc).withValues(alpha: 0.3),
                ),
              ),
              const SizedBox(height: 40),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildDarkInfoCard() {
    return Container(
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: const Color(0xFF2C2E33), // Dark Slate Gray
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(color: Colors.black.withValues(alpha: 0.2), blurRadius: 15, offset: const Offset(0, 8))
        ],
      ),
      child: Stack(
        children: [
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text("TANGGAL", style: TextStyle(color: Colors.grey.shade500, fontSize: 10, fontWeight: FontWeight.bold, letterSpacing: 1.0)),
              const SizedBox(height: 4),
              const Text("31 Maret 2026", style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold)),
              const SizedBox(height: 16),
              Text("NAMA", style: TextStyle(color: Colors.grey.shade500, fontSize: 10, fontWeight: FontWeight.bold, letterSpacing: 1.0)),
              const SizedBox(height: 4),
              const Text("ISNAENI SALSABELA", style: TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.w900)),
            ],
          ),
          Positioned(
            right: 0,
            top: 0,
            child: Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: 0.08),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Column(
                children: [
                  const FaIcon(FontAwesomeIcons.idCard, color: Colors.white38, size: 40),
                  const SizedBox(height: 8),
                  Text("NRP", style: TextStyle(color: Colors.grey.shade500, fontSize: 9, fontWeight: FontWeight.bold)),
                  const Text("AR260012", style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold)),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPerusahaanSection() {
    return Container(
      padding: const EdgeInsets.all(4),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.grey.shade300),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.only(left: 12, top: 8),
            child: Text("PERUSAHAAN*", style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Colors.grey.shade600)),
          ),
          ListTile(
            leading: Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(color: const Color(0xFF007AFF).withValues(alpha: 0.1), borderRadius: BorderRadius.circular(8)),
              child: const FaIcon(FontAwesomeIcons.building, color: Color(0xFF007AFF), size: 18),
            ),
            title: const Text("Magang / PKL", style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }

  Widget _buildSymptomsCard() {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.grey.shade200),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          RichText(
            text: TextSpan(
              style: const TextStyle(color: Colors.black, fontSize: 13, height: 1.5, fontWeight: FontWeight.bold),
              children: [
                const TextSpan(text: "Apakah anda mengalami salah satu atau lebih dari gejala dibawah ini? "),
                TextSpan(text: "(Demam, batuk, flu/pilek, radang tenggorokan, sesak nafas, gangguan penciuman, gangguan pengecepan, gangguan pencernaan, ruam kulit, delirium) ", style: TextStyle(color: Colors.grey.shade600, fontWeight: FontWeight.normal)),
                const TextSpan(text: "*", style: TextStyle(color: Colors.red)),
              ],
            ),
          ),
          const SizedBox(height: 20),
          _buildChoiceRow("YA", _hasSymptoms == true, () => setState(() => _hasSymptoms = true)),
          const SizedBox(height: 12),
          _buildChoiceRow("TIDAK", _hasSymptoms == false, () => setState(() => _hasSymptoms = false)),
        ],
      ),
    );
  }

  Widget _buildChoiceRow(String label, bool isSelected, VoidCallback onTap) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
        decoration: BoxDecoration(
          color: isSelected ? const Color(0xFFF0F7FF) : Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: isSelected ? const Color(0xFF0052cc) : Colors.grey.shade300, width: 1.5),
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(label, style: TextStyle(fontSize: 15, fontWeight: FontWeight.w900, color: isSelected ? const Color(0xFF0052cc) : Colors.black87)),
            Container(
              width: 24, height: 24,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                border: Border.all(color: isSelected ? const Color(0xFF0052cc) : Colors.grey.shade400, width: 2),
                color: isSelected ? const Color(0xFF0052cc) : Colors.transparent,
              ),
              child: isSelected ? const Icon(Icons.check, color: Colors.white, size: 14) : null,
            ),
          ],
        ),
      ),
    );
  }

  void _showGlassDialog(Widget content) {
    showGeneralDialog(
      context: context,
      barrierDismissible: true,
      barrierLabel: '',
      barrierColor: Colors.black.withValues(alpha: 0.4),
      transitionDuration: const Duration(milliseconds: 400),
      pageBuilder: (context, anim1, anim2) {
        return content;
      },
      transitionBuilder: (context, anim1, anim2, child) {
        return BackdropFilter(
          filter: ImageFilter.blur(sigmaX: 10.0 * anim1.value, sigmaY: 10.0 * anim1.value),
          child: FadeTransition(
            opacity: anim1,
            child: ScaleTransition(
              scale: anim1.drive(Tween(begin: 0.95, end: 1.0).chain(CurveTween(curve: Curves.easeOutCubic))),
              child: child,
            ),
          ),
        );
      },
    );
  }
}
