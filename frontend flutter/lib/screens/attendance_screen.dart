import 'dart:ui';
import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import 'package:intl/intl.dart';
import 'package:geolocator/geolocator.dart';
import 'package:frontend_flutter/constants.dart';
import '../services/auth_service.dart';
import '../services/attendance_service.dart';

class AttendanceScreen extends StatefulWidget {
  final Function(String?, bool, bool, {VoidCallback? onBack})? onLayoutChange;

  const AttendanceScreen({super.key, this.onLayoutChange});

  @override
  State<AttendanceScreen> createState() => _AttendanceScreenState();
}

class _AttendanceScreenState extends State<AttendanceScreen> {
  // User Data State
  Map<String, dynamic>? _userData;
  Map<String, dynamic>? _todayStatus;
  List<dynamic> _locations = [];
  Map<String, dynamic>? _selectedLocationData;
  bool _isLoading = false;
  
  final MapController _mapController = MapController();
  LatLng? _currentPosition;
  bool _isSearchingLocation = false;
  bool _hasShownNetworkError = false;
  
  // Attendance State
  bool _hasCheckedIn = false;
  bool _hasCheckedOut = false;
  String? _checkInTime;
  String? _checkOutTime;

  // FTW Process State
  int _currentStep = 0; // 0: Map, 1: Sleep Input, 2: Symptoms Form
  bool _symptomChoice = false; // false = TIDAK, true = YA

  // Sleep Input Form States
  String _sleepTime = "22:00";
  String _wakeUpTime = "05:00";
  bool _isMedication = false;
  bool _hasProblems = false;

  final List<String> _sleepTimes = ["18:00", "19:00", "20:00", "21:00", "22:00", "23:00", "24:00", "01:00", "02:00", "03:00"];
  final List<String> _wakeTimes = ["03:00", "04:00", "05:00", "06:00", "07:00", "08:00"];

  @override
  void initState() {
    super.initState();
    _loadUser();
  }

  Future<void> _loadUser() async {
    setState(() => _isLoading = true);
    try {
      final data = await authService.getUserData();
      print("👤 [User Debug] Data loaded: $data");
      
      // If user data can't be retrieved from storage, go to login
      if (data == null) {
        print("❌ [User Debug] No session found. Redirecting...");
        if (mounted) {
           await authService.logout();
           if (navigatorKey.currentState != null) {
              navigatorKey.currentState!.pushNamedAndRemoveUntil('/login', (route) => false);
           }
        }
        return;
      }

      final today = await attendanceService.getTodayStatus();
      
      // We removed the logout-on-null logic here because the Global Watchdog 
      // in ApiService already handles 401/403 errors correctly. 
      // If 'today' is null due to network issues, we just stay on the screen 
      // instead of deleting the session.

      if (mounted) {
        setState(() {
          _userData = data;
          _todayStatus = today;
          _hasCheckedIn = today != null && (today['status'] == 'checked_in' || today['status'] == 'completed');
          _hasCheckedOut = today != null && today['status'] == 'completed';
          
          if (today?['attendance']?['check_in'] != null) {
            final ci = today!['attendance']['check_in']['time_wita'];
            _checkInTime = _formatTimeFromBackend(ci);
          }
          if (today?['attendance']?['check_out'] != null) {
            final co = today!['attendance']['check_out']['time_wita'];
            _checkOutTime = _formatTimeFromBackend(co);
          }
        });

        // Load locations after user data is ready
        await _loadLocations();
        
        if (mounted) {
           setState(() => _isLoading = false);
           _initLocationService();
        }
      }
    } catch (e) {
      print("CRITICAL ERROR IN _loadUser: $e");
      // If any error occurs, we must turn off the spinner to prevent "stuck" state
      if (mounted) {
        setState(() => _isLoading = false);
        // Force logout/redirect only if it seems like an auth error (401/403)
        // Network errors will just stay on the screen but spinner is gone.
        if (e.toString().contains('401') || e.toString().contains('403')) {
           await authService.logout();
           if (navigatorKey.currentState != null) {
              navigatorKey.currentState!.pushNamedAndRemoveUntil('/login', (route) => false);
           }
        }
      }
    }
  }

  Future<void> _initLocationService() async {
    bool serviceEnabled;
    LocationPermission permission;

    serviceEnabled = await Geolocator.isLocationServiceEnabled();
    if (!serviceEnabled) return;

    permission = await Geolocator.checkPermission();
    if (permission == LocationPermission.denied) {
      permission = await Geolocator.requestPermission();
      if (permission == LocationPermission.denied) return;
    }
    
    if (permission == LocationPermission.deniedForever) return;

    if (mounted) setState(() => _isSearchingLocation = true);
    try {
      final pos = await Geolocator.getCurrentPosition(locationSettings: const LocationSettings(accuracy: LocationAccuracy.high));
      if (mounted) {
        setState(() {
          _currentPosition = LatLng(pos.latitude, pos.longitude);
        });
      }
    } finally {
      if (mounted) setState(() => _isSearchingLocation = false);
    }
  }

  Future<LatLng?> _getCurrentGPS() async {
    try {
      final pos = await Geolocator.getCurrentPosition(
        locationSettings: const LocationSettings(
          accuracy: LocationAccuracy.high,
          timeLimit: Duration(seconds: 5)
        )
      );
      
      if (pos.isMocked) {
        if (mounted) {
          _showStatusPopup(
            "Fake GPS Terdeteksi", 
            "Aplikasi mendeteksi penggunaan Fake GPS atau lokasi palsu. Harap matikan Fake GPS untuk melakukan absensi.", 
            Icons.gpp_maybe_rounded, 
            Colors.red
          );
        }
        return null; // Reject the fake location
      }

      return LatLng(pos.latitude, pos.longitude);
    } catch (e) {
      print("Error getting GPS: $e");
      return null;
    }
  }

