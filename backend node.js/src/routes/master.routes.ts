import { Router } from 'express';
import multer from 'multer';
import { 
  getShifts, createShift, updateShift, deleteShift, bulkDestroyShifts, 
  getDepartments, createDepartment, updateDepartment, deleteDepartment, bulkDeleteDepartments,
  getDivisions, createDivision, updateDivision, deleteDivision, bulkDeleteDivisions,
  getPositions, createPosition, updatePosition, deletePosition, bulkDeletePositions,
  getLocations, updateLocation, createLocation, deleteLocation
} from '../controllers/master.controller';
import { authenticateToken, authorizeRoles, restrictToSuperAdmin } from '../middlewares/auth.middleware';
import { uploadExcel } from '../middlewares/upload.middleware';

const upload = multer();
const router = Router();

// Global protection for master data
router.use(authenticateToken);
router.use(authorizeRoles('admin', 'admin_mitra', 'user', 'employee'));

// Logging Middleware khusus Master Data
router.use((req, res, next) => {
  console.log(`[Master API] Request: ${req.method} ${req.originalUrl}`);
  next();
});



// Bulk Delete Routes
router.delete('/shifts/bulk', restrictToSuperAdmin, bulkDestroyShifts);
router.delete('/departments/bulk', restrictToSuperAdmin, bulkDeleteDepartments);
router.delete('/divisions/bulk', restrictToSuperAdmin, bulkDeleteDivisions);
router.delete('/positions/bulk', restrictToSuperAdmin, bulkDeletePositions);

// Shifts
router.get('/shifts', getShifts);
router.post('/shifts', restrictToSuperAdmin, createShift);
router.put('/shifts/:id', restrictToSuperAdmin, updateShift);
router.delete('/shifts/:id', restrictToSuperAdmin, deleteShift);

// Departments
router.get('/departments', getDepartments);
router.post('/departments', restrictToSuperAdmin, createDepartment);
router.put('/departments/:id', restrictToSuperAdmin, updateDepartment);
router.delete('/departments/:id', restrictToSuperAdmin, deleteDepartment);

// Divisions
router.get('/divisions', getDivisions);
router.post('/divisions', restrictToSuperAdmin, createDivision);
router.put('/divisions/:id', restrictToSuperAdmin, updateDivision);
router.delete('/divisions/:id', restrictToSuperAdmin, deleteDivision);

// Positions
router.get('/positions', getPositions);
router.post('/positions', restrictToSuperAdmin, createPosition);
router.put('/positions/:id', restrictToSuperAdmin, updatePosition);
router.delete('/positions/:id', restrictToSuperAdmin, deletePosition);

// Geofence / Work Location Endpoints
router.get('/locations', getLocations);
router.post('/locations', restrictToSuperAdmin, createLocation);
router.put('/locations/:id', restrictToSuperAdmin, updateLocation);
router.delete('/locations/:id', restrictToSuperAdmin, deleteLocation);

export default router;
