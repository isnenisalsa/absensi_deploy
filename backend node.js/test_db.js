const { PrismaClient } = require('@prisma/client');
const prisma = new PrismaClient();
async function main() {
  const attendances = await prisma.attendances.findMany({
    orderBy: { attendance_date: 'desc' },
    take: 5
  });
  console.log("Recent attendances:", attendances);
}
main().catch(console.error).finally(()=>prisma.$disconnect());