  bool _isInsideGeofence(LatLng userPos, Map<String, dynamic> site) {
    if (site['polygon_coords'] != null && site['polygon_coords'].toString().trim().isNotEmpty) {
      try {
        final List<LatLng> polygon = site['polygon_coords'].toString().split('#').map((p) {
          final parts = p.split(',');
          return LatLng(double.parse(parts[0].trim()), double.parse(parts[1].trim()));
        }).toList();

        if (polygon.length >= 3) {
          return _pointInPolygon(userPos, polygon);
        }
      } catch (e) {
        print("Error parsing polygon_coords: $e");
      }
    }

    if (site['latitude'] == null || site['longitude'] == null) return false;
    
    final double siteLat = double.parse(site['latitude'].toString());
    final double siteLng = double.parse(site['longitude'].toString());
    final double radius = double.parse((site['radius_meters'] ?? 200).toString());
    
    final double distance = Geolocator.distanceBetween(
      userPos.latitude, userPos.longitude, 
      siteLat, siteLng
    );
    
    return distance <= radius;
  }

  bool _pointInPolygon(LatLng point, List<LatLng> polygon) {
    bool isInside = false;
    int j = polygon.length - 1;
    for (int i = 0; i < polygon.length; i++) {
        if (((polygon[i].longitude > point.longitude) != (polygon[j].longitude > point.longitude)) &&
            (point.latitude < (polygon[j].latitude - polygon[i].latitude) * (point.longitude - polygon[i].longitude) / (polygon[j].longitude - polygon[i].longitude) + polygon[i].latitude)) {
            isInside = !isInside;
        }
        j = i;
    }
    return isInside;
  }

  List<Polygon> _getPolygons() {
    if (_selectedLocationData?['polygon_coords'] == null || _selectedLocationData!['polygon_coords'].toString().trim().isEmpty) {
        return [];
    }
    try {
        List<LatLng> points = _selectedLocationData!['polygon_coords'].toString().split('#').map((p) {
            final parts = p.split(',');
            return LatLng(double.parse(parts[0].trim()), double.parse(parts[1].trim()));
        }).toList();
        
        return [
            Polygon(
                points: points,
                color: Colors.blue.withValues(alpha: 0.1),
                borderColor: Colors.blue,
                borderStrokeWidth: 2,
            )
        ];
    } catch (e) {
        return [];
    }
  }

  void _showOutsideGeofencePopup(LatLng userPos, Map<String, dynamic> site) {
    String message = "Anda berada di luar area geofence '${site['location_name']}'.";
    
    if (site['polygon_coords'] == null || site['polygon_coords'].toString().isEmpty) {
        final double siteLat = double.parse(site['latitude'].toString());
        final double siteLng = double.parse(site['longitude'].toString());
        final double radius = double.parse((site['radius_meters'] ?? 200).toString());
        final double distance = Geolocator.distanceBetween(userPos.latitude, userPos.longitude, siteLat, siteLng);
        message = "Anda berada ${distance.toStringAsFixed(0)}m dari lokasi '${site['location_name']}'.\n\nBatas maksimal adalah ${radius.toStringAsFixed(0)}m.";
    }
    
    _showStatusPopup(
      "Di Luar Area", 
      message, 
      Icons.location_off_rounded, 
      Colors.redAccent
    );
  }

