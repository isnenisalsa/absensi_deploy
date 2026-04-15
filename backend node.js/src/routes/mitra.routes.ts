import { Router } from 'express';
import { getMitras, createMitra, updateMitra, deleteMitra, bulkDestroyMitras } from '../controllers/mitra.controller';
import { authenticateToken } from '../middlewares/auth.middleware';
import { uploadExcel } from '../middlewares/upload.middleware';

const router = Router();

router.use(authenticateToken);

router.get('/', getMitras);
router.post('/', createMitra);
router.put('/:id', updateMitra);
router.delete('/bulk', bulkDestroyMitras);

router.delete('/:id', deleteMitra);

export default router;
