import { Request, Response } from 'express';
import { prisma } from '../utils/db';
import { calculateDistance, isPointInPolygon, parsePolygonCoords } from '../utils/geolocation';
import path from 'path';
import fs from 'fs';

// ─────────────────────────────────────────────────────────────────
// Upload Foto Bukti Absensi (Camera Only — no gallery)
// POST /api/attendance/upload-photo (multipart/form-data, field: photo)
// ─────────────────────────────────────────────────────────────────
export const uploadAttendancePhotoController = async (req: Request, res: Response): Promise<void> => {
  try {
    const file = req.file;
    if (!file) {
      res.status(400).json({ error: 'Tidak ada file foto yang dikirim.' });
      return;
    }

    // Return URL-friendly path so Flutter can store & reference later
    const filename = file.filename;
    const fileUrl = `/uploads/attendance/${filename}`;

    res.status(200).json({ 
      message: 'Foto berhasil diupload.',
      filename,
      url: fileUrl
    });
  } catch (error) {
    console.error('Upload photo error:', error);
    res.status(500).json({ 
      error: 'Gagal mengupload foto.', 
      details: error instanceof Error ? error.message : String(error) 
    });
  }
};

export const checkIn = async (req: Request, res: Response): Promise<void> => {
  try {
    // Dipastikan telah melewati token auth
    const nrp = req.user!.nrp; 
    const user = req.user!;

    const { shift_id, cp_location, work_location, lat, long, photo_url, location_id } = req.body;

    if (!shift_id || lat === undefined || long === undefined) {
      res.status(400).json({ error: 'Data Shift, Latitude, dan Longitude wajib diisi.' });
      return;
    }

    const employee = await prisma.employees.findUnique({ where: { nrp } });

    if (!employee && user.role !== 'admin') {
      res.status(404).json({ error: "Data karyawan tidak ditemukan. Pastikan NRP Anda terdaftar di tabel Employees atau gunakan role Admin untuk testing." });
      return;
    }

    // Validasi keberadaan shift_id
    const shiftExists = await prisma.shifts.findUnique({
      where: { shift_id: Number(shift_id) }
    });

    if (!shiftExists) {
      res.status(400).json({ error: `Shift dengan ID ${shift_id} tidak ditemukan. Silakan hubungi admin untuk update roster.` });
      return;
    }

    // Melacak koordinat geofence
    let targetLocation: any = null;
    
    // Fetch employee data with position to check for flexibility
    const employeeData = await prisma.employees.findUnique({
        where: { nrp },
        include: { location: true, position: true }
    });

    if (location_id) {
        // If they want to check in at a SPECIFIC location, check if they have the right
        const isFlexibleUser = (employeeData?.position as any)?.allow_any_location === true;
        const isAssignedLocation = Number(location_id) === employeeData?.location_id;

        const isFlexibleEmp = employeeData?.allow_any_location === true;
        const isAdmin = user.role === "admin";
        if (!isAssignedLocation && !isFlexibleUser && !isFlexibleEmp && !isAdmin) {
            res.status(403).json({ error: 'Jabatan Anda tidak diizinkan untuk melakukan absensi di luar lokasi yang ditentukan.' });
            return;
        }

        targetLocation = await prisma.locations.findUnique({
            where: { location_id: Number(location_id) }
        });
    }

    if (!targetLocation) {
        // Fallback ke lokasi default employee
        targetLocation = employeeData?.location;
    }

    if (targetLocation?.polygon_coords) {
      const polygon = parsePolygonCoords(targetLocation.polygon_coords);
      if (polygon.length > 0) {
        const isInside = isPointInPolygon({ lat: Number(lat), lng: Number(long) }, polygon);
        if (!isInside) {
          res.status(403).json({ 
            error: `Anda berada di luar area geofence ${targetLocation.location_name}.`,
            is_polygon: true
          });
          return;
        }
      }
    } else if (targetLocation?.latitude && targetLocation?.longitude) {
      const officeLat = Number(targetLocation.latitude);
      const officeLon = Number(targetLocation.longitude);
      const radiusLimit = targetLocation.radius_meters || 50;

      const distance = calculateDistance(lat, long, officeLat, officeLon);
      
      if (distance > radiusLimit) {
        res.status(403).json({ 
          error: `Anda berada di luar jangkauan ${targetLocation.location_name}.`, 
          jarak_meter: Math.round(distance),
          batas_radius_meter: radiusLimit
        });
        return;
      }
    }

    // Capture Local time and calculate offsets for literal storage
    const now = new Date();
    // Offset for WITA (UTC+8) and WIB (UTC+7)
    const timeWita = new Date(now.getTime() + 8 * 60 * 60 * 1000);
    const timeWib = new Date(now.getTime() + 7 * 60 * 60 * 1000);

    const attendance = await prisma.attendances.create({
      data: {
        nrp,
        attendance_date: now,
        date_in: now,
        time_wita: timeWita, 
        time_wib: timeWib, 
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

    const startOfDay = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    const endOfDay = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 23, 59, 59, 999);
    
    // Sinkronisasi lokasi aktual ke Master Roster Hari ini
    if (work_location) {
      await prisma.rosters.updateMany({
        where: {
          nrp,
          date: { gte: startOfDay, lte: endOfDay }
        },
        data: {
          actual_location: work_location
        }
      });
    }

    res.status(201).json({
      message: 'Check-in berhasil disimpan!',
      data: attendance
    });

  } catch (error) {
    console.error('Check-in error detailed:', error);
    res.status(500).json({ 
      error: 'Gagal melakukan absensi.', 
      details: error instanceof Error ? error.message : String(error) 
    });
  }
};

export const checkOut = async (req: Request, res: Response): Promise<void> => {
    try {
      const nrp = req.user!.nrp; 
      const user = req.user!;
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

    const { location_id } = req.body;
    let targetLocation: any = null;

    // Fetch employee data with position for flexibility check
    const employeeData = await prisma.employees.findUnique({
        where: { nrp },
        include: { location: true, position: true }
    });

    if (location_id) {
        const isFlexibleUser = (employeeData?.position as any)?.allow_any_location === true;
        const isAssignedLocation = Number(location_id) === employeeData?.location_id;

        const isFlexibleEmp = employeeData?.allow_any_location === true;
        const isAdmin = user.role === "admin";
        if (!isAssignedLocation && !isFlexibleUser && !isFlexibleEmp && !isAdmin) {
            res.status(403).json({ error: 'Jabatan Anda tidak diizinkan untuk melakukan absensi di luar lokasi yang ditentukan.' });
            return;
        }

        targetLocation = await prisma.locations.findUnique({
            where: { location_id: Number(location_id) }
        });
    }

    if (!targetLocation) {
        targetLocation = employeeData?.location;
    }

    if (targetLocation?.polygon_coords) {
      const polygon = parsePolygonCoords(targetLocation.polygon_coords);
      if (polygon.length > 0) {
        const isInside = isPointInPolygon({ lat: Number(lat), lng: Number(long) }, polygon);
        if (!isInside) {
          res.status(403).json({ 
            error: `Checkout gagal! Anda berada di luar area geofence ${targetLocation.location_name}.`,
            is_polygon: true
          });
          return;
        }
      }
    } else if (targetLocation?.latitude && targetLocation?.longitude) {
        const officeLat = Number(targetLocation.latitude);
        const officeLon = Number(targetLocation.longitude);
        const radiusLimit = targetLocation.radius_meters || 50;

        const distance = calculateDistance(lat, long, officeLat, officeLon);
        
        if (distance > radiusLimit) {
          res.status(403).json({ 
            error: `Checkout gagal! Anda berada di luar jangkauan ${targetLocation.location_name}.`, 
            jarak_meter: Math.round(distance),
            batas_radius_meter: radiusLimit
          });
          return;
        }
    }

      // Use existing 'now' and calculate offsets for literal storage
      const timeWita = new Date(now.getTime() + 8 * 60 * 60 * 1000);
      const timeWib = new Date(now.getTime() + 7 * 60 * 60 * 1000);

      const attendance = await prisma.attendances.create({
        data: {
          nrp,
          attendance_date: latestCheckIn.attendance_date,
          date_in: latestCheckIn.date_in,
          date_out: now,
          time_wita: timeWita,
          time_wib: timeWib,
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
      console.error('Check-out error detailed:', error);
      res.status(500).json({ 
        error: 'Gagal melakukan checkout absen.',
        details: error instanceof Error ? error.message : String(error)
      });
    }
  };

  export const getMyAttendance = async (req: Request, res: Response): Promise<void> => {
    try {
        const nrp = req.user!.nrp;
        const role = req.user!.role;
        const mitraId = req.user?.mitra_id;
        
        let whereClause: any = {};
        
        if (role !== 'admin') {
            whereClause.nrp = nrp;
        } else {
            // Admin Filter Logic
            if (mitraId) {
                whereClause.employee = { mitra_id: mitraId };
            }
            const { start_date, end_date, divisi, dept, lokasi } = req.query;

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
                    dept_id: Number(dept)
                };
            }
            if (lokasi && lokasi !== 'ALL') {
                whereClause.employee = { ...whereClause.employee, location_id: Number(lokasi) };
            }
        }

        const records = await prisma.attendances.findMany({
            where: whereClause,
            orderBy: { attendance_date: 'desc' },
            include: { 
                shift: true,
                employee: {
                    include: {
                        location: true,
                        position: true,
                        department: true,
                        division: true,
                    }
                }
            }
        });

        res.status(200).json(records);
    } catch(err) {
        res.status(500).json({ error: 'Gagal mengambil data.', details: String(err) });
    }
  }

  export const getTodayStatus = async (req: Request, res: Response): Promise<void> => {
    try {
        const nrp = req.user!.nrp;
        const now = new Date();
        
        // Start and end of today in local time, formatted for stable Prisma query
        // We use a string format YYYY-MM-DD for @db.Date fields to avoid timezone shifts
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const dateString = `${year}-${month}-${day}`;

        // 1. Get Today's Roster (using date string to match @db.Date exactly)
        const roster = await prisma.rosters.findFirst({
            where: {
                nrp,
                date: new Date(dateString + 'T00:00:00.000Z')
            },
            include: { shift: true }
        });

        // 2. Get Today's Attendance Records
        // Use full day range to capture all transactions for the current date
        const startOfDay = new Date(dateString + 'T00:00:00.000Z');
        const endOfDay = new Date(dateString + 'T23:59:59.999Z');

        const attendances = await prisma.attendances.findMany({
            where: {
                nrp,
                attendance_date: {
                    gte: startOfDay,
                    lte: endOfDay
                }
            },
            orderBy: { time_wita: 'asc' }
        });

        const checkIn = attendances.find(a => a.trans_type === 'Check_in');
        const checkOut = attendances.find(a => a.trans_type === 'Check_out');

        let status = 'waiting';
        if (checkOut) {
            status = 'completed';
        } else if (checkIn) {
            status = 'checked_in';
        }

        res.status(200).json({
            roster,
            attendance: {
                check_in: checkIn,
                check_out: checkOut
            },
            status
        });

    } catch (error) {
        console.error('Error fetching today status:', error);
        res.status(500).json({ error: 'Gagal mengambil status hari ini.' });
    }
  }

  export const submitFtwReport = async (req: Request, res: Response): Promise<void> => {
    try {
        const nrp = req.user!.nrp;
        const { 
            jam_tidur_12_jam, jam_bangun, 
            konsumsi_obat, punya_masalah, gejala_kesehatan 
        } = req.body;

        const now = new Date();
        // Stabilizing Date for @db.Date column: Always use YYYY-MM-DD at UTC midnight
        const dateString = now.toISOString().split('T')[0]; 
        const ftwDateUtc = new Date(`${dateString}T00:00:00.000Z`);

        // Backward compatibility mapping for Prisma and Laravel
        const map_kehadiran = true;
        const map_unit = "-";
        const map_pola = "-";
        const map_shift = "-";
        const map_tidur12 = jam_tidur_12_jam || "-"; 
        const map_tidur36 = "-";
        const map_status = req.body.status_ftw || "FIT";

        const ftw = await prisma.ftw_reports.upsert({
            where: {
                nrp_ftw_date: {
                    nrp,
                    ftw_date: ftwDateUtc
                }
            },
            update: {
                kehadiran_onsite: map_kehadiran, unit_dioperasikan: map_unit, 
                pola_shift: map_pola, shift: map_shift, 
                jam_tidur_12_jam: map_tidur12, jam_tidur_36_jam: map_tidur36, 
                jam_bangun, konsumsi_obat, punya_masalah, gejala_kesehatan, 
                status_ftw: map_status
            },
            create: {
                nrp,
                ftw_date: ftwDateUtc,
                kehadiran_onsite: map_kehadiran, unit_dioperasikan: map_unit, 
                pola_shift: map_pola, shift: map_shift, 
                jam_tidur_12_jam: map_tidur12, jam_tidur_36_jam: map_tidur36, 
                jam_bangun, konsumsi_obat, punya_masalah, gejala_kesehatan, 
                status_ftw: map_status
            }
        });

        res.status(201).json({ message: 'FTW Report berhasil dikirim!', data: ftw });
    } catch (error) {
        console.error('Submit FTW error:', error);
        res.status(500).json({ error: 'Gagal mengirim survey FTW.', details: String(error) });
    }
  }

  // Mengambil Data Fit To Work (FTW) untuk Admin
  export const getAllFtw = async (req: Request, res: Response): Promise<void> => {
    try {
        const { filter_date } = req.query;

        let whereClause: any = {};
        
        if (filter_date) {
            // Stable search: Match YYYY-MM-DD at UTC midnight exactly
            const targetDate = new Date(`${filter_date}T00:00:00.000Z`);
            whereClause.ftw_date = targetDate;
        }

        const mitraId = req.user?.mitra_id;
        if (mitraId) {
            whereClause.employee = { mitra_id: mitraId };
        }

        const records = await prisma.ftw_reports.findMany({
            where: whereClause,
            orderBy: [{ ftw_date: 'desc' }, { created_at: 'desc' }],
            include: { 
                employee: {
                  include: {
                    division: { include: { departments: { include: { department: true } } } },
                    position: true,
                    location: true
                  }
                }
            }
        });

        res.status(200).json(records);
    } catch(err) {
        res.status(500).json({ error: 'Gagal mengambil data.', details: String(err) });
    }
  }
