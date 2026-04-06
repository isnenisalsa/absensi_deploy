import 'dotenv/config';
import express from 'express';
import cors from 'cors';
import helmet from 'helmet';
import morgan from 'morgan';
import rateLimit from 'express-rate-limit';
import { errorHandler } from './middlewares/error.middleware';

// Import Routes
import authRoutes from './routes/auth.routes';
import attendanceRoutes from './routes/attendance.routes';
import masterRoutes from './routes/master.routes';
import employeeRoutes from './routes/employee.routes';
import rosterRoutes from './routes/roster.routes';
import userRoutes from './routes/user.routes';

const app = express();

// Konfigurasi Rate Limiter Global (Batasan 100 hit per 15 menit)
const limiter = rateLimit({
  windowMs: 15 * 60 * 1000, 
  max: 100, 
  message: { error: 'Terlalu banyak request, silakan coba lagi dalam 15 menit.' }
});

// Middleware dasar & Security
app.use(helmet()); // Mencegah XSS & Hide Express header
app.use(morgan('dev')); // Logger traffic
app.use(cors()); // Allow Origin Flutter/Web
app.use(express.json()); // Allow Parsing JSON
app.use(express.urlencoded({ extended: true }));
app.use(limiter); // Pasang limiter ke seluruh network

// Rute Basic Cek Server
app.get('/', (req, res) => {
  res.send('API Backend Absensi Berjalan Lancar!');
});

// Pendaftaran Global Rooutes
app.use('/api/auth', authRoutes);
app.use('/api/attendance', attendanceRoutes);
app.use('/api/master', masterRoutes);
app.use('/api/employees', employeeRoutes);
app.use('/api/rosters', rosterRoutes);
app.use('/api/users', userRoutes);

// Penanganan Route NotFound
app.use((req, res, next) => {
  res.status(404).json({ error: 'Endpoint tidak ditemukan' });
});

// Error handling mask 
app.use(errorHandler);

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
  console.log(`🚀 Server API berjalan di http://localhost:${PORT}`);
});
