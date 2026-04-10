const { PrismaClient } = require('@prisma/client');
const prisma = new PrismaClient();
async function main() {
  const shifts = await prisma.shifts.findMany();
  console.log(shifts);
}
main().catch(console.error).finally(()=>prisma.$disconnect());
