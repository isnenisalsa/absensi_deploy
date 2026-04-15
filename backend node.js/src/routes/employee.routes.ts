import { Router } from 'express';
import { getEmployees, createEmployee, updateEmployee, deleteEmployee } from '../controllers/employee.controller';
import { authenticateToken, authorizeRoles } from '../middlewares/auth.middleware';

const router = Router();

// Endpoint untuk manajemen karyawan hanya boleh diakses admin.
// Jika ingin semua token bisa akses sementara (untuk test UI admin yang nggak login sepenuhnya Node), sementara bisa dimatikan role admin-nya.
// Tapi karena token kita sudah set role, kita bungkus dengan authorizeRoles('admin')
// Tapi tunggu, apakah Laravel Front End /login nge-set role ke api_token session? Iya, asumsikan auth middleware lolos.

// Menggunakan authenticateToken di semua rute karyawan
router.use(authenticateToken);

// Update foto profil (Bisa diakses semua role yang login)
import { uploadProfile } from '../middlewares/upload.middleware';
import { updateProfilePhoto } from '../controllers/employee.controller';
router.post('/update-photo', uploadProfile.single('photo'), updateProfilePhoto);

// Hanya admin yang bisa kelola Karyawan
router.use(authorizeRoles('admin', 'admin_mitra'));

router.get('/', getEmployees);
router.post('/', createEmployee);
router.put('/:id', updateEmployee);
router.delete('/:id', deleteEmployee);

export default router;
