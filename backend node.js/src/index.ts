import 'dotenv/config';
import express from 'express';
import cors from 'cors';
import helmet from 'helmet';
import morgan from 'morgan';
import rateLimit from 'express-rate-limit';
import hpp from 'hpp';
import { errorHandler } from './middlewares/error.middleware';

// Import Routes
import authRoutes from './routes/auth.routes';
import attendanceRoutes from './routes/attendance.routes';
import masterRoutes from './routes/master.routes';
import employeeRoutes from './routes/employee.routes';
import rosterRoutes from './routes/roster.routes';
import userRoutes from './routes/user.routes';
import mitraRoutes from './routes/mitra.routes';
import statsRoutes from './routes/stats.routes';

const app = express();

// Konfigurasi Rate Limiter Global (Batasan 100 hit per 15 menit)
const limiter = rateLimit({
  windowMs: 15 * 60 * 1000, 
  max: 5000, 
  message: { error: 'Terlalu banyak request, silakan coba lagi dalam 15 menit.' }
});

// Middleware dasar & Security
app.use(helmet({
  contentSecurityPolicy: true, // Mencegah injection script luar
  crossOriginEmbedderPolicy: true,
  crossOriginOpenerPolicy: true,
  crossOriginResourcePolicy: { policy: "cross-origin" },
  dnsPrefetchControl: true,
  frameguard: { action: "deny" }, // Mencegah Clickjacking (Burp Suite sering test ini)
  hidePoweredBy: true,
  hsts: true, // Paksa HTTPS
  ieNoOpen: true,
  noSniff: true, // Mencegah MIME sniffing
  originAgentCluster: true,
  permittedCrossDomainPolicies: true,
  referrerPolicy: { policy: "no-referrer" },
  xssFilter: true, // Mencegah XSS di browser lama
})); 

app.use(morgan('dev')); // Logger traffic

// Konfigurasi CORS yang lebih ketat
app.use(cors({
  origin: '*', // Untuk tahap dev masih *, nanti ganti ke domain produksi
  methods: ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'],
  allowedHeaders: ['Content-Type', 'Authorization', 'X-Requested-With'],
  credentials: true,
}));

app.use(express.json({ limit: '10kb' })); // Batasi payload JSON untuk cegah DoS
app.use(express.urlencoded({ extended: true, limit: '10kb' }));
app.use(hpp()); // Mencegah HTTP Parameter Pollution (?id=1&id=2)
app.use(limiter); // Pasang limiter ke seluruh network

// Serve static files for uploads
app.use('/uploads', express.static('uploads'));

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
app.use('/api/master/mitras', mitraRoutes);
app.use('/api/stats', statsRoutes);

// Penanganan Route NotFound
app.use((req, res, next) => {
  console.log(`❌ 404 Error: Endpoint tidak ditemukan untuk request: ${req.method} ${req.originalUrl}`);
  res.status(404).json({ error: 'Endpoint tidak ditemukan' });
});

// Error handling mask 
app.use(errorHandler);

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
  console.log(`🚀 Server API berjalan di http://localhost:${PORT}`);
});
