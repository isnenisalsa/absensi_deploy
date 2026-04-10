const { PrismaClient } = require('@prisma/client');
const prisma = new PrismaClient();

async function check() {
  try {
    const nrp = 'AR260011'; // A real user
    const now = new Date();
    
    // Start and end of today in local time
    const startOfDay = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    const endOfDay = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 23, 59, 59, 999);

    // 1. Get Today's Roster
    const roster = await prisma.rosters.findFirst({
        where: {
            nrp,
            date: {
                gte: startOfDay,
                lte: endOfDay
            }
        },
        include: { shift: true }
    });

    console.log("ROSTER:", roster);

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
    
    console.log("ATTENDANCES:", attendances);
  } catch(e) {
    console.error("ERROR", e);
  } finally {
    prisma.$disconnect();
  }
}
check();
