import { Request, Response } from 'express';
import { prisma } from '../utils/db';

export const getShifts = async (req: Request, res: Response): Promise<void> => {
  try {
    const shifts = await prisma.shifts.findMany();
    res.json(shifts);
  } catch (err) {
    res.status(500).json({ error: 'Error fetching shifts' });
  }
};

export const getDepartments = async (req: Request, res: Response): Promise<void> => {
  try {
    const deps = await prisma.departments.findMany({
      include: {
        divisions: true
      }
    });
    res.json(deps);
  } catch (err) {
    res.status(500).json({ error: 'Error fetching deps' });
  }
};

export const getPositions = async (req: Request, res: Response): Promise<void> => {
    try {
      const positions = await prisma.positions.findMany();
      res.json(positions);
    } catch (err) {
      res.status(500).json({ error: 'Error fetching positions' });
    }
};

// --- GEOFENCE LOCATION DATA (MITRA KERJA) ---
export const getMitraKerja = async (req: Request, res: Response): Promise<void> => {
  try {
    const mitraList = await prisma.mitra_kerja.findMany();
    res.json(mitraList);
  } catch (err) {
    res.status(500).json({ error: 'Error fetching mitra kerja' });
  }
};

export const createMitraKerja = async (req: Request, res: Response): Promise<void> => {
  try {
    const { mitra_kerja_name, latitude, longitude, radius_meters } = req.body;
    
    // Validasi Sederhana
    if (!mitra_kerja_name) {
      res.status(400).json({ error: 'Nama Mitra Kerja wajib diisi' });
      return;
    }

    const created = await prisma.mitra_kerja.create({
      data: {
        mitra_kerja_name,
        latitude: latitude ? Number(latitude) : null,
        longitude: longitude ? Number(longitude) : null,
        radius_meters: radius_meters ? Number(radius_meters) : 50
      }
    });

    res.json({ message: 'Mitra kerja baru berhasil ditambahkan', data: created });
  } catch (err) {
    res.status(500).json({ error: 'Gagal menambah mitra kerja' });
  }
};

export const updateMitraKerja = async (req: Request, res: Response): Promise<void> => {
  try {
    const { id } = req.params;
    const { latitude, longitude, radius_meters } = req.body;
    
    const updated = await prisma.mitra_kerja.update({
      where: { mitra_kerja_id: Number(id) },
      data: {
        latitude: latitude ? Number(latitude) : null,
        longitude: longitude ? Number(longitude) : null,
        radius_meters: radius_meters ? Number(radius_meters) : 50
      }
    });
    
    res.json({ message: 'Geofence location updated successfully', data: updated });
  } catch (err) {
    res.status(500).json({ error: 'Error updating mitra kerja' });
  }
};

export const deleteMitraKerja = async (req: Request, res: Response): Promise<void> => {
  try {
    const { id } = req.params;
    await prisma.mitra_kerja.delete({
      where: { mitra_kerja_id: Number(id) }
    });
    
    res.json({ message: 'Geofence location deleted successfully' });
  } catch (err) {
    res.status(500).json({ error: 'Error deleting mitra kerja' });
  }
};

// --- SHIFTS ---
export const createShift = async (req: Request, res: Response): Promise<void> => {
  try {
    const { shift_code, time_in_expected, time_out_expected, is_night } = req.body;
    const tIn = time_in_expected.length === 5 ? time_in_expected + ':00' : time_in_expected;
    const tOut = time_out_expected.length === 5 ? time_out_expected + ':00' : time_out_expected;
    
    const created = await prisma.shifts.create({
      data: {
        shift_code,
        time_in_expected: new Date(`1970-01-01T${tIn}Z`),
        time_out_expected: new Date(`1970-01-01T${tOut}Z`),
        date_in: new Date('1970-01-01'),
        date_out: is_night == '1' ? new Date('1970-01-02') : new Date('1970-01-01')
      }
    });
    res.json({ message: 'Shift created', data: created });
  } catch (err) {
    res.status(500).json({ error: 'Error creating shift', details: String(err) });
  }
};
export const updateShift = async (req: Request, res: Response): Promise<void> => {
  try {
    const { id } = req.params;
    const { shift_code, time_in_expected, time_out_expected, is_night } = req.body;
    let updateData: any = {};
    if(shift_code) updateData.shift_code = shift_code;
    if(time_in_expected) {
        let tIn = time_in_expected.length === 5 ? time_in_expected + ':00' : time_in_expected;
        updateData.time_in_expected = new Date(`1970-01-01T${tIn}Z`);
    }
    if(time_out_expected) {
        let tOut = time_out_expected.length === 5 ? time_out_expected + ':00' : time_out_expected;
        updateData.time_out_expected = new Date(`1970-01-01T${tOut}Z`);
    }
    if (is_night !== undefined) {
        updateData.date_in = new Date('1970-01-01');
        updateData.date_out = is_night == '1' ? new Date('1970-01-02') : new Date('1970-01-01');
    }
    const updated = await prisma.shifts.update({ where: { shift_id: Number(id) }, data: updateData });
    res.json({ message: 'Shift updated', data: updated });
  } catch (err) {
    res.status(500).json({ error: 'Error updating shift', details: String(err) });
  }
};
export const deleteShift = async (req: Request, res: Response): Promise<void> => {
  try {
    const { id } = req.params;
    await prisma.shifts.delete({ where: { shift_id: Number(id) } });
    res.json({ message: 'Shift deleted' });
  } catch (err) {
    res.status(500).json({ error: 'Error deleting shift', details: String(err) });
  }
};

