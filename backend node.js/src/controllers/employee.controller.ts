import { Request, Response } from 'express';
import { prisma } from '../utils/db';
import bcrypt from 'bcrypt';

export const getEmployees = async (req: Request, res: Response): Promise<void> => {
  try {
    const employees = await prisma.employees.findMany({
      include: {
        user: { select: { role: true, is_active: true } },
        position: true,
        division: { include: { department: true } },
        mitra_kerja: true,
        district: true
      }
    });
    res.json(employees);
  } catch (error) {
    res.status(500).json({ error: 'Gagal mengambil data karyawan', details: String(error) });
  }
};

export const createEmployee = async (req: Request, res: Response): Promise<void> => {
  try {
    const { 
      nrp, 
      full_name, 
      password, 
      role, 
      pos_id, 
      div_id, 
      mitra_kerja_id, 
      dist_id, 
      default_work_location 
    } = req.body;

    if (!nrp || !full_name || !password) {
      res.status(400).json({ error: 'NRP, Full Name, and Password are required' });
      return;
    }

    const existingUser = await prisma.users.findUnique({ where: { nrp } });
    if (existingUser) {
      res.status(400).json({ error: 'Karyawan dengan NRP ini sudah terdaftar' });
      return;
    }

    const salt = await bcrypt.genSalt(10);
    const password_hash = await bcrypt.hash(password, salt);

    // Gunakan Prisma transaction untuk memastikan User dan Employee sukses keduannya
    const result = await prisma.$transaction(async (tx) => {
      const user = await tx.users.create({
        data: {
          nrp,
          password_hash,
          role: role || 'employee',
          is_active: true
        }
      });

      const employee = await tx.employees.create({
        data: {
          nrp,
          full_name,
          pos_id: pos_id ? Number(pos_id) : null,
          div_id: div_id ? Number(div_id) : null,
          mitra_kerja_id: mitra_kerja_id ? Number(mitra_kerja_id) : null,
          dist_id: dist_id ? Number(dist_id) : null,
          default_work_location: default_work_location || 'WFO'
        }
      });

      return { user, employee };
    });

    res.status(201).json({ message: 'Karyawan berhasil dibuat', data: result });
  } catch (error) {
    res.status(500).json({ error: 'Gagal membuat karyawan baru', details: String(error) });
  }
};

export const updateEmployee = async (req: Request, res: Response): Promise<void> => {
  try {
    const id = req.params.id as string;
    const { 
      full_name, 
      role, 
      pos_id, 
      div_id, 
      mitra_kerja_id, 
      dist_id, 
      default_work_location,
      password,
      is_active
    } = req.body;

    const result = await prisma.$transaction(async (tx) => {
      let updateDataUser: any = {};
      if (role) updateDataUser.role = String(role);
      if (is_active !== undefined) updateDataUser.is_active = is_active;
      if (password) {
        const salt = await bcrypt.genSalt(10);
        updateDataUser.password_hash = await bcrypt.hash(password, salt);
      }
      
      if (Object.keys(updateDataUser).length > 0) {
        await tx.users.update({
          where: { nrp: id },
          data: updateDataUser
        });
      }

      const employee = await tx.employees.update({
        where: { nrp: id },
        data: {
          ...(full_name && { full_name: String(full_name) }),
          ...(pos_id !== undefined && { pos_id: pos_id ? Number(pos_id) : null }),
          ...(div_id !== undefined && { div_id: div_id ? Number(div_id) : null }),
          ...(mitra_kerja_id !== undefined && { mitra_kerja_id: mitra_kerja_id ? Number(mitra_kerja_id) : null }),
          ...(dist_id !== undefined && { dist_id: dist_id ? Number(dist_id) : null }),
          ...(default_work_location !== undefined && { default_work_location: String(default_work_location) })
        }
      });

      return employee;
    });

    res.json({ message: 'Data karyawan diperbarui', data: result });
  } catch (error) {
    res.status(500).json({ error: 'Gagal memperbarui karyawan', details: String(error) });
  }
};

export const deleteEmployee = async (req: Request, res: Response): Promise<void> => {
  try {
    const id = req.params.id as string;
    
    // Check constraints: attendances
    const atts = await prisma.attendances.count({ where: { nrp: id } });
    if (atts > 0) {
      res.status(400).json({ error: 'Tidak bisa menghapus karyawan karena sudah memiliki riwayat absen. Pertimbangkan untuk menonaktifkan ganti menjadi is_active = false.' });
      return;
    }

    await prisma.$transaction(async (tx) => {
      await tx.employees.delete({ where: { nrp: id } });
      await tx.users.delete({ where: { nrp: id } });
    });

    res.json({ message: 'Karyawan berhasil dihapus' });
  } catch (error) {
    res.status(500).json({ error: 'Gagal menghapus karyawan', details: String(error) });
  }
};
