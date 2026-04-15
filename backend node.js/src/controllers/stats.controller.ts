import { Request, Response } from 'express';
import { prisma } from '../utils/db';

/**
 * Controller untuk menangani statistik dashboard
 */
export const getDashboardStats = async (req: Request, res: Response): Promise<void> => {
  try {
    const mitraId = req.user?.mitra_id;
    
    // Filter berdasarkan mitra_id jika user adalah admin mitra (bukan superadmin)
    const whereMitra = mitraId ? { mitra_id: Number(mitraId) } : {};

    // 1. Total Karyawan
    const totalEmployees = await prisma.employees.count({
      where: {
        ...whereMitra,
        user: { role: 'employee' }
      }
    });

    // 2. Presensi Hari Ini (Check-in)
    const now = new Date();
    const dateString = now.toISOString().split('T')[0];
    const today = new Date(`${dateString}T00:00:00.000Z`);

    const todayPresence = await prisma.attendances.count({
      where: {
        attendance_date: today,
        trans_type: 'Check_in',
        employee: {
            ...whereMitra,
            user: { role: 'employee' }
        }
      }
    });

    // 3. Terlambat Hari Ini (Check-in time > shift_time_in)
    // Query check-ins today and include shift info
    const latePresences = await prisma.attendances.findMany({
      where: {
        attendance_date: today,
        trans_type: 'Check_in',
        employee: {
            ...whereMitra,
            user: { role: 'employee' }
        }
      },
      include: { shift: true }
    });

    let lateCount = 0;
    latePresences.forEach(att => {
      if (att.shift && att.time_wita && att.shift.time_in_expected) {
        const checkInTime = new Date(att.time_wita).getTime();
        const expectedTime = new Date(att.shift.time_in_expected).getTime();
        if (checkInTime > expectedTime) {
          lateCount++;
        }
      }
    });

    // 4. Tidak Hadir (Total - Presence - Izin/Sakit)
    // Note: this is a simple delta
    const notPresent = Math.max(0, totalEmployees - todayPresence);

    // 5. Total Lokasi Kerja & Master Data (Isolasi)
    let totalDepts, totalDivs, totalPositions, totalShifts, totalLocations;

    if (mitraId) {
        // Jika Admin Mitra, hitung hanya yang terpakai oleh karyawan mereka
        totalDepts = await prisma.departments.count({
            where: { employees: { some: { mitra_id: Number(mitraId) } } }
        });
        totalDivs = await prisma.divisions.count({
            where: { employees: { some: { mitra_id: Number(mitraId) } } }
        });
        totalPositions = await prisma.positions.count({
            where: { employees: { some: { mitra_id: Number(mitraId) } } }
        });
        totalShifts = await prisma.shifts.count({
            where: { rosters: { some: { employee: { mitra_id: Number(mitraId) } } } }
        });
        totalLocations = await prisma.locations.count({
            where: { employees: { some: { mitra_id: Number(mitraId) } } }
        });
    } else {
        // Jika Superadmin, hitung global
        totalDepts = await prisma.departments.count();
        totalDivs = await prisma.divisions.count();
        totalPositions = await prisma.positions.count();
        totalShifts = await prisma.shifts.count();
        totalLocations = await prisma.locations.count();
    }
    
    // Isolasi Total Mitra: Admin Mitra hanya melihat 1 (dirinya sendiri)
    const totalMitras = mitraId ? 1 : await prisma.mitras.count();

    // 6. Trend Presensi 7 Hari Terakhir
    const trend = [];
    for (let i = 6; i >= 0; i--) {
        const d = new Date();
        d.setDate(d.getDate() - i);
        const ds = d.toISOString().split('T')[0];
        const dt = new Date(`${ds}T00:00:00.000Z`);

        const count = await prisma.attendances.count({
            where: {
                attendance_date: dt,
                trans_type: 'Check_in',
                employee: {
                    ...whereMitra,
                    user: { role: 'employee' }
                }
            }
        });

        trend.push({
            date: ds,
            count
        });
    }

    // 7. Aktivitas Terbaru (10 Terakhir untuk tabel)
    const recentPresence = await prisma.attendances.findMany({
        where: {
            employee: {
                ...whereMitra,
                user: { role: 'employee' }
            }
        },
        include: {
            employee: {
                select: { 
                    full_name: true, 
                    photo_profile: true,
                    department: { select: { dept_name: true } },
                    mitra: { select: { mitra_name: true } } 
                }
            },
            shift: {
                select: { shift_code: true }
            }
        },
        orderBy: { time_wita: 'desc' },
        take: 10
    });

    // 8. Breakdown per Mitra (Khusus Superadmin)
    let mitraBreakdown = [];
    if (!mitraId) {
        const mitrasData = await prisma.mitras.findMany({
            include: { 
                employees: {
                    where: { user: { role: 'employee' } }
                }
            }
        });
        mitraBreakdown = mitrasData.map(m => ({
            name: m.mitra_name,
            count: m.employees.length
        }));
    }

    res.json({
        total_employees: totalEmployees,
        today_presence: todayPresence,
        late_count: lateCount,
        not_present: notPresent,
        outside_geofence: 0, // Logic placeholder based on system constraints
        total_mitras: totalMitras,
        master_summary: {
            departments: totalDepts,
            divisions: totalDivs,
            positions: totalPositions,
            shifts: totalShifts,
            locations: totalLocations
        },
        trend,
        recent_presence: recentPresence,
        mitra_breakdown: mitraBreakdown
    });

  } catch (err) {
    console.error('API Dashboards Stats Error:', err);
    res.status(500).json({ 
        error: 'Gagal memuat statistik dashboard', 
        details: err instanceof Error ? err.message : String(err) 
    });
  }
};
