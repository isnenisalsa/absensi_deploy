import { PrismaClient } from '@prisma/client';
const prisma = new PrismaClient();
async function main() {
  const now = new Date();
  try {
    const nrp = 'AR260062'; // Dummy or real
    const attendance = await prisma.attendances.create({
      data: {
        nrp,
        attendance_date: now,
        date_in: now,
        time_wita: now, 
        time_wib: new Date(now.getTime() - 60 * 60 * 1000), 
        shift_id: 1,
        trans_type: 'Check_in',
        cp_location: "SBT",
        work_location: "Site A",
        att_latitude: -3.65,
        att_longitude: 115.35,
        photo_evidence: "url"
      }
    });
    console.log("Success:", attendance);
  } catch (e) {
    console.error("Prisma error:", e);
  } finally {
    await prisma.$disconnect();
  }
}
main();
