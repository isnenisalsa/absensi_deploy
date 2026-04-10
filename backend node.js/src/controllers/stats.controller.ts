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
      where: whereMitra
    });

    // 2. Presensi Hari Ini (Check-in)
    const now = new Date();
    const dateString = now.toISOString().split('T')[0];
    const today = new Date(`${dateString}T00:00:00.000Z`);

    const todayPresence = await prisma.attendances.count({
      where: {
        attendance_date: today,
        trans_type: 'Check_in',
        employee: whereMitra
      }
    });

    // 3. Status FTW Hari Ini
    const todayFtwFit = await prisma.ftw_reports.count({
      where: {
        ftw_date: today,
        status_ftw: 'FIT',
        employee: whereMitra
      }
    });

    const todayFtwUnfit = await prisma.ftw_reports.count({
      where: {
        ftw_date: today,
        status_ftw: 'UNFIT',
        employee: whereMitra
      }
    });

    // 4. Total Lokasi Kerja
    const totalLocations = await prisma.locations.count();

    // 5. Trend Presensi 7 Hari Terakhir
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
                employee: whereMitra
            }
        });

        trend.push({
            date: ds,
            count
        });
    }

    // 6. Aktivitas Terbaru (5 Terakhir)
    const recentPresence = await prisma.attendances.findMany({
        where: {
            employee: whereMitra
        },
        include: {
            employee: {
                select: { 
                    full_name: true, 
                    mitra: { select: { mitra_name: true } } 
                }
            }
        },
        orderBy: { time_wita: 'desc' },
        take: 5
    });

    // 7. Breakdown per Mitra (Khusus Superadmin)
    let mitraBreakdown = [];
    if (!mitraId) {
        const mitrasData = await prisma.mitras.findMany({
            include: { 
                _count: { select: { employees: true } } 
            }
        });
        mitraBreakdown = mitrasData.map(m => ({
            name: m.mitra_name,
            count: m._count.employees
        }));
    }

    res.json({
        total_employees: totalEmployees,
        today_presence: todayPresence,
        today_ftw: { fit: todayFtwFit, unfit: todayFtwUnfit },
        total_locations: totalLocations,
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
