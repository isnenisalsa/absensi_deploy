import { Router } from 'express';
import { getUsers, updateUser, deleteUser } from '../controllers/user.controller';
import { authenticateToken, authorizeRoles } from '../middlewares/auth.middleware';

const router = Router();

// Endpoint manajemen user (Hanya Admin)
router.use(authenticateToken);
router.use(authorizeRoles('admin', 'admin_mitra')); 

router.get('/', getUsers);
router.patch('/:nrp', updateUser);
router.delete('/:nrp', deleteUser);

export default router;
