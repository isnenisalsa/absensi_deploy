import 'dart:io';
import 'dart:ui';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import 'package:image_picker/image_picker.dart';
import 'package:dio/dio.dart';
import '../services/auth_service.dart';
import 'login_screen.dart';

class ProfileScreen extends StatefulWidget {
  final VoidCallback? onPhotoUpdated;
  const ProfileScreen({super.key, this.onPhotoUpdated});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  Map<String, dynamic>? _userData;
  bool _isLoadingPhoto = false;

  // Settings States
  bool _pushNotifications = true;
  bool _emailNotifications = true;

  @override
  void initState() {
    super.initState();
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

  Future<void> _handleLogout() async {
    showGeneralDialog(
      context: context,
      barrierDismissible: true,
      barrierLabel: '',
      barrierColor: Colors.black.withValues(alpha: 0.4),
      transitionDuration: const Duration(milliseconds: 300),
      pageBuilder: (dialogContext, anim1, anim2) {
        return AlertDialog(
          backgroundColor: Colors.white.withValues(alpha: 0.9),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(24),
          ),
          title: const Text(
            "Log Out",
            style: TextStyle(fontWeight: FontWeight.w900),
          ),
          content: const Text("Apakah Anda yakin ingin keluar dari akun ini?"),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(dialogContext),
              child: const Text(
                "Batal",
                style: TextStyle(
                  color: Colors.blue,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ),
            TextButton(
              onPressed: () async {
                Navigator.pop(dialogContext);
                await authService.logout();
                if (!mounted) return;
                Navigator.pushAndRemoveUntil(
                  this.context,
                  MaterialPageRoute(builder: (context) => const LoginScreen()),
                  (route) => false,
                );
              },
              child: const Text(
                "Keluar",
                style: TextStyle(
                  color: Colors.red,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ),
          ],
        );
      },
      transitionBuilder: (context, anim1, anim2, child) {
        return BackdropFilter(
          filter: ImageFilter.blur(
            sigmaX: 10 * anim1.value,
            sigmaY: 10 * anim1.value,
          ),
          child: FadeTransition(
            opacity: anim1,
            child: ScaleTransition(
              scale: anim1.drive(Tween(begin: 0.95, end: 1.0)),
              child: child,
            ),
          ),
        );
      },
    );
  }

  Future<void> _pickAndUploadImage() async {
    final ImagePicker picker = ImagePicker();

    // Show selection dialog
    final ImageSource? source = await showModalBottomSheet<ImageSource>(
      context: context,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) => SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Padding(
              padding: EdgeInsets.symmetric(vertical: 20),
              child: Text(
                "Ganti Foto Profil",
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.w900,
                  letterSpacing: -0.5,
                ),
              ),
            ),
            ListTile(
              leading: const Icon(Icons.camera_alt_rounded, color: Colors.blue),
              title: const Text(
                "Kamera",
                style: TextStyle(fontWeight: FontWeight.bold),
              ),
              onTap: () => Navigator.pop(context, ImageSource.camera),
            ),
            ListTile(
              leading: const Icon(
                Icons.photo_library_rounded,
                color: Colors.green,
              ),
              title: const Text(
                "Galeri",
                style: TextStyle(fontWeight: FontWeight.bold),
              ),
              onTap: () => Navigator.pop(context, ImageSource.gallery),
            ),
            const SizedBox(height: 10),
          ],
        ),
      ),
    );

    if (source == null) return;

    final XFile? image = await picker.pickImage(
      source: source,
      imageQuality: 70,
      maxWidth: 800,
    );

    if (image == null) return;

    setState(() => _isLoadingPhoto = true);

    try {
      final photoUrl = await authService.uploadProfilePhoto(image.path);

      if (photoUrl != null) {
        // Update local state and SharedPreferences
        final newUser = Map<String, dynamic>.from(_userData!);
        newUser['employee_data']['photo_profile'] = photoUrl;

        await authService.updateUserData(newUser);

        if (mounted) {
          setState(() {
            _userData = newUser;
            _isLoadingPhoto = false;
          });

          // Notify parent (Header)
          widget.onPhotoUpdated?.call();

          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text("Foto profil berhasil diperbarui!"),
              backgroundColor: Colors.green,
            ),
          );
        }
      } else {
        throw Exception("Gagal mengunggah foto");
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isLoadingPhoto = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text("Error: $e"), backgroundColor: Colors.red),
        );
      }
    }
  }

  Future<void> _handleChangePassword() async {
    final TextEditingController oldPassController = TextEditingController();
    final TextEditingController newPassController = TextEditingController();
    final TextEditingController confirmPassController = TextEditingController();
    bool isSaving = false;

    showGeneralDialog(
      context: context,
      barrierDismissible: true,
      barrierLabel: '',
      barrierColor: Colors.black.withValues(alpha: 0.4),
      transitionDuration: const Duration(milliseconds: 300),
      pageBuilder: (dialogContext, anim1, anim2) {
        return StatefulBuilder(
          builder: (context, setDialogState) {
            return AlertDialog(
              backgroundColor: Colors.white.withValues(alpha: 0.9),
              surfaceTintColor: Colors.transparent,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(24),
              ),
              title: const Text(
                "Ganti Password",
                style: TextStyle(fontWeight: FontWeight.w900, fontSize: 18),
              ),
              content: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    _buildPasswordField(oldPassController, "Password Lama"),
                    const SizedBox(height: 12),
                    _buildPasswordField(newPassController, "Password Baru"),
                    const SizedBox(height: 12),
                    _buildPasswordField(
                      confirmPassController,
                      "Konfirmasi Password Baru",
                    ),
                  ],
                ),
              ),
              actions: [
                TextButton(
                  onPressed: () => Navigator.pop(dialogContext),
                  child: const Text(
                    "Batal",
                    style: TextStyle(
                      color: Colors.grey,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
                ElevatedButton(
                  onPressed:
                      isSaving
                          ? null
                          : () async {
                            final oldP = oldPassController.text.trim();
                            final newP = newPassController.text.trim();
                            final confirmP = confirmPassController.text.trim();

                            if (oldP.isEmpty ||
                                newP.isEmpty ||
                                confirmP.isEmpty) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                const SnackBar(
                                  content: Text("Semua field harus diisi"),
                                  backgroundColor: Colors.orange,
                                ),
                              );
                              return;
                            }

                            if (newP != confirmP) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                const SnackBar(
                                  content: Text(
                                    "Konfirmasi password tidak cocok",
                                  ),
                                  backgroundColor: Colors.red,
                                ),
                              );
                              return;
                            }

                            if (newP.length < 6) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                const SnackBar(
                                  content: Text(
                                    "Password baru minimal 6 karakter",
                                  ),
                                  backgroundColor: Colors.red,
                                ),
                              );
                              return;
                            }

                            setDialogState(() => isSaving = true);
                            try {
                              await authService.changePassword(oldP, newP);
                              if (mounted) {
                                Navigator.pop(dialogContext);
                                ScaffoldMessenger.of(this.context).showSnackBar(
                                  const SnackBar(
                                    content: Text("Password berhasil diganti"),
                                    backgroundColor: Colors.green,
                                  ),
                                );
                              }
                            } catch (e) {
                              String errorMsg = "Terjadi kesalahan";
                              if (e is DioException &&
                                  e.response?.data != null) {
                                errorMsg =
                                    e.response?.data['error'] ?? errorMsg;
                              }
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(
                                  content: Text(errorMsg),
                                  backgroundColor: Colors.red,
                                ),
                              );
                            } finally {
                              setDialogState(() => isSaving = false);
                            }
                          },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.blue,
                    foregroundColor: Colors.white,
                    elevation: 0,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    padding: const EdgeInsets.symmetric(
                      horizontal: 20,
                      vertical: 12,
                    ),
                  ),
                  child:
                      isSaving
                          ? const SizedBox(
                            width: 20,
                            height: 20,
                            child: CircularProgressIndicator(
                              color: Colors.white,
                              strokeWidth: 2,
                            ),
                          )
                          : const Text(
                            "Simpan",
                            style: TextStyle(fontWeight: FontWeight.bold),
                          ),
                ),
              ],
            );
          },
        );
      },
      transitionBuilder: (context, anim1, anim2, child) {
        return BackdropFilter(
          filter: ImageFilter.blur(
            sigmaX: 10 * anim1.value,
            sigmaY: 10 * anim1.value,
          ),
          child: FadeTransition(
            opacity: anim1,
            child: ScaleTransition(
              scale: anim1.drive(Tween(begin: 0.95, end: 1.0)),
              child: child,
            ),
          ),
        );
      },
    );
  }

  Widget _buildPasswordField(TextEditingController controller, String label) {
    return TextField(
      controller: controller,
      obscureText: true,
      style: const TextStyle(fontSize: 14),
      decoration: InputDecoration(
        labelText: label,
        labelStyle: TextStyle(
          fontSize: 12,
          fontWeight: FontWeight.bold,
          color: Colors.grey.shade600,
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: Colors.grey.shade300),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: Colors.blue, width: 2),
        ),
        contentPadding: const EdgeInsets.symmetric(
          horizontal: 16,
          vertical: 16,
        ),
        fillColor: Colors.grey.shade50,
        filled: true,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    // Extract nested data for cleaner access
    final employeeData = _userData?['employee_data'];
    final nrp = _userData?['nrp'] ?? "AR260062";
    final fullName = employeeData?['full_name'] ?? "Isnaeni Salsabela";
    final role = _userData?['role'] ?? "Employee";
    final photoPath = employeeData?['photo_profile'];

    final positionName = employeeData?['position']?['pos_name'] ?? "-";
    final departmentName =
        employeeData?['division']?['department']?['dept_name'] ?? "-";
    final divisionName = employeeData?['division']?['div_name'] ?? "-";
    final locationName = employeeData?['location']?['location_name'] ?? "-";
    final homeBase = employeeData?['default_work_location'] ?? "-";

    return SingleChildScrollView(
      padding: const EdgeInsets.only(top: 130, bottom: 140),
      child: Column(
        children: [
          // 1. HEADER SECTION
          _buildProfileHeader(fullName, nrp, role, photoPath),
          const SizedBox(height: 32),

          // 2. EMPLOYEE DETAILS GROUPS
          _buildSectionHeader("Profile Pengguna"),
          _buildInfoGroup([
            _buildDataRow("Jabatan", positionName),
            _buildDataRow("Departemen", departmentName),
            _buildDataRow("Divisi", divisionName),
          ]),

          const SizedBox(height: 12),
          _buildSectionHeader("LOKASI & PENEMPATAN"),
          _buildInfoGroup([_buildDataRow("Site", locationName)]),

          const SizedBox(height: 32),
          _buildSectionHeader("PENGATURAN"),
          _buildInfoGroup([
            _buildSettingTile(
              icon: FontAwesomeIcons.solidBell,
              label: "Push Notifikasi",
              value: _pushNotifications,
              onChanged: (v) => setState(() => _pushNotifications = v),
            ),
            _buildSettingTile(
              icon: FontAwesomeIcons.at,
              label: "Email Notifikasi",
              value: _emailNotifications,
              onChanged: (v) => setState(() => _emailNotifications = v),
            ),
            const Divider(height: 1, indent: 20, endIndent: 20),
            _buildActionTile(
              icon: FontAwesomeIcons.lock,
              label: "Ganti Password",
              onTap: _handleChangePassword,
            ),
          ]),

          const SizedBox(height: 40),

          // 3. LOGOUT ACTION
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20),
            child: Material(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              child: InkWell(
                onTap: _handleLogout,
                borderRadius: BorderRadius.circular(16),
                child: Container(
                  padding: const EdgeInsets.symmetric(vertical: 18),
                  decoration: BoxDecoration(
                    border: Border.all(
                      color: Colors.red.withValues(alpha: 0.1),
                      width: 1.5,
                    ),
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      FaIcon(
                        FontAwesomeIcons.arrowRightFromBracket,
                        color: Colors.red.shade700,
                        size: 18,
                      ),
                      const SizedBox(width: 12),
                      Text(
                        "LOG OUT AKUN",
                        style: TextStyle(
                          color: Colors.red.shade700,
                          fontWeight: FontWeight.w900,
                          fontSize: 13,
                          letterSpacing: 1.2,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildProfileHeader(
    String name,
    String nrp,
    String role,
    String? photoPath,
  ) {
    // Role Color Coding
    final bool isAdmin = role.toLowerCase() == "admin";
    final Color roleColor = isAdmin
        ? const Color(0xFF007AFF)
        : const Color(0xFF34C759);

    // Construct full URL if path exists
    final String baseUrl = kIsWeb
        ? 'http://localhost:3000'
        : (Platform.isAndroid
              ? 'http://10.0.2.2:3000'
              : 'http://localhost:3000');
    final String photoUrl = photoPath != null
        ? (photoPath.startsWith('http') ? photoPath : '$baseUrl$photoPath')
        : "https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&q=80&w=300&h=300";

    return Column(
      children: [
        Stack(
          children: [
            GestureDetector(
              onTap: _pickAndUploadImage,
              child: Container(
                width: 120,
                height: 120,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  border: Border.all(color: Colors.white, width: 4),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.1),
                      blurRadius: 20,
                      offset: const Offset(0, 10),
                    ),
                  ],
                  image: DecorationImage(
                    image: NetworkImage(photoUrl),
                    fit: BoxFit.cover,
                  ),
                ),
                child: _isLoadingPhoto
                    ? Container(
                        decoration: BoxDecoration(
                          color: Colors.black.withValues(alpha: 0.3),
                          shape: BoxShape.circle,
                        ),
                        child: const Center(
                          child: CircularProgressIndicator(
                            color: Colors.white,
                            strokeWidth: 2,
                          ),
                        ),
                      )
                    : null,
              ),
            ),
            Positioned(
              right: 0,
              bottom: 0,
              child: IgnorePointer(
                child: Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: const Color(0xFF2C2E33),
                    shape: BoxShape.circle,
                    border: Border.all(color: Colors.white, width: 2),
                  ),
                  child: const FaIcon(
                    FontAwesomeIcons.camera,
                    color: Colors.white,
                    size: 12,
                  ),
                ),
              ),
            ),
          ],
        ),
        const SizedBox(height: 16),
        Text(
          name,
          style: const TextStyle(
            fontSize: 22,
            fontWeight: FontWeight.w900,
            letterSpacing: -0.5,
          ),
        ),
        const SizedBox(height: 4),
        Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Text(
              nrp,
              style: TextStyle(
                fontSize: 14,
                color: Colors.grey.shade600,
                fontWeight: FontWeight.bold,
                letterSpacing: 1.0,
              ),
            ),
            const SizedBox(width: 8),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
              decoration: BoxDecoration(
                color: roleColor.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(6),
                border: Border.all(color: roleColor.withValues(alpha: 0.2)),
              ),
              child: Text(
                role.toUpperCase(),
                style: TextStyle(
                  color: roleColor,
                  fontSize: 8,
                  fontWeight: FontWeight.w900,
                  letterSpacing: 0.5,
                ),
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildSectionHeader(String title) {
    return Padding(
      padding: const EdgeInsets.only(left: 24, bottom: 8),
      child: Align(
        alignment: Alignment.centerLeft,
        child: Text(
          title,
          style: TextStyle(
            fontSize: 10,
            fontWeight: FontWeight.w900,
            color: Colors.grey.shade500,
            letterSpacing: 1.2,
          ),
        ),
      ),
    );
  }

  Widget _buildInfoGroup(List<Widget> children) {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.03),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(children: children),
    );
  }

  Widget _buildDataRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.all(16.0),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 100,
            child: Text(
              label,
              style: const TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.bold,
                color: Colors.black54,
              ),
            ),
          ),
          const Text(":  ", style: TextStyle(fontWeight: FontWeight.bold)),
          Expanded(
            child: Text(
              value,
              style: const TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.w900,
                color: Colors.black,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSettingTile({
    required dynamic icon,
    required String label,
    required bool value,
    required ValueChanged<bool> onChanged,
  }) {
    return Padding(
      padding: const EdgeInsets.all(16.0),
      child: Row(
        children: [
          FaIcon(icon, size: 16, color: Colors.grey.shade600),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              label,
              style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
            ),
          ),
          Transform.scale(
            scale: 0.8,
            child: Switch.adaptive(
              value: value,
              onChanged: onChanged,
              activeTrackColor: const Color(0xFF34C759),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildActionTile({
    required dynamic icon,
    required String label,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 16.0),
        child: Row(
          children: [
            FaIcon(icon, size: 16, color: Colors.grey.shade600),
            const SizedBox(width: 12),
            Expanded(
              child: Text(
                label,
                style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
              ),
            ),
            Icon(
              Icons.chevron_right_rounded,
              size: 20,
              color: Colors.grey.shade400,
            ),
          ],
        ),
      ),
    );
  }
}
