import { Request, Response, NextFunction } from 'express';
import { verifyToken, TokenPayload } from '../utils/jwt';
import { prisma } from '../utils/db';

// Menambahkan property user ke object Request milik Express
declare global {
  namespace Express {
    interface Request {
      user?: TokenPayload;
    }
  }
}

export const authenticateToken = async (req: Request, res: Response, next: NextFunction): Promise<void> => {
  const authHeader = req.headers['authorization'];
  const token = authHeader && authHeader.split(' ')[1]?.trim();

  if (!token) {
    console.warn(`[Auth Debug] Missing token header. IP: ${req.ip}`);
    res.status(401).json({ error: 'Akses ditolak. Token tidak disediakan.' });
    return;
  }

  const decoded = verifyToken(token);
  if (!decoded) {
    console.warn(`[Auth Debug] JWT Verification failed (Invalid or Expired). IP: ${req.ip}`);
    res.status(403).json({ error: 'Token tidak valid atau sudah kedaluwarsa.' });
    return;
  }

  // Check if session exists and is active in database
  const session = await prisma.user_sessions.findFirst({
    where: {
      token: token,
      nrp: decoded.nrp,
      is_active: true,
      expires_at: {
        gt: new Date()
      }
    }
  });

  if (!session) {
    console.warn(`[Auth Debug] Session not found or inactive in DB for NRP: ${decoded.nrp}. Token: ${token.substring(0, 15)}...`);
    res.status(401).json({ error: 'Sesi Anda telah berakhir atau tidak valid. Silakan login kembali.' });
    return;
  }

  req.user = decoded;
  next();
};

export const authorizeRoles = (...roles: string[]) => {
  return (req: Request, res: Response, next: NextFunction): void => {
    if (!req.user || !roles.includes(req.user.role)) {
      res.status(403).json({ error: 'Anda tidak memiliki hak akses (role) ke resource ini.' });
      return;
    }
    next();
  };
};

export const restrictToSuperAdmin = (req: Request, res: Response, next: NextFunction): void => {
  if (!req.user) {
    res.status(401).json({ error: 'Unauthorized' });
    return;
  }
  
  // Super Admin is admin role AND has no mitra_id
  if (req.user.role === 'admin' && !req.user.mitra_id) {
    next();
    return;
  }
  
  res.status(403).json({ error: 'Akses Ditolak: Hanya Super Administrator yang dapat melakukan tindakan ini.' });
};
