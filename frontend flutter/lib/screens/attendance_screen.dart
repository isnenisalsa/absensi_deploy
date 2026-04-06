import 'dart:ui';
import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';

class AttendanceScreen extends StatefulWidget {
  const AttendanceScreen({super.key});

  @override
  State<AttendanceScreen> createState() => _AttendanceScreenState();
}

class _AttendanceScreenState extends State<AttendanceScreen> {
  // Mode aktif: true = Check-in, false = Check-out
  bool _isCheckInMode = true;

  // State Pilihan Lokasi (Bisa di cari/search)
  String _selectedLocation = "OFFICE PAMA MULIA";

  // Data Dummy untuk banyak Lokasi
  final List<String> _allLocations = [
    "OFFICE PAMA MULIA",
    "SITE ARIA",
    "SITE KPC",
    "SITE ADARO",
    "SITE KIDECO",
    "SITE TCMM",
    "HEAD OFFICE JAKARTA",
    "WAREHOUSE BALIKPAPAN",
    "PLTU CELUKAN BAWANG"
  ];

  // Koordinat Utama (Contoh Kantor PAMA Jakarta/Pusat Koordinat Sembarang)
  final LatLng _officeLocation = const LatLng(-6.275816, 106.828669); 

  // Fungsi Modul Cepat Pencarian Lokasi (Bottom Sheet)
  void _showLocationSearchModal(BuildContext context) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true, // Biar menutupi layar sebagian
      backgroundColor: Colors.transparent, // Background bening untuk sudut membulat
      builder: (context) {
        String searchQuery = "";
        return StatefulBuilder(
          builder: (context, setModalState) {
            final filteredLocations = _allLocations
                .where((loc) => loc.toLowerCase().contains(searchQuery.toLowerCase()))
                .toList();
            
            return Container(
              height: MediaQuery.of(context).size.height * 0.75, // Muncul memenuhi 75% layar atas
              decoration: const BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.only(topLeft: Radius.circular(24), topRight: Radius.circular(24)),
              ),
              padding: EdgeInsets.only(
                top: 12, left: 24, right: 24, 
                bottom: MediaQuery.of(context).viewInsets.bottom, // Antisipasi Keyboard Muncul
              ),
              child: Column(
                children: [
                  // Gagang Modal
                  Container(
                    width: 40, height: 5,
                    decoration: BoxDecoration(color: Colors.grey.shade300, borderRadius: BorderRadius.circular(10)),
                  ),
                  const SizedBox(height: 20),
                  const Text("Pilih Lokasi Absensi", style: TextStyle(fontSize: 18, fontWeight: FontWeight.w900, letterSpacing: -0.5)),
                  const SizedBox(height: 16),
                  
                  // Kotak Pencarian Bar
                  TextField(
                    decoration: InputDecoration(
                      hintText: "Ketik nama lokasi / site...",
                      hintStyle: TextStyle(color: Colors.grey.shade500, fontSize: 13),
                      prefixIcon: const Icon(Icons.search, color: Color(0xFF007AFF)),
                      filled: true,
                      fillColor: Colors.grey.shade100,
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: BorderSide.none),
                      contentPadding: const EdgeInsets.symmetric(vertical: 0),
                    ),
                    onChanged: (val) {
                      setModalState(() { searchQuery = val; });
                    },
                  ),
                  const SizedBox(height: 16),
                  
                  // List Yang Bisa di Scroll dan Beradaptasi
                  Expanded(
                    child: ListView.separated(
                      itemCount: filteredLocations.length,
                      separatorBuilder: (context, index) => Divider(color: Colors.grey.shade200, height: 1),
                      itemBuilder: (context, index) {
                        final loc = filteredLocations[index];
                        return ListTile(
                          contentPadding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                          leading: Container(
                            padding: const EdgeInsets.all(8),
                            decoration: BoxDecoration(color: const Color(0xFF007AFF).withValues(alpha: 0.1), shape: BoxShape.circle),
                            child: const FaIcon(FontAwesomeIcons.locationDot, color: Color(0xFF007AFF), size: 14),
                          ),
                          title: Text(loc, style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13)),
                          onTap: () {
                            setState(() { _selectedLocation = loc; });
                            Navigator.pop(context); // Tutup saat dipilih
                          },
                        );
                      }
                    )
                  )
                ],
              ),
            );
          }
        );
      }
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Stack(
        children: [
          // 1. BACKGROUND MAP (OPEN STREET MAP)
          FlutterMap(
            options: MapOptions(
              initialCenter: _officeLocation,
              initialZoom: 16.0,
              minZoom: 3.0,
              maxZoom: 19.0,
            ),
            children: [
              TileLayer(
                // Menggunakan Satelit asli Google Maps (Street View)
                urlTemplate: 'https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}',
                userAgentPackageName: 'com.example.pama_absensi',
              ),
              MarkerLayer(
                markers: [
                  Marker(
                    point: _officeLocation,
                    width: 140,
                    height: 80,
                    child: Column(
                      children: [
                        const FaIcon(
                          FontAwesomeIcons.locationDot,
                          color: Color(0xFF003B70), // Dark Navy Pama
                          size: 36,
                        ),
                        Container(
                          margin: const EdgeInsets.only(top: 4),
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(4),
                            boxShadow: [
                              BoxShadow(color: Colors.black.withValues(alpha: 0.1), blurRadius: 4),
                            ],
                          ),
                          child: Text(
                            _selectedLocation,
                            style: const TextStyle(fontSize: 8, fontWeight: FontWeight.bold),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ],
          ),

          // 2. ABSENSI TOP CONTROLS (TOGGLE & DROPDOWN)
          Align(
            alignment: Alignment.topCenter,
            child: SafeArea(
              child: Padding(
                padding: const EdgeInsets.only(top: 90, left: 24, right: 24), // Aman dari Floating Header (Overlap)
                child: Column(
                  mainAxisSize: MainAxisSize.min, // SANGAT PENTING: Mencegah kolom ini melar ke bawah dan 'memakan' fitur geser map!
                  children: [
                    // Check-in / Check-out Toggle (Sliding Animation Segmented Control)
                  Container(
                    height: 52,
                    padding: const EdgeInsets.all(4), // Jarak di dalam agar kelihatan 'tenggelam'
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.95), // Background trek putih
                      borderRadius: BorderRadius.circular(26),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withValues(alpha: 0.08),
                          blurRadius: 15,
                          offset: const Offset(0, 5),
                        ),
                      ],
                    ),
                    child: LayoutBuilder(
                      builder: (context, constraints) {
                        final double buttonWidth = constraints.maxWidth / 2;
                        return Stack(
                          children: [
                            // Kapsul Warna yang Bisa Meluncur secara Fisik Kiri Kanan
                            AnimatedPositioned(
                              duration: const Duration(milliseconds: 300),
                              curve: Curves.easeOutCubic, // Lembut dan tidak memantul (bouncing)
                              top: 0,
                              bottom: 0,
                              left: _isCheckInMode ? 0 : buttonWidth,
                              width: buttonWidth,
                              child: AnimatedContainer(
                                duration: const Duration(milliseconds: 300),
                                decoration: BoxDecoration(
                                  color: _isCheckInMode ? const Color(0xFF007AFF) : const Color(0xFFFF3B30),
                                  borderRadius: BorderRadius.circular(22),
                                  boxShadow: [
                                    BoxShadow(
                                      color: Colors.black.withValues(alpha: 0.1),
                                      blurRadius: 4,
                                      offset: const Offset(0, 2),
                                    )
                                  ],
                                ),
                              ),
                            ),
                            // Teks yang Mengapung Bersamaan di atas Slider
                            Row(
                              children: [
                                Expanded(
                                  child: GestureDetector(
                                    behavior: HitTestBehavior.opaque,
                                    onTap: () => setState(() => _isCheckInMode = true),
                                    child: Center(
                                      child: AnimatedDefaultTextStyle(
                                        duration: const Duration(milliseconds: 300),
                                        style: TextStyle(
                                          color: _isCheckInMode ? Colors.white : Colors.grey.shade500,
                                          fontWeight: FontWeight.w900, // Extra Bold
                                          fontSize: 11,
                                          letterSpacing: 0.5,
                                        ),
                                        child: const Text("CHECK IN"),
                                      ),
                                    ),
                                  ),
                                ),
                                Expanded(
                                  child: GestureDetector(
                                    behavior: HitTestBehavior.opaque,
                                    onTap: () => setState(() => _isCheckInMode = false),
                                    child: Center(
                                      child: AnimatedDefaultTextStyle(
                                        duration: const Duration(milliseconds: 300),
                                        style: TextStyle(
                                          color: !_isCheckInMode ? Colors.white : Colors.grey.shade500,
                                          fontWeight: FontWeight.w900, // Extra Bold
                                          fontSize: 11,
                                          letterSpacing: 0.5,
                                        ),
                                        child: const Text("CHECK OUT"),
                                      ),
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ],
                        );
                      },
                    ),
                  ),
                  const SizedBox(height: 12),
                  
                  // Location Selector UI (Tombol Buka Modal)
                  GestureDetector(
                    onTap: () => _showLocationSearchModal(context),
                    child: Container(
                      height: 36,
                      padding: const EdgeInsets.symmetric(horizontal: 16),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(18),
                        border: Border.all(color: Colors.grey.shade300),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withValues(alpha: 0.05),
                            blurRadius: 8,
                            offset: const Offset(0, 2),
                          )
                        ],
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            _selectedLocation,
                            style: const TextStyle(
                              fontSize: 11,
                              color: Colors.black87,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                          const SizedBox(width: 8),
                          const FaIcon(FontAwesomeIcons.chevronDown, size: 12, color: Colors.black54),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),

          // 3. FLOATING MAP ACTION BUTTONS (TARGET & HISTORY)
          Positioned(
            top: 180, // Mengambang dari atas, aman dari pemotongan layar bawah emulator
            right: 16, // Mengapung di pinggir KANAN layar
            child: Column(
              children: [
                // Tombol Ekstra/Menu Form (History Kehadiran)
                Container(
                  width: 44,
                  height: 44,
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.95),
                    shape: BoxShape.circle,
                    boxShadow: [
                      BoxShadow(color: Colors.black.withValues(alpha: 0.1), blurRadius: 10, offset: const Offset(0, 4))
                    ],
                  ),
                  child: const Center(
                    child: FaIcon(FontAwesomeIcons.clockRotateLeft, size: 20, color: Colors.black87), // Ikon History Riwayat
                  ),
                ),
                const SizedBox(height: 16),
                // Tombol Target GPS Lokasi Saya
                Container(
                  width: 44,
                  height: 44,
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.95),
                    shape: BoxShape.circle,
                    boxShadow: [
                      BoxShadow(color: Colors.black.withValues(alpha: 0.1), blurRadius: 10, offset: const Offset(0, 4))
                    ],
                  ),
                  child: const Center(
                    child: FaIcon(FontAwesomeIcons.crosshairs, size: 20, color: Colors.blueAccent),
                  ),
                ),
              ],
            ),
          ),

          // 4. BOTTOM FLOATING INFO BOARD (FULL GLASSMORPHISM)
          Positioned(
            bottom: 120, // Diperlebar agar ada celah cukup dengan Footer Navbar
            left: 20,
            right: 20,
            child: ConstrainedBox(
              constraints: BoxConstraints(
                maxHeight: MediaQuery.of(context).size.height * 0.42, // Memaksimalkan tinggi kartu sebatas 42% layar (Anti Overlap)
              ),
              child: ClipRRect(
                borderRadius: BorderRadius.circular(30),
                child: BackdropFilter(
                  filter: ImageFilter.blur(sigmaX: 15, sigmaY: 15), // Blur lebih kuat (seperti kaca asli)
                  child: Container(
                    padding: const EdgeInsets.all(24),
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.60), // Lebih transparan (dari 88% turun jadi 60%) agar jernih efek kacanya
                      borderRadius: BorderRadius.circular(30),
                      border: Border.all(color: Colors.white.withValues(alpha: 0.8), width: 1.5), // Garis luar kaca
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withValues(alpha: 0.05),
                          blurRadius: 20,
                          offset: const Offset(0, 10),
                        )
                      ],
                    ),
                    child: SingleChildScrollView(
                      child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      // GRID DATA KARYAWAN
                      Row(
                        children: [
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text("FULL NAME", style: TextStyle(fontSize: 9, fontWeight: FontWeight.bold, color: Colors.grey.shade500, letterSpacing: 1.2)),
                                const SizedBox(height: 4),
                                const Text("User", style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                              ],
                            ),
                          ),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text("EMPLOYEE NRP", style: TextStyle(fontSize: 9, fontWeight: FontWeight.bold, color: Colors.grey.shade500, letterSpacing: 1.2)),
                                const SizedBox(height: 4),
                                const Text("80210455", style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                              ],
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),
                      Row(
                        children: [
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text("DEPARTMENT", style: TextStyle(fontSize: 9, fontWeight: FontWeight.bold, color: Colors.grey.shade500, letterSpacing: 1.2)),
                                const SizedBox(height: 4),
                                const Text("HCGS", style: TextStyle(fontSize: 18, fontWeight: FontWeight.w900)),
                              ],
                            ),
                          ),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text("SITE", style: TextStyle(fontSize: 9, fontWeight: FontWeight.bold, color: Colors.grey.shade500, letterSpacing: 1.2)),
                                const SizedBox(height: 4),
                                Text(
                                  // Batasi kepanjangan teks site dengan elipsis kalau kelamaan
                                  _selectedLocation.contains(" ") ? _selectedLocation.split(" ").last : _selectedLocation, 
                                  style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                      
                      const SizedBox(height: 16),
                      // DEVICE SECURITY
                      Text("DEVICE SECURITY ID", style: TextStyle(fontSize: 9, fontWeight: FontWeight.bold, color: Colors.grey.shade500, letterSpacing: 1.2)),
                      const SizedBox(height: 4),
                      FittedBox(
                        alignment: Alignment.centerLeft,
                        fit: BoxFit.scaleDown,
                        child: Text(
                          "UUID: 8F2D-44B1-A092-CE9140B92X1", 
                          style: TextStyle(fontSize: 10, fontFamily: 'monospace', color: Color(0xFF34C759), fontWeight: FontWeight.w600, letterSpacing: 0.5),
                        ),
                      ),
                      
                      const SizedBox(height: 16),

                      // RED WARNING BOX
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                        decoration: BoxDecoration(
                          color: const Color(0xFFFF3B30).withValues(alpha: 0.1), // Sistem Biru dicampur merah sangat rendah
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: const Color(0xFFFF3B30).withValues(alpha: 0.3)),
                        ),
                        child: Row(
                          children: [
                            const FaIcon(FontAwesomeIcons.circleExclamation, color: Color(0xFFFF3B30), size: 16),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Text(
                                "Segala Macam Kecurangan akan terdeteksi di GPS dan Sistem",
                                style: TextStyle(
                                  color: const Color(0xFFFF3B30).withValues(alpha: 0.9), // Warna merah agak gelap sedikit
                                  fontSize: 10,
                                  fontWeight: FontWeight.bold,
                                  height: 1.4,
                                ),
                              ),
                            )
                          ],
                        ),
                      ),

                      const SizedBox(height: 24),

                      // KONFIRMASI BUTTON
                      ElevatedButton(
                        onPressed: () {},
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF007AFF), // Apple Blue
                          foregroundColor: Colors.white,
                          elevation: 0,
                          shadowColor: const Color(0xFF007AFF).withValues(alpha: 0.4),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                          padding: const EdgeInsets.symmetric(vertical: 20),
                        ),
                        child: const Text(
                          "KONFIRMASI",
                          style: TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.w900,
                            letterSpacing: 0.5,
                          ),
                        ),
                      ),
                      const SizedBox(height: 8), // Breathing space terbawah
                    ],
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    ],
      ),
    );
  }
}
