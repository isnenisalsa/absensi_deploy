import { Router } from 'express';
import { checkIn, checkOut, getMyAttendance, getAllFtw, getTodayStatus, submitFtwReport, uploadAttendancePhotoController } from '../controllers/attendance.controller';
import { authenticateToken, authorizeRoles } from '../middlewares/auth.middleware';
import { validate } from '../middlewares/validation.middleware';
import { checkInSchema, checkOutSchema, ftwReportSchema } from '../utils/schemas';
import { uploadAttendancePhoto } from '../middlewares/upload.middleware';

const router = Router();

// Harus terautentikasi (kirim Bearer Token di Header Header)
router.use(authenticateToken);

// Upload Foto Bukti Absensi (Camera Only)
// Field name: "photo" — Flutter mengirim file dengan key ini
router.post('/upload-photo', uploadAttendancePhoto.single('photo'), uploadAttendancePhotoController);

// Mengirimkan Absensi Masuk (Check-In)
router.post('/check-in', validate(checkInSchema), checkIn);

// Mengirimkan Absensi Keluar (Check-Out)
router.post('/check-out', validate(checkOutSchema), checkOut);

// Mengirimkan Laporan Fit To Work (FTW)
router.post('/ftw', validate(ftwReportSchema), submitFtwReport);

// Data FTW untuk Admin
router.get('/ftw', authorizeRoles('admin', 'admin_mitra'), getAllFtw);

// Status Absensi Hari Ini (Roster & History)
router.get('/today-status', getTodayStatus);

// Riwayat Absensi (History) — admin, admin_mitra, dan employee sendiri
router.get('/history', getMyAttendance);

export default router;
