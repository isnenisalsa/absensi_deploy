import { Request, Response } from 'express';
import { prisma } from '../utils/db';
import { calculateDistance } from '../utils/geolocation';

export const checkIn = async (req: Request, res: Response): Promise<void> => {
  try {
    // Dipastikan telah melewati token auth
    const nrp = req.user!.nrp; 

    const { shift_id, cp_location, work_location, lat, long, photo_url } = req.body;

    if (!shift_id || lat === undefined || long === undefined) {
      res.status(400).json({ error: 'Data Shift, Latitude, dan Longitude wajib diisi.' });
      return;
    }

    // Melacak koordinat kantor dari Employee's Mitra Kerja
    const employeeData = await prisma.employees.findUnique({
      where: { nrp },
      include: { mitra_kerja: true }
    });

    if (employeeData?.mitra_kerja?.latitude && employeeData?.mitra_kerja?.longitude) {
      const officeLat = Number(employeeData.mitra_kerja.latitude);
      const officeLon = Number(employeeData.mitra_kerja.longitude);
      const radiusLimit = employeeData.mitra_kerja.radius_meters || 50;

      const distance = calculateDistance(lat, long, officeLat, officeLon);
      
      if (distance > radiusLimit) {
        res.status(403).json({ 
          error: 'Lokasi Anda berada di luar jangkauan area kerja terdaftar.', 
          jarak_meter: Math.round(distance),
          batas_radius_meter: radiusLimit
        });
        return;
      }
    }

    // Capture Local time 
    const now = new Date();

    const attendance = await prisma.attendances.create({
      data: {
        nrp,
        attendance_date: now,
        date_in: now,
        time_wita: now, 
        time_wib: new Date(now.getTime() - 60 * 60 * 1000), 
        shift_id,
        trans_type: 'Check_in',
        cp_location,
        work_location,
        att_latitude: lat,
        att_longitude: long,
        att_map_link: `https://maps.google.com/?q=${lat},${long}`,
        photo_evidence: photo_url
      }
    });

    res.status(201).json({
      message: 'Check-in berhasil disimpan!',
      data: attendance
    });

  } catch (error) {
    console.error('Check-in error:', error);
    res.status(500).json({ error: 'Gagal melakukan absensi.', details: String(error) });
  }
};

export const checkOut = async (req: Request, res: Response): Promise<void> => {
    try {
      const nrp = req.user!.nrp; 
      const { lat, long, photo_url } = req.body;
  
      // Opsional: Validasi kalau hari ini belum checkout dsb.
      // Dibuat route Create Transaksi ke-dua bernama Check-Out
    const now = new Date();
    // Look back 24 hours to find the latest Check-in that HAS NOT been checked out yet.
    const twentyFourHoursAgo = new Date(now.getTime() - 24 * 60 * 60 * 1000);

    const latestCheckIn = await prisma.attendances.findFirst({
      where: {
          nrp,
          trans_type: 'Check_in',
          time_wita: {
              gte: twentyFourHoursAgo
          }
      },
      orderBy: { time_wita: 'desc' }
    });

    if(!latestCheckIn) {
        res.status(404).json({ error: 'Anda belum Check-in dalam 24 jam terakhir!' });
        return;
    }

    // Optional: Check if already checked out for this specific Check-in
    const alreadyCheckedOut = await prisma.attendances.findFirst({
        where: {
            nrp,
            trans_type: 'Check_out',
            // Check-out must be after the check-in time
            time_wita: {
                gt: latestCheckIn.time_wita
            }
        }
    });

    if (alreadyCheckedOut) {
        res.status(400).json({ error: 'Anda sudah melakukan Check-out untuk shift terakhir.' });
        return;
    }

      const attendance = await prisma.attendances.create({
        data: {
          nrp,
          attendance_date: latestCheckIn.attendance_date,
          date_in: latestCheckIn.date_in,
          date_out: now,
          time_wita: now,
          time_wib: new Date(now.getTime() - 60 * 60 * 1000),
          shift_id: latestCheckIn.shift_id,
          trans_type: 'Check_out',
          att_latitude: lat,
          att_longitude: long,
          att_map_link: `https://maps.google.com/?q=${lat},${long}`,
          photo_evidence: photo_url
        }
      });
  
      res.status(201).json({
        message: 'Check-out berhasil disimpan!',
        data: attendance
      });
  
    } catch (error) {
      console.error('Check-out error:', error);
      res.status(500).json({ error: 'Gagal melakukan checkout absen.' });
    }
  };

  export const getMyAttendance = async (req: Request, res: Response): Promise<void> => {
    try {
        const nrp = req.user!.nrp;
        const role = req.user!.role; // Assuming token includes role
        
        let whereClause: any = {};
        
        if (role !== 'admin') {
            whereClause.nrp = nrp;
        } else {
            // Admin Filter Logic
            const { start_date, end_date, divisi, dept, perusahaan, distrik } = req.query;

            if (start_date || end_date) {
                whereClause.attendance_date = {};
                if (start_date) whereClause.attendance_date.gte = new Date(`${start_date}T00:00:00.000Z`);
                if (end_date) whereClause.attendance_date.lte = new Date(`${end_date}T23:59:59.999Z`);
            }

            if (divisi && divisi !== 'ALL') {
                whereClause.employee = { ...whereClause.employee, div_id: Number(divisi) };
            }
            if (dept && dept !== 'ALL') {
                whereClause.employee = { 
                    ...whereClause.employee, 
                    division: { dept_id: Number(dept) } 
                };
            }
            if (perusahaan && perusahaan !== 'ALL') {
                whereClause.employee = { ...whereClause.employee, mitra_kerja_id: Number(perusahaan) };
            }
            if (distrik && distrik !== 'ALL') {
                whereClause.employee = { ...whereClause.employee, dist_id: Number(distrik) };
            }
        }

        const records = await prisma.attendances.findMany({
            where: whereClause,
            orderBy: { attendance_date: 'desc' },
            include: { 
                shift: true,
                employee: {
                    include: {
                        mitra_kerja: true,
                        position: true,
                        division: {
                            include: { department: true }
                        },
                        district: true
                    }
                }
            }
        });

        res.status(200).json(records);
    } catch(err) {
        res.status(500).json({ error: 'Gagal mengambil data.', details: String(err) });
    }
  }

  // Mengambil Data Fit To Work (FTW) untuk Admin
  export const getAllFtw = async (req: Request, res: Response): Promise<void> => {
    try {
        const { filter_date } = req.query;

        let whereClause: any = {};
        
        if (filter_date) {
            // Kita cari dari jam 00:00 s.d 23:59 pada tanggal yang dipilih
            const startDate = new Date(`${filter_date}T00:00:00.000Z`);
            const endDate = new Date(`${filter_date}T23:59:59.999Z`);
            whereClause.ftw_date = {
                gte: startDate,
                lte: endDate
            };
        }

        const records = await prisma.ftw_reports.findMany({
            where: whereClause,
            orderBy: [{ ftw_date: 'desc' }, { created_at: 'desc' }],
            include: { 
                employee: true
            }
        });

        res.status(200).json(records);
    } catch(err) {
        res.status(500).json({ error: 'Gagal mengambil data.', details: String(err) });
    }
  }
