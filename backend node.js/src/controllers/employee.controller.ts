import { Request, Response } from 'express';
import { prisma } from '../utils/db';
import bcrypt from 'bcrypt';

export const getEmployees = async (req: Request, res: Response): Promise<void> => {
  try {
    const mitraId = req.user?.mitra_id;
    const employees = await prisma.employees.findMany({
      where: {
        ...(mitraId && { mitra_id: mitraId })
      },
      include: {
        user: { select: { role: true, is_active: true } },
        position: true,
        division: { include: { department: true } },
        location: true,
        mitra: true
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
      location_id, 
      mitra_id,
      default_work_location 
    } = req.body;

    const userMitraId = req.user?.mitra_id;
    // If user is a Mitra Admin, they can ONLY create employees for their own mitra
    const finalMitraId = userMitraId ? userMitraId : (mitra_id ? Number(mitra_id) : null);

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
    const result = await prisma.$transaction(async (tx: any) => {
      const user = await tx.users.create({
        data: {
          nrp,
          password_hash,
          role: role || 'employee',
          is_active: true,
          mitra_id: finalMitraId
        }
      });

      const employee = await tx.employees.create({
        data: {
          nrp,
          full_name,
          pos_id: pos_id ? Number(pos_id) : null,
          div_id: div_id ? Number(div_id) : null,
          location_id: location_id ? Number(location_id) : null,
          mitra_id: finalMitraId,
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
      location_id, 
      mitra_id,
      default_work_location,
      password,
      is_active
    } = req.body;

    const userMitraId = req.user?.mitra_id;

    // Verify ownership if not super admin
    if (userMitraId) {
       const targetEmp = await prisma.employees.findUnique({ where: { nrp: id } });
       if (!targetEmp || targetEmp.mitra_id !== userMitraId) {
         res.status(403).json({ error: 'Anda tidak memiliki akses ke karyawan ini' });
         return;
       }
    }

    const result = await prisma.$transaction(async (tx: any) => {
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
          ...(location_id !== undefined && { location_id: location_id ? Number(location_id) : null }),
          ...(mitra_id !== undefined && !userMitraId && { mitra_id: mitra_id ? Number(mitra_id) : null }), // Only super can change mitra
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
    const userMitraId = req.user?.mitra_id;

    // Verify ownership if not super admin
    if (userMitraId) {
       const targetEmp = await prisma.employees.findUnique({ where: { nrp: id } });
       if (!targetEmp || targetEmp.mitra_id !== userMitraId) {
         res.status(403).json({ error: 'Anda tidak memiliki akses ke karyawan ini' });
         return;
       }
    }

    // Check constraints: attendances
    const atts = await prisma.attendances.count({ where: { nrp: id } });
    if (atts > 0) {
      res.status(400).json({ error: 'Tidak bisa menghapus karyawan karena sudah memiliki riwayat absen. Pertimbangkan untuk menonaktifkan ganti menjadi is_active = false.' });
      return;
    }

    await prisma.$transaction(async (tx: any) => {
      await tx.employees.delete({ where: { nrp: id } });
      await tx.users.delete({ where: { nrp: id } });
    });

    res.json({ message: 'Karyawan berhasil dihapus' });
  } catch (error) {
    res.status(500).json({ error: 'Gagal menghapus karyawan', details: String(error) });
  }
};
