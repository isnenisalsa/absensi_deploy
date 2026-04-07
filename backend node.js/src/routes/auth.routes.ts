import { Router } from 'express';
import { login, logout } from '../controllers/auth.controller';
import { validate } from '../middlewares/validation.middleware';
import { loginSchema } from '../utils/schemas';
import { authenticateToken } from '../middlewares/auth.middleware';

const router = Router();

// Endpoint Login yang generate JWT token dengan validasi zOD
router.post('/login', validate(loginSchema), login);
router.post('/logout', authenticateToken, logout);

export default router;