  void _centerOnUser() async {
    if (mounted) setState(() => _isSearchingLocation = true);
    
    // Use bestForNavigation for accurate fix but shorter timeout
    try {
      final pos = await Geolocator.getCurrentPosition(
        locationSettings: const LocationSettings(
          accuracy: LocationAccuracy.best,
          timeLimit: Duration(seconds: 10),
        ),
      );
      
      if (mounted) {
        if (pos.isMocked) {
          setState(() => _isSearchingLocation = false);
          _showStatusPopup(
            "Fake GPS Terdeteksi", 
            "Aplikasi mendeteksi penggunaan Fake GPS atau lokasi palsu. Harap matikan Fake GPS untuk menggunakan aplikasi.", 
            Icons.gpp_maybe_rounded, 
            Colors.red
          );
          return;
        }

        setState(() {
          _isSearchingLocation = false;
          _currentPosition = LatLng(pos.latitude, pos.longitude);
        });
        _mapController.move(LatLng(pos.latitude, pos.longitude), 16.0);
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text("📍 Lokasi ditemukan!"), backgroundColor: Colors.green)
        );
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isSearchingLocation = false);
        _showNetworkErrorPopup();
      }
    }
  }

  void _showNetworkErrorPopup() {
    if (_hasShownNetworkError) return;
    _hasShownNetworkError = true;
    _showStatusPopup(
      "Gagal Menemukan Lokasi", 
      "Aplikasi tidak dapat mengunci GPS Anda. Pastikan GPS aktif, internet stabil, dan Anda berada di luar ruangan.", 
      Icons.location_off_rounded, 
      Colors.redAccent
    );
  }

  void _showLocationPicker() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => _LocationPickerModal(
        locations: _locations,
        selectedLocation: _selectedLocationData,
        onSelected: (loc) {
          setState(() {
            _selectedLocationData = loc;
            final newCenter = LatLng(double.parse(loc['latitude'].toString()), double.parse(loc['longitude'].toString()));
            _mapController.move(newCenter, 14.0);
          });
        },
      ),
    );
  }

  Future<void> _loadLocations() async {
    try {
      final List<dynamic> allLocations = await attendanceService.getLocations();
      print("🗺️ [Location Debug] Sites from API: ${allLocations.length}");
      
      // Get data from state
      final employeeData = _userData?['employee_data'];
      print("👤 [Location Debug] Employee Location ID: ${employeeData?['location_id']}");
      final positionData = employeeData?['position'];
      
      // 1. Check for "Allow Any" flags (Employee OR Position level)
      final bool empAllowAny = employeeData?['allow_any_location'] == true || employeeData?['allow_any_location'] == 1;
      final bool posAllowAny = positionData?['allow_any_location'] == true || positionData?['allow_any_location'] == 1;
      final bool isAdmin = _userData?['role'] == 'admin';
      
      if (empAllowAny || posAllowAny || isAdmin) {
        setState(() {
          _locations = allLocations;
          _setDefaultSelection();
        });
        return;
      }

      // 2. Collect all authorized IDs
      Set<int> authorizedIds = {};
      
      // From Primary Location
      if (employeeData?['location_id'] != null) {
        authorizedIds.add(int.parse(employeeData!['location_id'].toString()));
      }
      
      // From Position Allowed Sites
      final List<dynamic> posAllowed = positionData?['allowed_locations'] ?? [];
      for (var item in posAllowed) {
        if (item['location_id'] != null) {
          authorizedIds.add(int.parse(item['location_id'].toString()));
        }
      }
      
      // From Employee Specific Allowed Sites
      final List<dynamic> empAllowed = employeeData?['allowed_locations'] ?? [];
      for (var item in empAllowed) {
        if (item['location_id'] != null) {
          authorizedIds.add(int.parse(item['location_id'].toString()));
        }
      }
      
      // 3. Synchronization with Roster (Today's Assigned Site)
      final rosteredSiteName = _todayStatus?['work_location'];
      if (rosteredSiteName != null && rosteredSiteName.toString().isNotEmpty) {
        // Find ID from the master list by name
        final rosteredLoc = allLocations.firstWhere(
          (loc) => loc['location_name'].toString().toUpperCase() == rosteredSiteName.toString().toUpperCase(),
          orElse: () => null
        );
        if (rosteredLoc != null) {
          authorizedIds.add(int.parse(rosteredLoc['location_id'].toString()));
        }
      }

      // 4. Transform IDs back to Location Objects
      List<dynamic> filtered = [];
      if (isAdmin || empAllowAny || posAllowAny) {
        print("🔓 [Location Debug] Admin/Flexible access granted. Showing all sites.");
        filtered = allLocations;
      } else {
        filtered = allLocations.where((loc) {
          final locId = int.parse(loc['location_id'].toString());
          return authorizedIds.contains(locId);
        }).toList();
      }

      print("🎯 [Location Debug] Filtered Sites for UI: ${filtered.length} (Admin: $isAdmin)");

      setState(() {
        _locations = filtered;
        _setDefaultSelection();
      });
    } catch (e) {
      print('Error loading locations: $e');
    }
  }

  void _setDefaultSelection() {
    if (_locations.isNotEmpty) {
       // Priority: Roster site first, then Profile site, then first in list
       final rosteredSiteName = _todayStatus?['work_location'];
       final profileLocId = _userData?['employee_data']?['location_id'];
       
       dynamic target;
       if (rosteredSiteName != null) {
          target = _locations.firstWhere(
            (l) => l['location_name'].toString().toUpperCase() == rosteredSiteName.toString().toUpperCase(),
            orElse: () => null
          );
       }
       
       if (target == null && profileLocId != null) {
          target = _locations.firstWhere(
            (l) => l['location_id'] == profileLocId,
            orElse: () => null
          );
       }
       
       _selectedLocationData = target ?? _locations.first;
    } else {
      _selectedLocationData = null;
    }
  }

  String _formatTimeFromBackend(String? timeStr) {
    if (timeStr == null) return "--:--";
    try {
      final dt = DateTime.parse(timeStr);
      return DateFormat("HH:mm").format(dt);
    } catch (e) {
      if (timeStr.contains(":")) {
         final parts = timeStr.split(":");
         if (parts.length >= 2) {
           return "${parts[0].padLeft(2, '0')}:${parts[1].padLeft(2, '0')}";
         }
      }
      return "--:--";
    }
  }

  // Map Data
  final String _selectedLocation = "SITE PAMA SATUI";
  final LatLng _officeLocation = const LatLng(-3.65, 115.35); 

  void _triggerSleepForm() async {
    if (_hasCheckedIn) {
      _showAlreadyCheckedInPopup();
      return;
    }

    setState(() => _isSearchingLocation = true);
    final freshPos = await _getCurrentGPS();
    if (!mounted) return;
    if (freshPos == null) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text("Gagal mendapatkan lokasi GPS. Pastikan GPS aktif.")));
      setState(() => _isSearchingLocation = false);
      return;
    }

    if (_selectedLocationData != null && !_isInsideGeofence(freshPos, _selectedLocationData!)) {
      _showOutsideGeofencePopup(freshPos, _selectedLocationData!);
      setState(() => _isSearchingLocation = false);
      return;
    }
    setState(() => _isSearchingLocation = false);

    setState(() {
      _currentStep = 1;
    });
    // Update Layout: HIDE FOOTER
    widget.onLayoutChange?.call("FTW INPUT", true, false, onBack: () {
      setState(() {
        _currentStep = 0;
      });
      widget.onLayoutChange?.call(null, false, true);
    });
  }

  void _submitFinalSurvey() async {
    setState(() => _isLoading = true);
    
    final shiftName = _todayStatus?['roster']?['shift']?['shift_code'] ?? "Unknown";

    final ftwData = {
      "kehadiran_onsite": true,
      "unit_dioperasikan": "-",
      "pola_shift": "-",
      "shift": shiftName,
      "jam_tidur_12_jam": _sleepTime,
      "jam_tidur_36_jam": "-",
      "jam_bangun": _wakeUpTime,
      "konsumsi_obat": _isMedication,
      "punya_masalah": _hasProblems,
      "gejala_kesehatan": _symptomChoice,
      "status_ftw": _symptomChoice ? "FIT WITH NOTE" : "FIT"
    };

    final ftwResult = await attendanceService.submitFtwReport(ftwData);
    
    if (ftwResult != null) {
      final freshPos = await _getCurrentGPS();
      if (!mounted) return;
      if (freshPos == null) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text("Gagal mendapatkan lokasi GPS. Pastikan GPS aktif.")));
        setState(() => _isLoading = false);
        return;
      }

      // Removed old end-of-survey geofence check
      
      // Dynamic Shift Lookup: If roster is missing (common for testing/admins), fetch the first available shift
      int finalShiftId = 1;
      if (_todayStatus?['roster']?['shift_id'] != null) {
        finalShiftId = int.parse(_todayStatus!['roster']['shift_id'].toString());
      } else {
        try {
          final shifts = await attendanceService.getShifts();
          if (shifts.isNotEmpty) {
            finalShiftId = int.parse(shifts[0]['shift_id'].toString());
            print("🔄 [Shift Debug] Roster missing. Auto-selected available Shift ID: $finalShiftId");
          }
        } catch (e) {
          print("⚠️ [Shift Debug] Failed to fetch fallback shifts: $e");
        }
      }

      final checkInData = {
        "shift_id": finalShiftId,
        "lat": freshPos.latitude,
        "long": freshPos.longitude,
        "location_id": _selectedLocationData?['location_id'],
        "cp_location": "SBT",
        "work_location": _selectedLocationData?['location_name'] ?? "-",
        "photo_url": "https://pama.com/dummy.jpg"
      };

      final checkInResult = await attendanceService.checkIn(checkInData);
      
      if (checkInResult != null) {
        if (!mounted) return;
        _showSuccessPopup("Check-In Berhasil!", "Absensi Anda telah tercatat di server.", () {
          _loadUser(); // Refresh data
          setState(() {
            _currentStep = 0;
          });
          widget.onLayoutChange?.call(null, false, true);
        });
      } else {
        if (!mounted) return;
         // Show error from backend if possible
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text("Gagal melakukan Check-In. Kemungkinan Roster Anda belum terdaftar atau Shift tidak ditemukan."),
            backgroundColor: Colors.red,
          )
        );
      }
    } else {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text("Gagal mengirim Survey FTW."), backgroundColor: Colors.red));
    }
    
    setState(() => _isLoading = false);
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Center(child: CircularProgressIndicator());
    }
    if (_currentStep == 1) {
      return _buildSleepInputForm();
    }
    return _buildAttendanceDashboard();
  }

  // STEP 1: SLEEP INPUT & SYMPTOMS (SINGLE PAGE FTW)
  Widget _buildSleepInputForm() {
    return Container(
      color: const Color(0xFFF4F5F7),
      child: SingleChildScrollView(
        padding: const EdgeInsets.only(top: 130, left: 20, right: 20, bottom: 40),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
             _buildUserProfileCard(),
             const SizedBox(height: 16),
             _buildShiftCard(),
             const SizedBox(height: 16),
             _buildGridSection(
               title: "Jam Berapa Anda Tidur?",
               options: _sleepTimes,
               currentValue: _sleepTime,
               onChanged: (val) => setState(() => _sleepTime = val),
             ),
             const SizedBox(height: 24),
             _buildGridSection(
               title: "Jam berapa Anda bangun?",
               options: _wakeTimes,
               currentValue: _wakeUpTime,
               onChanged: (val) => setState(() => _wakeUpTime = val),
             ),
             const SizedBox(height: 24),
             _buildBinaryChoiceSection(
               title: "Apakah Anda Mengkonsumsi Obat yang Dapat Menyebabkan Kantuk?",
               value: _isMedication,
               onChanged: (val) => setState(() => _isMedication = val),
             ),
             const SizedBox(height: 24),
             _buildBinaryChoiceSection(
               title: "Apakah Anda Punya Masalah?",
               value: _hasProblems,
               onChanged: (val) => setState(() => _hasProblems = val),
             ),
             const SizedBox(height: 24),
             _buildSymptomsQuestionCard(),
             const SizedBox(height: 32),
             _buildSubmitButton(),
          ],
        ),
      ),
    );
  }

  // COMPONENTS
  Widget _buildSymptomsQuestionCard() {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16), border: Border.all(color: const Color(0xFF007AFF).withValues(alpha: 0.5), width: 1)),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          RichText(
            text: TextSpan(
              style: const TextStyle(color: Colors.black, fontSize: 13, fontWeight: FontWeight.w900, height: 1.4),
              children: [
                const TextSpan(text: "Apakah anda mengalami salah satu atau lebih dari gejala dibawah ini? "),
                TextSpan(text: "(Demam, batuk, flu/pilek, radang tenggorokan, sesak nafas, gangguan penciuman, gangguan pengecap, gangguan pencernaan) ", style: TextStyle(color: Colors.grey.shade600, fontWeight: FontWeight.w500)),
                const TextSpan(text: "*", style: TextStyle(color: Colors.red)),
              ],
            ),
          ),
          const SizedBox(height: 20),
          _buildSymptomsRadio("YA", _symptomChoice, () => setState(() => _symptomChoice = true)),
          const SizedBox(height: 12),
          _buildSymptomsRadio("TIDAK", !_symptomChoice, () => setState(() => _symptomChoice = false)),
        ],
      ),
    );
  }

  Widget _buildSubmitButton() {
     return ElevatedButton.icon(
      onPressed: _showSubmitConfirmation,
      icon: const FaIcon(FontAwesomeIcons.paperPlane, size: 14),
      label: const Text("KIRIM JAWABAN", style: TextStyle(fontSize: 14, fontWeight: FontWeight.w900, letterSpacing: 1.0)),
      style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF0052cc), foregroundColor: Colors.white, padding: const EdgeInsets.symmetric(vertical: 20), shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12))),
    );
  }

  void _showSubmitConfirmation() {
    _showGlassDialog(AlertDialog(
      title: const Text("Konfirmasi Survei", style: TextStyle(fontWeight: FontWeight.bold)),
      content: const Text("Apakah Anda yakin ingin mengirim survei FTW ini?"),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      actions: [
        TextButton(onPressed: () => Navigator.pop(context), child: const Text("BATAL", style: TextStyle(color: Colors.grey))),
        ElevatedButton(
          onPressed: () {
            Navigator.pop(context); // Tutup dialog
            _submitFinalSurvey(); // Lanjut submit
          },
          style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF0052cc), foregroundColor: Colors.white),
          child: const Text("KIRIM"),
        )
      ],
    ));
  }

  // UTILS (Visual Restoration)
  Widget _buildUserProfileCard() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16), boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 10)]),
      child: Row(
        children: [
          ClipRRect(borderRadius: BorderRadius.circular(50), child: Image.network(_userData?['employee_data']?['photo'] != null ? 'http://10.0.2.2:3000/uploads/profiles/${_userData!['employee_data']['photo']}' : "https://ui-avatars.com/api/?name=${_userData?['employee_data']?['full_name'] ?? 'User'}&background=random", width: 60, height: 60, fit: BoxFit.cover, errorBuilder: (c, e, s) => Container(width: 60, height: 60, color: Colors.grey.shade300, child: const Icon(Icons.person, color: Colors.grey)))),
          const SizedBox(width: 16),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(_userData?['employee_data']?['position']?['pos_name']?.toUpperCase() ?? "EMPLOYEE", style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w900, color: Color(0xFF007AFF))),
              Text(_userData?['employee_data']?['full_name'] ?? "Isnaeni Salsabela", style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900)),
              Text(_userData?['nrp'] ?? "AR260062", style: TextStyle(fontSize: 12, color: Colors.grey.shade500, fontWeight: FontWeight.bold)),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildShiftCard() {
    final shiftName = _todayStatus?['roster']?['shift']?['shift_code'] ?? "Tidak Ada Shift";
    
    // Safely extract and format UTC ISO 8601 Strings like "1970-01-01T07:17:00.000Z" to "07:17"
    String shiftStart = "--:--";
    String shiftEnd = "--:--";
    
    try {
      final rawStart = _todayStatus?['roster']?['shift']?['time_in_expected'];
      if (rawStart != null) shiftStart = rawStart.toString().substring(11, 16);
      
      final rawEnd = _todayStatus?['roster']?['shift']?['time_out_expected'];
      if (rawEnd != null) shiftEnd = rawEnd.toString().substring(11, 16);
    } catch (e) {
      print("Warning parsing time string: $e");
    }

    
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16), border: Border.all(color: Colors.blueAccent.withValues(alpha: 0.2))),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
           Container(padding: const EdgeInsets.symmetric(vertical: 8), decoration: BoxDecoration(color: const Color(0xFF007AFF).withValues(alpha: 0.1), borderRadius: BorderRadius.circular(8)), child: const Text("Shift Anda Hari Ini", textAlign: TextAlign.center, style: TextStyle(color: Color(0xFF007AFF), fontSize: 12, fontWeight: FontWeight.bold))),
           const SizedBox(height: 16),
           Row(
             mainAxisAlignment: MainAxisAlignment.spaceBetween,
             children: [
               const Text("Shift Aktif", style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Colors.black87)),
               Container(padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4), decoration: BoxDecoration(color: Colors.blueAccent, borderRadius: BorderRadius.circular(12)), child: Text(shiftName, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.white))),
             ],
           ),
           const SizedBox(height: 8),
           Row(
             mainAxisAlignment: MainAxisAlignment.spaceBetween,
             children: [
               const Text("Jam Kerja", style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Colors.black87)),
               Text("$shiftStart - $shiftEnd", style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
             ],
           ),
        ],
      ),
    );
  }


  Widget _buildGridSection({required String title, required List<String> options, required String currentValue, required ValueChanged<String> onChanged}) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(title, style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 13)),
        const SizedBox(height: 12),
        GridView.builder(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(crossAxisCount: 4, childAspectRatio: 2.2, crossAxisSpacing: 8, mainAxisSpacing: 8),
          itemCount: options.length,
          itemBuilder: (context, index) {
            final opt = options[index];
            final isSelected = opt == currentValue;
            return GestureDetector(
              onTap: () => onChanged(opt),
              child: Container(decoration: BoxDecoration(color: isSelected ? const Color(0xFF007AFF) : Colors.white, borderRadius: BorderRadius.circular(8), border: Border.all(color: isSelected ? const Color(0xFF007AFF) : Colors.grey.shade300, width: 1.5)), alignment: Alignment.center, child: Text(opt, textAlign: TextAlign.center, style: TextStyle(color: isSelected ? Colors.white : Colors.black, fontSize: 10, fontWeight: FontWeight.w900))),
            );
          },
        ),
        const SizedBox(height: 12),
      ],
    );
  }

  Widget _buildBinaryChoiceSection({required String title, required bool value, required ValueChanged<bool> onChanged}) {
    return Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Text(title, style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 13)),
        const SizedBox(height: 12),
        Row(children: [
          Expanded(child: _buildChoiceBtn("TIDAK", !value, () => onChanged(false))),
          const SizedBox(width: 12),
          Expanded(child: _buildChoiceBtn("IYA", value, () => onChanged(true))),
        ]),
      ]);
  }

  Widget _buildChoiceBtn(String label, bool isSelected, VoidCallback onTap) {
    return GestureDetector(onTap: onTap, child: Container(height: 48, decoration: BoxDecoration(color: isSelected ? const Color(0xFF005EB8) : Colors.white, borderRadius: BorderRadius.circular(12), border: Border.all(color: isSelected ? const Color(0xFF005EB8) : Colors.grey.shade300, width: 1.5)), alignment: Alignment.center, child: Text(label, style: TextStyle(color: isSelected ? Colors.white : Colors.grey.shade600, fontWeight: FontWeight.w900, fontSize: 14))));
  }

  Widget _buildSymptomsRadio(String label, bool isSelected, VoidCallback onTap) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        decoration: BoxDecoration(color: isSelected ? const Color(0xFFE3F2FD) : Colors.white, borderRadius: BorderRadius.circular(12), border: Border.all(color: isSelected ? const Color(0xFF007AFF) : Colors.grey.shade300, width: 1.5)),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(label, style: TextStyle(color: isSelected ? const Color(0xFF007AFF) : Colors.black, fontWeight: FontWeight.w900, fontSize: 13)),
            Container(width: 22, height: 22, decoration: BoxDecoration(shape: BoxShape.circle, border: Border.all(color: isSelected ? const Color(0xFF007AFF) : Colors.grey.shade400, width: 2), color: isSelected ? const Color(0xFF007AFF) : Colors.transparent), child: isSelected ? const Icon(Icons.check, size: 14, color: Colors.white) : null),
          ],
        ),
      ),
    );
  }

  // DASHBOARD
  Widget _buildAttendanceDashboard() {
    final LatLng center = _selectedLocationData != null 
        ? LatLng(double.parse(_selectedLocationData!['latitude'].toString()), double.parse(_selectedLocationData!['longitude'].toString()))
        : _officeLocation;
    final double radius = double.parse((_selectedLocationData?['radius_meters'] ?? 200).toString());
    final String locName = _selectedLocationData?['location_name'] ?? _selectedLocation;

    return Stack(
      children: [
        FlutterMap(
          mapController: _mapController,
          options: MapOptions(initialCenter: center, initialZoom: 14.0), 
          children: [
            TileLayer(
              urlTemplate: 'https://mt{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}',
              subdomains: const ['0', '1', '2', '3'],
              userAgentPackageName: 'id.pama.absensi.pama_absensi_app_v2',
              tileProvider: NetworkTileProvider(),
              errorImage: const NetworkImage('https://via.placeholder.com/256?text=Network+Error'),
              errorTileCallback: (tile, error, stackTrace) {
                if (!_hasShownNetworkError) {
                  WidgetsBinding.instance.addPostFrameCallback((_) {
                    _showNetworkErrorPopup();
                  });
                }
              },
            ), 
            PolygonLayer(
              polygons: _getPolygons(),
            ),
            CircleLayer(circles: <CircleMarker>[
              if (_selectedLocationData?['polygon_coords'] == null || _selectedLocationData!['polygon_coords'].toString().trim().isEmpty)
                CircleMarker(point: center, radius: radius, useRadiusInMeter: true, color: Colors.blue.withValues(alpha: 0.1), borderColor: Colors.blue, borderStrokeWidth: 1),
            ]),
            MarkerLayer(markers: [
              if (_currentPosition != null) ...[
                // Outer Pulse
                Marker(
                  point: _currentPosition!,
                  width: 50, height: 50,
                  child: Container(
                    decoration: BoxDecoration(
                      color: Colors.blue.withValues(alpha: 0.2),
                      shape: BoxShape.circle,
                    ),
                  ),
                ),
                // Inner Border
                Marker(
                  point: _currentPosition!,
                  width: 24, height: 24,
                  child: Container(
                    decoration: BoxDecoration(
                      color: Colors.white,
                      shape: BoxShape.circle,
                      boxShadow: [BoxShadow(color: Colors.black26, blurRadius: 4)]
                    ),
                    padding: const EdgeInsets.all(3),
                    child: Container(
                      decoration: const BoxDecoration(
                        color: Colors.blue,
                        shape: BoxShape.circle,
                      ),
                    ),
                  ),
                ),
              ],
              Marker(
                point: center, 
                width: 140, height: 80, 
                child: Column(
                  children: [
                    const FaIcon(FontAwesomeIcons.locationDot, color: Color(0xFFE91E63), size: 36), 
                    Container(
                      margin: const EdgeInsets.only(top: 4), 
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2), 
                      decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(4), boxShadow: [BoxShadow(color: Colors.black12, blurRadius: 4)]), 
                      child: Text(locName, style: const TextStyle(fontSize: 8, fontWeight: FontWeight.bold))
                    )
                  ]
                )
              )
            ])
          ]
        ),
        if (((_userData?['employee_data']?['allow_any_location'] == true || _userData?['employee_data']?['allow_any_location'] == 1)) || 
            ((_userData?['employee_data']?['position']?['allow_any_location'] == true || _userData?['employee_data']?['position']?['allow_any_location'] == 1)))
          Align(
            alignment: Alignment.topCenter, 
            child: SafeArea(
              child: Padding(
                padding: const EdgeInsets.only(top: 90), 
                child: GestureDetector(
                  onTap: _showLocationPicker,
                  child: Container(
                    height: 48, 
                    padding: const EdgeInsets.symmetric(horizontal: 20), 
                    decoration: BoxDecoration(
                      color: Colors.white, 
                      borderRadius: BorderRadius.circular(24), 
                      boxShadow: [
                        BoxShadow(color: Colors.black.withValues(alpha: 0.08), blurRadius: 15, offset: const Offset(0, 5))
                      ],
                      border: Border.all(color: Colors.grey.shade100)
                    ), 
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const FaIcon(FontAwesomeIcons.magnifyingGlass, size: 12, color: Colors.blueAccent),
                        const SizedBox(width: 12),
                        Text(
                          _selectedLocationData?['location_name'] ?? "Search Location...",
                          style: const TextStyle(color: Colors.black, fontSize: 14, fontWeight: FontWeight.w700),
                        ),
                        const SizedBox(width: 12),
                        const FaIcon(FontAwesomeIcons.caretDown, size: 14, color: Colors.blueAccent),
                      ],
                    ),
                  ),
                ),
              )
            )
          ),
        Positioned(
          bottom: 130, left: 20, right: 20,
          child: Stack(
            clipBehavior: Clip.none,
            children: [
              ClipRRect(borderRadius: BorderRadius.circular(30), child: BackdropFilter(filter: ImageFilter.blur(sigmaX: 10, sigmaY: 10), child: Container(padding: const EdgeInsets.all(18), decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.6), borderRadius: BorderRadius.circular(30), border: Border.all(color: Colors.white, width: 1.5)), child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [_buildInfoRow("FULL NAME", _userData?['full_name'] ?? "USER", "EMPLOYEE NRP", _userData?['nrp'] ?? "8021XXXX"), const SizedBox(height: 12), _buildInfoRow("DEPARTMENT", "HCGS", "SITE", _selectedLocationData?['location_name'] ?? _selectedLocation.split(" ").last), const SizedBox(height: 12), Text("ATTENDANCE STATUS", style: TextStyle(fontSize: 8, fontWeight: FontWeight.bold, color: Colors.grey.shade500)), Text(_hasCheckedOut ? "SUDAH CHECK-OUT" : (_hasCheckedIn ? "SUDAH CHECK-IN" : "MENUNGGU CHECK-IN"), style: TextStyle(fontWeight: FontWeight.w900, color: _hasCheckedOut ? Colors.orange : (_hasCheckedIn ? const Color(0xFF34C759) : Colors.blue))), const SizedBox(height: 12), _buildInfoRow("JAM CHECK-IN", _checkInTime ?? "--:--", "JAM CHECK-OUT", _checkOutTime ?? "--:--"), const SizedBox(height: 20), Row(children: [Expanded(child: _buildActionButton("CHECK IN", const Color(0xFF007AFF), _hasCheckedIn, _triggerSleepForm)), const SizedBox(width: 12), Expanded(child: _buildActionButton("CHECK OUT", const Color(0xFFFF9500), _hasCheckedOut, () { 
                if (_hasCheckedOut) {
                  _showAlreadyCheckedOutPopup();
                } else if (_hasCheckedIn) { 
                  _showCheckOutConfirmation(); 
                } else { 
                  _showDirectCheckOutWarning(); 
                } 
              }))])])))),
            ],
          ),
        ),
        // HIGH-VISIBILITY GPS BUTTON
        Positioned(
          bottom: 405, 
          right: 20,
          child: FloatingActionButton(
            onPressed: _centerOnUser,
            backgroundColor: Colors.white,
            elevation: 4,
            mini: true,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(25)),
            child: const Icon(Icons.my_location_rounded, color: Colors.blueAccent),
          ),
        ),
        // SEARCHING LOCATION OVERLAY
        if (_isSearchingLocation)
          Positioned.fill(
            child: BackdropFilter(
              filter: ImageFilter.blur(sigmaX: 5, sigmaY: 5),
              child: Container(
                color: Colors.black.withValues(alpha: 0.2),
                child: Center(
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 20),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(20),
                      boxShadow: [BoxShadow(color: Colors.black12, blurRadius: 20)]
                    ),
                    child: const Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        CircularProgressIndicator(strokeWidth: 3, color: Colors.blueAccent),
                        SizedBox(height: 16),
                        Text(
                          "Mencari Lokasi...",
                          style: TextStyle(fontWeight: FontWeight.w900, fontSize: 13, color: Colors.blueAccent),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ),
          ),
      ],
    );
  }

  Widget _buildInfoRow(String label1, String value1, String label2, String value2) {
    return Row(children: [Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(label1, style: const TextStyle(fontSize: 8, fontWeight: FontWeight.bold)), Text(value1, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w900))])), Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(label2, style: const TextStyle(fontSize: 8, fontWeight: FontWeight.bold)), Text(value2, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w900))]))]);
  }

  Widget _buildActionButton(String label, Color color, bool isCompleted, VoidCallback onTap) {
    return ElevatedButton(
      onPressed: onTap, // Always clickable to show status pop-up if isCompleted
      style: ElevatedButton.styleFrom(
        backgroundColor: isCompleted ? Colors.grey.shade300 : color, 
        foregroundColor: isCompleted ? Colors.grey.shade600 : Colors.white,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)), 
        padding: const EdgeInsets.symmetric(vertical: 14)
      ), 
      child: Text(label, style: const TextStyle(fontWeight: FontWeight.w900))
    );
  }

  void _showAlreadyCheckedInPopup() {
    _showStatusPopup("Anda Sudah Check-In", "Anda telah melakukan recorded Check-In hari ini pada pukul $_checkInTime.", Icons.check_circle_outline, Colors.blue);
  }

  void _showAlreadyCheckedOutPopup() {
    _showStatusPopup("Anda Sudah Check-Out", "Anda telah melakukan recorded Check-Out hari ini pada pukul $_checkOutTime.", Icons.outbox, Colors.orange);
  }

  void _showStatusPopup(String title, String message, IconData icon, Color color, {String? actionText, VoidCallback? onAction}) {
    _showGlassDialog(
      AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, color: color, size: 60),
            const SizedBox(height: 16),
            Text(title, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900)),
            const SizedBox(height: 12),
            Text(message, textAlign: TextAlign.center, style: const TextStyle(fontSize: 13, height: 1.5)),
            const SizedBox(height: 24),
            ElevatedButton(
              onPressed: () {
                Navigator.pop(context);
                if (onAction != null) onAction();
              },
              style: ElevatedButton.styleFrom(backgroundColor: color, minimumSize: const Size(double.infinity, 50), shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12))),
              child: Text(actionText ?? "MENGERTI", style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
            ),
          ],
        ),
      ),
    );
  }

  void _showSuccessPopup(String title, String message, VoidCallback onConfirm) {
    _showGlassDialog(AlertDialog(shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)), content: Column(mainAxisSize: MainAxisSize.min, children: [const Icon(Icons.check_circle, color: Colors.green, size: 60), const SizedBox(height: 16), Text(title, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900)), const SizedBox(height: 8), Text(message, textAlign: TextAlign.center), const SizedBox(height: 24), ElevatedButton(onPressed: () { Navigator.pop(context); onConfirm(); }, style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF007AFF), minimumSize: const Size(double.infinity, 50), shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12))), child: const Text("OK", style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)))])));
  }

  void _showCheckOutConfirmation() async {
     setState(() => _isSearchingLocation = true);
     final freshPos = await _getCurrentGPS();
     if (freshPos == null) {
       ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text("Gagal mendapatkan lokasi GPS. Pastikan GPS aktif.")));
       setState(() => _isSearchingLocation = false);
       return;
     }

     if (_selectedLocationData != null && !_isInsideGeofence(freshPos, _selectedLocationData!)) {
       _showOutsideGeofencePopup(freshPos, _selectedLocationData!);
       setState(() => _isSearchingLocation = false);
       return;
     }
     setState(() => _isSearchingLocation = false);

     _showGlassDialog(AlertDialog(
       title: const Text("Konfirmasi Check-Out"), 
       content: const Text("Apakah Anda yakin ingin melakukan Check-Out sekarang?"), 
       actions: [
         TextButton(onPressed: () => Navigator.pop(context), child: const Text("BATAL")), 
         TextButton(
           onPressed: () async { 
             Navigator.pop(context); 
             setState(() => _isLoading = true);
             
             final checkOutData = {
               "lat": freshPos.latitude,
               "long": freshPos.longitude,
               "location_id": _selectedLocationData?['location_id'],
               "photo_url": "https://pama.com/dummy_checkout.jpg"
             };

             final result = await attendanceService.checkOut(checkOutData);
             if (result != null) {
               _showSuccessPopup("Check-Out Berhasil!", "Sampai jumpa besok! Shift Anda telah selesai.", () {
                  _loadUser(); // Refresh UI status
               });
             } else {
               ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text("Gagal melakukan Check-Out.")));
             }
             setState(() => _isLoading = false);
           }, 
           child: const Text("CHECK OUT")
         )
       ]
      ));
  }

  void _showDirectCheckOutWarning() {
    _showGlassDialog(AlertDialog(title: const Text("Warning"), content: const Text("Anda belum check in. Yakin lanjut checkout?"), actions: [TextButton(onPressed: () => Navigator.pop(context), child: const Text("BATAL")), TextButton(onPressed: () { Navigator.pop(context); setState(() { _hasCheckedOut = true; _checkOutTime = DateFormat("HH:mm").format(DateTime.now()); }); }, child: const Text("YAKIN"))]));
  }

  void _showGlassDialog(Widget content) {
    showGeneralDialog(context: context, barrierDismissible: true, barrierLabel: '', barrierColor: Colors.black.withValues(alpha: 0.4), transitionDuration: const Duration(milliseconds: 400), pageBuilder: (context, anim1, anim2) => content, transitionBuilder: (context, anim1, anim2, child) { return BackdropFilter(filter: ImageFilter.blur(sigmaX: 10.0 * anim1.value, sigmaY: 10.0 * anim1.value), child: FadeTransition(opacity: anim1, child: ScaleTransition(scale: anim1.drive(Tween(begin: 0.95, end: 1.0).chain(CurveTween(curve: Curves.easeOutCubic))), child: child))); });
  }
}

