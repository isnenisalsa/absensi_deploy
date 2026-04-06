import { Router } from 'express';
import { getRosters, createRoster, bulkCreateRoster, deleteRoster } from '../controllers/roster.controller';
import { authenticateToken } from '../middlewares/auth.middleware';

const router = Router();

router.use(authenticateToken); // Protect roster routes

router.get('/', getRosters);
router.post('/', createRoster);
router.post('/bulk', bulkCreateRoster);
router.delete('/:id', deleteRoster);

export default router;
