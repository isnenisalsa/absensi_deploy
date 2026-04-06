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

// Hanya admin yang bisa kelola
router.use(authorizeRoles('admin'));

router.get('/', getEmployees);
router.post('/', createEmployee);
router.put('/:id', updateEmployee);
router.delete('/:id', deleteEmployee);

export default router;
