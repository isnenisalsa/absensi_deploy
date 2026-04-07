import { Router } from 'express';
import { 
  getShifts, createShift, updateShift, deleteShift,
  getDepartments, createDepartment, updateDepartment, deleteDepartment,
  getDivisions, createDivision, updateDivision, deleteDivision,
  getPositions, createPosition, updatePosition, deletePosition,
  getLocations, updateLocation, createLocation, deleteLocation
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

// Geofence / Work Location Endpoints
router.get('/locations', getLocations);
router.post('/locations', createLocation);
router.put('/locations/:id', updateLocation);
router.delete('/locations/:id', deleteLocation);


export default router;
