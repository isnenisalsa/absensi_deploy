import { Router } from 'express';
import { getDashboardStats } from '../controllers/stats.controller';
import { authenticateToken } from '../middlewares/auth.middleware';

const router = Router();

// Endpoint dashboard statistik (terproteksi JWT)
router.get('/dashboard', authenticateToken, getDashboardStats);

export default router;
