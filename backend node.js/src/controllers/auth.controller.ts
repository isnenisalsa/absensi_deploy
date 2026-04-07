import { Request, Response } from 'express';
import bcrypt from 'bcrypt';
import { prisma } from '../utils/db';
import { generateToken } from '../utils/jwt';

export const login = async (req: Request, res: Response): Promise<void> => {
  try {
    const { nrp, password } = req.body;
    const ipAddress = req.ip || req.socket.remoteAddress;
    const userAgent = req.headers['user-agent'];

    if (!nrp || !password) {
      res.status(400).json({ error: 'NRP dan password harus diisi.' });
      return;
    }

    const user = await prisma.users.findUnique({
      where: { nrp },
      include: { employee: true }
    });

    if (!user) {
      res.status(401).json({ error: 'NRP tidak terdaftar.' });
      return;
    }

    if (!user.is_active) {
      res.status(403).json({ error: 'Akun Anda telah dinonaktifkan.' });
      return;
    }

    const isMatch = await bcrypt.compare(password, user.password_hash);
    if (!isMatch) {
      res.status(401).json({ error: 'Password yang dimasukkan salah.' });
      return;
    }

    const token = generateToken({ 
      nrp: user.nrp, 
      role: user.role,
      mitra_id: user.mitra_id 
    });

    // Store Session in Database
    await prisma.user_sessions.create({
      data: {
        nrp: user.nrp,
        token: token,
        ip_address: ipAddress || 'unknown',
        user_agent: userAgent || 'unknown',
        expires_at: new Date(Date.now() + 24 * 60 * 60 * 1000) // Match JWT 24h
      }
    });

    res.status(200).json({
      message: 'Login berhasil.',
      token,
      user: {
        nrp: user.nrp,
        role: user.role,
        mitra_id: user.mitra_id,
        employee_data: user.employee
      }
    });

  } catch (error) {
    console.error('Error in login auth:', error);
    res.status(500).json({ error: 'Terjadi kesalahan pada server: ' + (error instanceof Error ? error.message : String(error)) });
  }
};

export const logout = async (req: Request, res: Response): Promise<void> => {
  try {
    const authHeader = req.headers['authorization'];
    const token = authHeader && authHeader.split(' ')[1];

    if (token) {
      // Invalidate session in DB
      await prisma.user_sessions.updateMany({
        where: { token: token },
        data: { is_active: false }
      });
    }

    res.status(200).json({ message: 'Logout berhasil.' });
  } catch (error) {
    console.error('Logout error:', error);
    res.status(500).json({ error: 'Gagal melakukan logout.' });
  }
};
