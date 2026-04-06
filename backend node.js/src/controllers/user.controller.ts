import { Request, Response } from 'express';
import { prisma } from '../utils/db';
import bcrypt from 'bcrypt';

export const getUsers = async (req: Request, res: Response): Promise<void> => {
  try {
    const users = await prisma.users.findMany({
      include: {
        employee: {
          select: {
            full_name: true,
            position: true,
            division: true
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

    const updateData: any = {};
    if (role) updateData.role = role;
    if (is_active !== undefined) updateData.is_active = is_active;
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
