import { Request, Response } from 'express';
import bcrypt from 'bcrypt';
import { prisma } from '../utils/db';
import * as ExcelJS from 'exceljs';

export const getMitras = async (req: Request, res: Response): Promise<void> => {
  try {
    const mitraId = req.user?.mitra_id;
    const mitras = await prisma.mitras.findMany({
      where: mitraId ? { mitra_id: Number(mitraId) } : {},
      include: {
        users: {
          where: { role: 'admin_mitra' },
          include: { employee: { select: { full_name: true } } }
        },
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
    if (req.user?.mitra_id) {
        res.status(403).json({ error: 'Anda tidak diizinkan menambah data mitra baru.' });
        return;
    }
    const { mitra_name, contact_pama, admin_name } = req.body;

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
      const defaultPassword = `P4ssw0rd4ria${nrpAdmin}`;
      const hashedPassword = await bcrypt.hash(defaultPassword, 10);

      const adminUser = await tx.users.create({
        data: {
          nrp: nrpAdmin,
          password_hash: hashedPassword,
          role: 'admin_mitra', 
          mitra_id: mitra.mitra_id,
          is_active: true
        }
      });

      // 3. Create Employee record for the admin identity
      await tx.employees.create({
        data: {
          nrp: nrpAdmin,
          full_name: admin_name || `Admin ${mitra_name}`,
          mitra_id: mitra.mitra_id,
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
    if (req.user?.mitra_id) {
        res.status(403).json({ error: 'Anda tidak diizinkan mengubah data mitra.' });
        return;
    }
    const { id } = req.params;
    const { mitra_name, contact_pama, admin_name } = req.body;

    const result = await prisma.$transaction(async (tx) => {
      const updated = await tx.mitras.update({
        where: { mitra_id: Number(id) },
        data: {
          mitra_name,
          contact_pama
        }
      });

      if (admin_name) {
        const adminUser = await tx.users.findFirst({
          where: { mitra_id: Number(id), role: 'admin_mitra' }
        });

        if (adminUser) {
          await tx.employees.update({
            where: { nrp: adminUser.nrp },
            data: { full_name: admin_name }
          });
        }
      }

      return updated;
    });

    res.json({ message: 'Data mitra berhasil diperbarui', data: result });
  } catch (err) {
    console.error('Update Mitra Error:', err);
    res.status(500).json({ error: 'Gagal memperbarui data mitra' });
  }
};

export const deleteMitra = async (req: Request, res: Response): Promise<void> => {
  try {
    if (req.user?.mitra_id) {
        res.status(403).json({ error: 'Anda tidak diizinkan menghapus data mitra.' });
        return;
    }
    const { id } = req.params;
    
    // Delete Mitra (Prisma will handle cascading deletes for employees, users, etc.)
    await prisma.mitras.delete({
      where: { mitra_id: Number(id) }
    });

    res.json({ message: 'Mitra berhasil dihapus' });
  } catch (err) {
    res.status(500).json({ error: 'Gagal menghapus mitra' });
  }
};

export const bulkDestroyMitras = async (req: Request, res: Response): Promise<void> => {
  try {
    const { ids } = req.body;
    if (!ids || !Array.isArray(ids)) {
      res.status(400).json({ error: 'Invalid or missing IDs array' });
      return;
    }

    // Delete Multiple Mitras (Prisma handles cascade)
    await prisma.mitras.deleteMany({
      where: { mitra_id: { in: ids.map(Number) } }
    });

    res.json({ message: `${ids.length} Mitra berhasil dihapus sekaligus` });
  } catch (err) {
    res.status(500).json({ error: 'Error in bulk deletion', details: String(err) });
  }
};


