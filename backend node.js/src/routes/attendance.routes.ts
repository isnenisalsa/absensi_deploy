import { Router } from 'express';
import { checkIn, checkOut, getMyAttendance, getAllFtw } from '../controllers/attendance.controller';
import { authenticateToken } from '../middlewares/auth.middleware';
import { validate } from '../middlewares/validation.middleware';
import { checkInSchema, checkOutSchema } from '../utils/schemas';

const router = Router();

// Harus terautentikasi (kirim Bearer Token di Header Header)
router.use(authenticateToken);

// Mengirimkan Absensi Masuk (Check-In)
router.post('/check-in', validate(checkInSchema), checkIn);

// Mengirimkan Absensi Keluar (Check-Out)
router.post('/check-out', validate(checkOutSchema), checkOut);

// Sejarah Transaksi Absensi user tersebut
router.get('/history', getMyAttendance);

// Data FTW untuk Admin
router.get('/ftw', getAllFtw);

export default router;
