import { Router } from 'express';
import { getRosters, createRoster, bulkCreateRoster, deleteRoster } from '../controllers/roster.controller';
import { authenticateToken, authorizeRoles } from '../middlewares/auth.middleware';

const router = Router();

router.use(authenticateToken); 
router.use(authorizeRoles('admin', 'employee', 'safety', 'csr'));

router.get('/', getRosters);
router.post('/', createRoster);
router.post('/bulk', bulkCreateRoster);
router.delete('/:id', deleteRoster);

export default router;