// --- DEPARTMENTS ---
export const createDepartment = async (req: Request, res: Response): Promise<void> => {
  try {
    const { dept_name } = req.body;
    const created = await prisma.departments.create({ data: { dept_name } });
    res.json({ message: 'Department created', data: created });
  } catch (err) {
    res.status(500).json({ error: 'Error creating department', details: String(err) });
  }
};
export const updateDepartment = async (req: Request, res: Response): Promise<void> => {
  try {
    const { id } = req.params;
    const { dept_name } = req.body;
    const updated = await prisma.departments.update({ where: { dept_id: Number(id) }, data: { dept_name } });
    res.json({ message: 'Department updated', data: updated });
  } catch (err) {
    res.status(500).json({ error: 'Error updating department', details: String(err) });
  }
};
export const deleteDepartment = async (req: Request, res: Response): Promise<void> => {
  try {
    const { id } = req.params;
    await prisma.departments.delete({ where: { dept_id: Number(id) } });
    res.json({ message: 'Department deleted' });
  } catch (err) {
    res.status(500).json({ error: 'Error deleting department', details: String(err) });
  }
};

// --- DIVISIONS ---
export const getDivisions = async (req: Request, res: Response): Promise<void> => {
  try {
    const { dept_id } = req.query;
    let whereClause = {};
    if(dept_id) whereClause = { dept_id: Number(dept_id) };
    const divisions = await prisma.divisions.findMany({ where: whereClause, include: { department: true }});
    res.json(divisions);
  } catch (err) {
    res.status(500).json({ error: 'Error fetching divisions', details: String(err) });
  }
};
export const createDivision = async (req: Request, res: Response): Promise<void> => {
  try {
    const { div_name, dept_id } = req.body;
    const created = await prisma.divisions.create({ data: { div_name, dept_id: Number(dept_id) } });
    res.json({ message: 'Division created', data: created });
  } catch (err) {
    res.status(500).json({ error: 'Error creating division', details: String(err) });
  }
};
export const updateDivision = async (req: Request, res: Response): Promise<void> => {
  try {
    const { id } = req.params;
    const { div_name, dept_id } = req.body;
    let updateData: any = {};
    if(div_name) updateData.div_name = div_name;
    if(dept_id) updateData.dept_id = Number(dept_id);
    const updated = await prisma.divisions.update({ where: { div_id: Number(id) }, data: updateData });
    res.json({ message: 'Division updated', data: updated });
  } catch (err) {
    res.status(500).json({ error: 'Error updating division', details: String(err) });
  }
};
export const deleteDivision = async (req: Request, res: Response): Promise<void> => {
  try {
    const { id } = req.params;
    await prisma.divisions.delete({ where: { div_id: Number(id) } });
    res.json({ message: 'Division deleted' });
  } catch (err) {
    res.status(500).json({ error: 'Error deleting division', details: String(err) });
  }
};

// --- POSITIONS ---
export const createPosition = async (req: Request, res: Response): Promise<void> => {
  try {
    const { pos_name } = req.body;
    const created = await prisma.positions.create({ data: { pos_name } });
    res.json({ message: 'Position created', data: created });
  } catch (err) {
    res.status(500).json({ error: 'Error creating position', details: String(err) });
  }
};
export const updatePosition = async (req: Request, res: Response): Promise<void> => {
  try {
    const { id } = req.params;
    const { pos_name } = req.body;
    const updated = await prisma.positions.update({ where: { pos_id: Number(id) }, data: { pos_name } });
    res.json({ message: 'Position updated', data: updated });
  } catch (err) {
    res.status(500).json({ error: 'Error updating position', details: String(err) });
  }
};
export const deletePosition = async (req: Request, res: Response): Promise<void> => {
  try {
    const { id } = req.params;
    await prisma.positions.delete({ where: { pos_id: Number(id) } });
    res.json({ message: 'Position deleted' });
  } catch (err) {
    res.status(500).json({ error: 'Error deleting position', details: String(err) });
  }
};

// --- DISTRICTS ---
export const getDistricts = async (req: Request, res: Response): Promise<void> => {
  try {
    const districts = await prisma.districts.findMany();
    res.json(districts);
  } catch (err) {
    res.status(500).json({ error: 'Error fetching districts', details: String(err) });
  }
};
export const createDistrict = async (req: Request, res: Response): Promise<void> => {
  try {
    const { dist_name } = req.body;
    const created = await prisma.districts.create({ data: { dist_name } });
    res.json({ message: 'District created', data: created });
  } catch (err) {
    res.status(500).json({ error: 'Error creating district', details: String(err) });
  }
};
export const updateDistrict = async (req: Request, res: Response): Promise<void> => {
  try {
    const { id } = req.params;
    const { dist_name } = req.body;
    const updated = await prisma.districts.update({ where: { dist_id: Number(id) }, data: { dist_name } });
    res.json({ message: 'District updated', data: updated });
  } catch (err) {
    res.status(500).json({ error: 'Error updating district', details: String(err) });
  }
};
export const deleteDistrict = async (req: Request, res: Response): Promise<void> => {
  try {
    const { id } = req.params;
    await prisma.districts.delete({ where: { dist_id: Number(id) } });
    res.json({ message: 'District deleted' });
  } catch (err) {
    res.status(500).json({ error: 'Error deleting district', details: String(err) });
  }
};
