import { Router } from 'express';
import { 
  getShifts, createShift, updateShift, deleteShift,
  getDepartments, createDepartment, updateDepartment, deleteDepartment,
  getDivisions, createDivision, updateDivision, deleteDivision,
  getPositions, createPosition, updatePosition, deletePosition,
  getMitraKerja, updateMitraKerja, createMitraKerja, deleteMitraKerja,
  getDistricts, createDistrict, updateDistrict, deleteDistrict
} from '../controllers/master.controller';

const router = Router();

// Shifts
router.get('/shifts', getShifts);
router.post('/shifts', createShift);
router.put('/shifts/:id', updateShift);
router.delete('/shifts/:id', deleteShift);

// Departments
router.get('/departments', getDepartments);
router.post('/departments', createDepartment);
router.put('/departments/:id', updateDepartment);
router.delete('/departments/:id', deleteDepartment);

// Divisions
router.get('/divisions', getDivisions);
router.post('/divisions', createDivision);
router.put('/divisions/:id', updateDivision);
router.delete('/divisions/:id', deleteDivision);

// Positions
router.get('/positions', getPositions);
router.post('/positions', createPosition);
router.put('/positions/:id', updatePosition);
router.delete('/positions/:id', deletePosition);

// Geofence Endpoints
router.get('/mitra-kerja', getMitraKerja);
router.post('/mitra-kerja', createMitraKerja);
router.put('/mitra-kerja/:id', updateMitraKerja);
router.delete('/mitra-kerja/:id', deleteMitraKerja);

// Districts
router.get('/districts', getDistricts);
router.post('/districts', createDistrict);
router.put('/districts/:id', updateDistrict);
router.delete('/districts/:id', deleteDistrict);

export default router;
