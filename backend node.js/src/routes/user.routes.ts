import { Router } from 'express';
import { getUsers, updateUser } from '../controllers/user.controller';
import { authenticateToken, authorizeRoles } from '../middlewares/auth.middleware';

const router = Router();

// Endpoint manajemen user (Hanya Admin)
router.use(authenticateToken);
router.use(authorizeRoles('admin')); 

router.get('/', getUsers);
router.patch('/:nrp', updateUser);

export default router;
