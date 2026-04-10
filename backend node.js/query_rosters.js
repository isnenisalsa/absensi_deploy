const { PrismaClient } = require('@prisma/client');
const prisma = new PrismaClient();

async function main() {
  const rosters = await prisma.rosters.findMany({
    include: { shift: true }
  });
  console.log("All Rosters in DB:");
  rosters.forEach(r => {
    console.log(`NRP: ${r.nrp}, Date: ${r.date.toISOString()}, Shift: ${r.shift?.shift_name || 'NULL'}, ShiftID: ${r.shift_id}`);
  });
}
main().finally(() => prisma.$disconnect());
