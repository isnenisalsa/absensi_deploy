import multer from 'multer';
import path from 'path';
import fs from 'fs';

// Pastikan folder uploads ada
const uploadDir = 'uploads/profiles';
if (!fs.existsSync(uploadDir)) {
    fs.mkdirSync(uploadDir, { recursive: true });
}

// Pastikan folder uploads/attendance ada
const attendanceUploadDir = 'uploads/attendance';
if (!fs.existsSync(attendanceUploadDir)) {
    fs.mkdirSync(attendanceUploadDir, { recursive: true });
}

// Konfigurasi penyimpanan
const storage = multer.diskStorage({
  destination: (req, file, cb) => {
    cb(null, uploadDir);
  },
  filename: (req, file, cb) => {
    const nrp = req.user?.nrp || 'unknown';
    const uniqueSuffix = Date.now() + '-' + Math.round(Math.random() * 1E9);
    cb(null, `profile-${nrp}-${uniqueSuffix}${path.extname(file.originalname)}`);
  }
});

// Filter file (hanya gambar)
const fileFilter = (req: any, file: any, cb: any) => {
  if (file.mimetype.startsWith('image/')) {
    cb(null, true);
  } else {
    cb(new Error('Hanya file gambar yang diperbolehkan!'), false);
  }
};

export const uploadProfile = multer({
  storage: storage,
  limits: {
    fileSize: 5 * 1024 * 1024 // Batasan 5MB
  },
  fileFilter: fileFilter
});

// --- ATTENDANCE PHOTO UPLOAD CONFIG ---
const attendanceStorage = multer.diskStorage({
  destination: (req, file, cb) => {
    cb(null, attendanceUploadDir);
  },
  filename: (req, file, cb) => {
    const nrp = req.user?.nrp || 'unknown';
    const uniqueSuffix = Date.now() + '-' + Math.round(Math.random() * 1E9);
    cb(null, `attendance-${nrp}-${uniqueSuffix}.jpg`);
  }
});

export const uploadAttendancePhoto = multer({
  storage: attendanceStorage,
  limits: {
    fileSize: 5 * 1024 * 1024 // Batasan 5MB
  },
  fileFilter: fileFilter // Sama: hanya gambar
});

// --- EXCEL UPLOAD CONFIG ---
const excelStorage = multer.memoryStorage();
const excelFilter = (req: any, file: any, cb: any) => {
  if (file.mimetype === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' || 
      file.mimetype === 'application/vnd.ms-excel' ||
      file.originalname.endsWith('.xlsx') || 
      file.originalname.endsWith('.xls')) {
    cb(null, true);
  } else {
    cb(new Error('Hanya file Excel (.xlsx atau .xls) yang diperbolehkan!'), false);
  }
};

export const uploadExcel = multer({
  storage: excelStorage,
  limits: {
    fileSize: 10 * 1024 * 1024 // 10MB limit for Excel
  },
  fileFilter: excelFilter
});
