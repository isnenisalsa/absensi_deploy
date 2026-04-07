import { Router } from 'express';
import { getMitras, createMitra, updateMitra, deleteMitra } from '../controllers/mitra.controller';

const router = Router();

router.get('/', getMitras);
router.post('/', createMitra);
router.put('/:id', updateMitra);
router.delete('/:id', deleteMitra);

export default router;