// Searchable Location Modal
class _LocationPickerModal extends StatefulWidget {
  final List<dynamic> locations;
  final Map<String, dynamic>? selectedLocation;
  final Function(Map<String, dynamic>) onSelected;

  const _LocationPickerModal({
    required this.locations,
    this.selectedLocation,
    required this.onSelected,
  });

  @override
  State<_LocationPickerModal> createState() => _LocationPickerModalState();
}

class _LocationPickerModalState extends State<_LocationPickerModal> {
  late List<dynamic> _filteredLocations;
  final TextEditingController _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _filteredLocations = widget.locations;
  }

  void _filter(String query) {
    setState(() {
      _filteredLocations = widget.locations
          .where((loc) => loc['location_name']
              .toString()
              .toLowerCase()
              .contains(query.toLowerCase()))
          .toList();
    });
  }

  @override
  Widget build(BuildContext context) {
    return DraggableScrollableSheet(
      initialChildSize: 0.6,
      minChildSize: 0.4,
      maxChildSize: 0.9,
      builder: (context, scrollController) => Container(
        decoration: const BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
        ),
        child: Column(
          children: [
            const SizedBox(height: 12),
            Container(width: 40, height: 4, decoration: BoxDecoration(color: Colors.grey.shade300, borderRadius: BorderRadius.circular(2))),
            const SizedBox(height: 20),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 20),
              child: TextField(
                controller: _searchController,
                onChanged: _filter,
                decoration: InputDecoration(
                  hintText: "Search Location...",
                  prefixIcon: const Icon(Icons.search, color: Colors.blueAccent),
                  filled: true,
                  fillColor: Colors.grey.shade100,
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: BorderSide.none),
                  contentPadding: const EdgeInsets.symmetric(vertical: 16),
                ),
              ),
            ),
            const SizedBox(height: 12),
            Expanded(
              child: ListView.builder(
                controller: scrollController,
                itemCount: _filteredLocations.length,
                itemBuilder: (context, index) {
                  final loc = _filteredLocations[index] as Map<String, dynamic>;
                  final isSelected = loc['location_id'] == widget.selectedLocation?['location_id'];
                  return ListTile(
                    contentPadding: const EdgeInsets.symmetric(horizontal: 24, vertical: 4),
                    leading: Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(color: isSelected ? Colors.blue.withValues(alpha: 0.1) : Colors.grey.shade100, shape: BoxShape.circle),
                      child: Icon(Icons.location_on, color: isSelected ? Colors.blue : Colors.grey, size: 20),
                    ),
                    title: Text(loc['location_name'] ?? "Unknown", style: TextStyle(fontWeight: isSelected ? FontWeight.bold : FontWeight.normal, color: isSelected ? Colors.blue : Colors.black)),
                    subtitle: Text("Geofence: ${loc['radius_meters']}m"),
                    trailing: isSelected ? const Icon(Icons.check_circle, color: Colors.blue) : null,
                    onTap: () {
                      widget.onSelected(loc);
                      Navigator.pop(context);
                    },
                  );
                },
              ),
            ),
          ],
        ),
      ),
    );
  }
}

