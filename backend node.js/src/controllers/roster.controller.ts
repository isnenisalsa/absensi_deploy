import { Request, Response } from 'express';
import { prisma } from '../utils/db';

export const getRosters = async (req: Request, res: Response) => {
  try {
    const { month, year, nrp } = req.query;
    let where: any = {};

    if (nrp) {
      where.nrp = String(nrp);
    }

    if (month && year) {
      const startDate = new Date(Number(year), Number(month) - 1, 1);
      const endDate = new Date(Number(year), Number(month), 0);
      where.date = {
        gte: startDate,
        lte: endDate,
      };
    }

    const rosters = await prisma.rosters.findMany({
      where,
      include: {
        employee: true,
        shift: true,
      },
      orderBy: {
        date: 'asc',
      },
    });

    res.json(rosters);
  } catch (error) {
    res.status(500).json({ error: 'Gagal mengambil data roster', details: String(error) });
  }
};

export const createRoster = async (req: Request, res: Response) => {
  try {
    const { nrp, date, shift_id } = req.body;

    if (!nrp || !date) {
      return res.status(400).json({ error: 'NRP dan Tanggal wajib diisi' });
    }

    const roster = await prisma.rosters.upsert({
      where: {
        nrp_date: {
          nrp,
          date: new Date(date),
        },
      },
      update: {
        shift_id: shift_id ? Number(shift_id) : null,
        work_location: req.body.work_location || null,
      },
      create: {
        nrp,
        date: new Date(date),
        shift_id: shift_id ? Number(shift_id) : null,
        work_location: req.body.work_location || null,
      },
    });

    res.status(201).json(roster);
  } catch (error) {
    res.status(500).json({ error: 'Gagal membuat/update roster', details: String(error) });
  }
};

export const bulkCreateRoster = async (req: Request, res: Response) => {
  try {
    const { rosters } = req.body; // Array of { nrp, date, shift_id, work_location }

    if (!Array.isArray(rosters)) {
      return res.status(400).json({ error: 'Data roster harus berupa array' });
    }

    const results = await prisma.$transaction(
      rosters.map((r) =>
        prisma.rosters.upsert({
          where: {
            nrp_date: {
              nrp: r.nrp,
              date: new Date(r.date),
            },
          },
          update: {
            shift_id: r.shift_id ? Number(r.shift_id) : null,
            work_location: r.work_location || null,
          },
          create: {
            nrp: r.nrp,
            date: new Date(r.date),
            shift_id: r.shift_id ? Number(r.shift_id) : null,
            work_location: r.work_location || null,
          },
        })
      )
    );


    res.json({ message: 'Bulk roster berhasil diproses', count: results.length });
  } catch (error) {
    res.status(500).json({ error: 'Gagal memproses bulk roster', details: String(error) });
  }
};

export const deleteRoster = async (req: Request, res: Response) => {
  try {
    const { id } = req.params;
    await prisma.rosters.delete({
      where: { id: Number(id) },
    });
    res.json({ message: 'Roster berhasil dihapus' });
  } catch (error) {
    res.status(500).json({ error: 'Gagal menghapus roster', details: String(error) });
  }
};
