import { Request, Response } from 'express';
import { prisma } from '../utils/db';
import bcrypt from 'bcrypt';

export const getUsers = async (req: Request, res: Response): Promise<void> => {
  try {
    const mitraId = req.user?.mitra_id;
    const users = await prisma.users.findMany({
      where: {
        ...(mitraId && { mitra_id: mitraId }),
        role: { in: ['admin', 'admin_mitra'] }
      },
      include: {
        employee: {
          select: {
            full_name: true,
            position: true,
            division: true
          }
        },
        mitra: {
          select: {
            mitra_name: true
          }
        }
      }
    });
    res.json(users);
  } catch (error) {
    res.status(500).json({ error: 'Gagal mengambil data user', details: String(error) });
  }
};

export const updateUser = async (req: Request, res: Response): Promise<void> => {
  try {
    const nrp = req.params.nrp as string;
    const { role, is_active, password } = req.body;
    const mitraId = req.user?.mitra_id;

    // Ownership check
    if (mitraId) {
        const targetUser = await prisma.users.findUnique({ where: { nrp } });
        if (!targetUser || targetUser.mitra_id !== mitraId) {
            return res.status(403).json({ error: 'Anda tidak memiliki akses ke user ini' });
        }
    }

    const updateData: any = {};
    if (role) {
        // Admin Mitra cannot change roles
        if (mitraId) {
            return res.status(403).json({ error: 'Admin Mitra tidak diizinkan mengubah role akses' });
        }
        updateData.role = role;
    }
    if (is_active !== undefined) {
        // Robust boolean parsing
        updateData.is_active = (is_active === 'true' || is_active === true || is_active === 1 || is_active === '1');
    }
    if (password) {
      const salt = await bcrypt.genSalt(10);
      updateData.password_hash = await bcrypt.hash(password, salt);
    }

    const user = await prisma.users.update({
      where: { nrp },
      data: updateData
    });

    res.json({ message: 'User berhasil diperbarui', data: user });
  } catch (error) {
    res.status(500).json({ error: 'Gagal memperbarui user', details: String(error) });
  }
};

export const deleteUser = async (req: Request, res: Response): Promise<void> => {
  try {
    const nrp = req.params.nrp as string;

    await prisma.users.delete({
      where: { nrp }
    });

    res.json({ message: 'User berhasil dihapus secara permanen' });
  } catch (error) {
    res.status(500).json({ error: 'Gagal menghapus user', details: String(error) });
  }
};
