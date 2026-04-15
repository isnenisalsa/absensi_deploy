import 'dart:io';
import 'dart:ui';
import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import 'package:intl/intl.dart';
import '../components/glassmorphic_layout.dart';
import '../services/attendance_service.dart';
import '../services/api_service.dart';

class AttendanceHistoryScreen extends StatefulWidget {
  const AttendanceHistoryScreen({super.key});

  @override
  State<AttendanceHistoryScreen> createState() => _AttendanceHistoryScreenState();
}

class _AttendanceHistoryScreenState extends State<AttendanceHistoryScreen> {
  List<dynamic> _history = [];
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadHistory();
  }

  Future<void> _loadHistory() async {
    setState(() { _isLoading = true; _error = null; });
    try {
      final data = await attendanceService.getAttendanceHistory();
      if (mounted) {
        setState(() {
          _history = data;
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _error = "Gagal memuat riwayat: $e";
          _isLoading = false;
        });
      }
    }
  }

  String _formatDate(String? raw) {
    if (raw == null) return '-';
    try {
      final dt = DateTime.parse(raw);
      return DateFormat('EEE, dd MMM yyyy', 'id').format(dt);
    } catch (_) {
      return raw.split('T')[0];
    }
  }

  String _formatTime(String? raw) {
    if (raw == null) return '--:--';
    try {
      final dt = DateTime.parse(raw);
      return DateFormat('HH:mm').format(dt);
    } catch (_) {
      if (raw.contains('T')) return raw.split('T')[1].substring(0, 5);
      return raw.substring(0, 5);
    }
  }

  String _getPhotoUrl(String? filename) {
    if (filename == null || filename.isEmpty) return '';
    // Sudah merupakan full URL
    if (filename.startsWith('http')) return filename;
    // Filename dari backend (disimpan di uploads/attendance/)
    final base = Platform.isAndroid ? 'http://10.0.2.2:3000' : 'http://127.0.0.1:3000';
    return '$base/uploads/attendance/$filename';
  }

  bool _hasPhoto(dynamic item) {
    final p = item['photo_evidence'];
    return p != null && p.toString().isNotEmpty && !p.toString().contains('dummy');
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF4F5F7),
      body: GlassmorphicLayout(
        title: "RIWAYAT ABSENSI",
        showBackButton: true,
        onBackPressed: () => Navigator.pop(context),
        child: _isLoading
            ? const Center(child: CircularProgressIndicator())
            : _error != null
                ? _buildError()
                : _history.isEmpty
                    ? _buildEmpty()
                    : _buildList(),
      ),
    );
  }

  Widget _buildError() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(40),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.cloud_off_rounded, size: 60, color: Colors.grey),
            const SizedBox(height: 16),
            Text(_error!, textAlign: TextAlign.center, style: const TextStyle(color: Colors.grey)),
            const SizedBox(height: 20),
            ElevatedButton.icon(
              onPressed: _loadHistory,
              icon: const Icon(Icons.refresh),
              label: const Text("Coba Lagi"),
              style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF007AFF), foregroundColor: Colors.white),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildEmpty() {
    return const Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.history_rounded, size: 64, color: Colors.grey),
          SizedBox(height: 16),
          Text("Belum ada riwayat absensi", style: TextStyle(color: Colors.grey, fontWeight: FontWeight.w600)),
        ],
      ),
    );
  }

  Widget _buildList() {
    return RefreshIndicator(
      onRefresh: _loadHistory,
      child: ListView.builder(
        padding: const EdgeInsets.only(top: 130, bottom: 40, left: 20, right: 20),
        itemCount: _history.length,
        itemBuilder: (context, i) => _buildHistoryCard(_history[i]),
      ),
    );
  }

  Widget _buildHistoryCard(dynamic item) {
    final isCheckIn = item['trans_type'] == 'Check_in';
    final hasPhoto = _hasPhoto(item);
    final photoUrl = hasPhoto ? _getPhotoUrl(item['photo_evidence']) : null;

    final cardColor = isCheckIn ? const Color(0xFF007AFF) : const Color(0xFFFF9500);
    final bgColor = isCheckIn
        ? const Color(0xFF007AFF).withValues(alpha: 0.06)
        : const Color(0xFFFF9500).withValues(alpha: 0.06);

    return Container(
      margin: const EdgeInsets.only(bottom: 14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 12,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(20),
        child: Column(
          children: [
            // ── Header bar ──────────────────────────────────────────
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              color: bgColor,
              child: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: cardColor,
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(
                          isCheckIn ? Icons.login_rounded : Icons.logout_rounded,
                          color: Colors.white,
                          size: 12,
                        ),
                        const SizedBox(width: 5),
                        Text(
                          isCheckIn ? "CHECK-IN" : "CHECK-OUT",
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 10,
                            fontWeight: FontWeight.w900,
                            letterSpacing: 1,
                          ),
                        ),
                      ],
                    ),
                  ),
                  const Spacer(),
                  Text(
                    _formatDate(item['attendance_date']),
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w700,
                      color: Colors.grey.shade600,
                    ),
                  ),
                ],
              ),
            ),

            // ── Body ───────────────────────────────────────────────
            Padding(
              padding: const EdgeInsets.all(16),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Left: Foto evidence
                  GestureDetector(
                    onTap: hasPhoto ? () => _showPhotoPreview(photoUrl!) : null,
                    child: Container(
                      width: 72,
                      height: 72,
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(14),
                        color: Colors.grey.shade100,
                        border: Border.all(
                          color: hasPhoto ? cardColor.withValues(alpha: 0.3) : Colors.grey.shade200,
                        ),
                      ),
                      child: hasPhoto
                          ? ClipRRect(
                              borderRadius: BorderRadius.circular(13),
                              child: Stack(
                                fit: StackFit.expand,
                                children: [
                                  Image.network(
                                    photoUrl!,
                                    fit: BoxFit.cover,
                                    errorBuilder: (_, __, ___) => _noPhotoPlaceholder(),
                                  ),
                                  // Zoom icon overlay
                                  Positioned(
                                    bottom: 4, right: 4,
                                    child: Container(
                                      padding: const EdgeInsets.all(3),
                                      decoration: BoxDecoration(
                                        color: Colors.black.withValues(alpha: 0.5),
                                        shape: BoxShape.circle,
                                      ),
                                      child: const Icon(Icons.zoom_in, color: Colors.white, size: 10),
                                    ),
                                  ),
                                ],
                              ),
                            )
                          : _noPhotoPlaceholder(),
                    ),
                  ),
                  const SizedBox(width: 14),

                  // Right: Info
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Jam
                        Row(
                          children: [
                            Icon(Icons.schedule_rounded, size: 13, color: cardColor),
                            const SizedBox(width: 4),
                            Text(
                              _formatTime(item['time_wita']),
                              style: TextStyle(
                                fontSize: 22,
                                fontWeight: FontWeight.w900,
                                color: cardColor,
                                letterSpacing: -0.5,
                              ),
                            ),
                            const SizedBox(width: 4),
                            Text(
                              "WITA",
                              style: TextStyle(
                                fontSize: 10,
                                fontWeight: FontWeight.w600,
                                color: Colors.grey.shade500,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 6),
                        // Lokasi
                        if (item['work_location'] != null && item['work_location'].toString().isNotEmpty) ...[
                          Row(
                            children: [
                              Icon(Icons.location_on_rounded, size: 12, color: Colors.grey.shade500),
                              const SizedBox(width: 4),
                              Expanded(
                                child: Text(
                                  item['work_location'].toString(),
                                  style: TextStyle(
                                    fontSize: 11,
                                    fontWeight: FontWeight.w700,
                                    color: Colors.grey.shade700,
                                  ),
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 4),
                        ],
                        // Koordinat
                        if (item['att_latitude'] != null) ...[
                          Row(
                            children: [
                              Icon(Icons.my_location, size: 11, color: Colors.grey.shade400),
                              const SizedBox(width: 4),
                              Text(
                                "${double.parse(item['att_latitude'].toString()).toStringAsFixed(5)}, "
                                "${double.parse(item['att_longitude'].toString()).toStringAsFixed(5)}",
                                style: TextStyle(
                                  fontSize: 9,
                                  fontFamily: 'monospace',
                                  color: Colors.grey.shade400,
                                ),
                              ),
                            ],
                          ),
                        ],
                        const SizedBox(height: 6),
                        // Map link
                        if (item['att_map_link'] != null)
                          Row(
                            children: [
                              Icon(Icons.map_outlined, size: 11, color: Colors.blue.shade300),
                              const SizedBox(width: 4),
                              Text(
                                hasPhoto ? "Ada foto bukti" : "Tidak ada foto",
                                style: TextStyle(
                                  fontSize: 10,
                                  fontWeight: FontWeight.w600,
                                  color: hasPhoto ? Colors.green.shade600 : Colors.grey.shade400,
                                ),
                              ),
                              if (hasPhoto) ...[
                                const SizedBox(width: 4),
                                Icon(Icons.check_circle, size: 11, color: Colors.green.shade500),
                              ],
                            ],
                          ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _noPhotoPlaceholder() {
    return Column(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        Icon(Icons.no_photography_rounded, size: 22, color: Colors.grey.shade300),
        const SizedBox(height: 4),
        Text(
          "Tidak\nAda Foto",
          textAlign: TextAlign.center,
          style: TextStyle(fontSize: 8, color: Colors.grey.shade400, height: 1.2),
        ),
      ],
    );
  }

  void _showPhotoPreview(String imageUrl) {
    showGeneralDialog(
      context: context,
      barrierDismissible: true,
      barrierLabel: '',
      barrierColor: Colors.black.withValues(alpha: 0.85),
      transitionDuration: const Duration(milliseconds: 300),
      pageBuilder: (context, anim1, anim2) {
        return GestureDetector(
          onTap: () => Navigator.pop(context),
          child: Scaffold(
            backgroundColor: Colors.transparent,
            body: Stack(
              children: [
                Center(
                  child: InteractiveViewer(
                    child: Image.network(
                      imageUrl,
                      fit: BoxFit.contain,
                      errorBuilder: (_, __, ___) => const Center(
                        child: Text("Gagal memuat foto", style: TextStyle(color: Colors.white)),
                      ),
                    ),
                  ),
                ),
                SafeArea(
                  child: Align(
                    alignment: Alignment.topRight,
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: GestureDetector(
                        onTap: () => Navigator.pop(context),
                        child: Container(
                          decoration: BoxDecoration(
                            color: Colors.white.withValues(alpha: 0.2),
                            shape: BoxShape.circle,
                          ),
                          padding: const EdgeInsets.all(10),
                          child: const Icon(Icons.close, color: Colors.white, size: 20),
                        ),
                      ),
                    ),
                  ),
                ),
                // Label bukti
                Positioned(
                  bottom: 40, left: 0, right: 0,
                  child: Center(
                    child: ClipRRect(
                      borderRadius: BorderRadius.circular(20),
                      child: BackdropFilter(
                        filter: ImageFilter.blur(sigmaX: 10, sigmaY: 10),
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
                          decoration: BoxDecoration(
                            color: Colors.white.withValues(alpha: 0.15),
                            borderRadius: BorderRadius.circular(20),
                            border: Border.all(color: Colors.white.withValues(alpha: 0.3)),
                          ),
                          child: const Text(
                            "📷  Foto Bukti Absensi",
                            style: TextStyle(
                              color: Colors.white,
                              fontWeight: FontWeight.w700,
                              fontSize: 13,
                            ),
                          ),
                        ),
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        );
      },
      transitionBuilder: (context, anim1, anim2, child) {
        return FadeTransition(
          opacity: anim1,
          child: ScaleTransition(
            scale: anim1.drive(Tween(begin: 0.92, end: 1.0).chain(CurveTween(curve: Curves.easeOutCubic))),
            child: child,
          ),
        );
      },
    );
  }
}
