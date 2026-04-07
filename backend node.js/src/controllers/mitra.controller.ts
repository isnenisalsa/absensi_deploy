import { Request, Response } from 'express';
import bcrypt from 'bcrypt';
import { prisma } from '../utils/db';

export const getMitras = async (req: Request, res: Response): Promise<void> => {
  try {
    const mitras = await prisma.mitras.findMany({
      include: {
        _count: {
          select: { employees: true }
        }
      }
    });
    res.json(mitras);
  } catch (err) {
    res.status(500).json({ error: 'Gagal mengambil data mitra' });
  }
};

export const createMitra = async (req: Request, res: Response): Promise<void> => {
  try {
    const { mitra_name, contact_pama } = req.body;

    if (!mitra_name) {
      res.status(400).json({ error: 'Nama mitra wajib diisi' });
      return;
    }

    // Use transaction to ensure both Mitra and Admin User are created
    const result = await prisma.$transaction(async (tx) => {
      // 1. Create Mitra
      const mitra = await tx.mitras.create({
        data: {
          mitra_name,
          contact_pama
        }
      });

      // 2. Create Admin Account for this Mitra
      // Standardize NRP for Mitra Admin: ADM-[MITRA_ID]-[SLUGIFIED_NAME]
      const slug = mitra_name.toLowerCase().replace(/[^a-z0-9]/g, '-').substring(0, 10);
      const nrpAdmin = `ADM-${mitra.mitra_id}-${slug}`;
      const defaultPassword = 'Mitra123!';
      const hashedPassword = await bcrypt.hash(defaultPassword, 10);

      const adminUser = await tx.users.create({
        data: {
          nrp: nrpAdmin,
          password_hash: hashedPassword,
          role: 'admin', // Use admin role, filtering will be based on mitra_id
          mitra_id: mitra.mitra_id,
          is_active: true
        }
      });

      return { mitra, adminUser, defaultPassword };
    });

    res.status(201).json({
      message: 'Mitra dan akun admin berhasil dibuat',
      data: result.mitra,
      admin: {
        nrp: result.adminUser.nrp,
        password: result.defaultPassword
      }
    });

  } catch (err) {
    console.error('Error creating mitra:', err);
    res.status(500).json({ error: 'Gagal membuat mitra', details: String(err) });
  }
};

export const updateMitra = async (req: Request, res: Response): Promise<void> => {
  try {
    const { id } = req.params;
    const { mitra_name, contact_pama } = req.body;

    const updated = await prisma.mitras.update({
      where: { mitra_id: Number(id) },
      data: {
        mitra_name,
        contact_pama
      }
    });

    res.json({ message: 'Data mitra berhasil diperbarui', data: updated });
  } catch (err) {
    res.status(500).json({ error: 'Gagal memperbarui data mitra' });
  }
};

export const deleteMitra = async (req: Request, res: Response): Promise<void> => {
  try {
    const { id } = req.params;
    
    // Check if there are employees linked to this mitra
    const employeeCount = await prisma.employees.count({
      where: { mitra_id: Number(id) }
    });

    if (employeeCount > 0) {
      res.status(400).json({ error: 'Tidak dapat menghapus mitra yang masih memiliki karyawan' });
      return;
    }

    await prisma.mitras.delete({
      where: { mitra_id: Number(id) }
    });

    res.json({ message: 'Mitra berhasil dihapus' });
  } catch (err) {
    res.status(500).json({ error: 'Gagal menghapus mitra' });
  }
};
