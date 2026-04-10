const { PrismaClient } = require('@prisma/client');
const prisma = new PrismaClient();

async function checkUsers() {
  try {
    const users = await prisma.users.findMany({
      include: { employee: true },
      take: 5
    });
    console.log("Current Users Sample:");
    users.forEach(u => {
      console.log(`NRP: ${u.nrp}, Name: ${u.employee?.full_name || 'No Emp Data'}, Role: ${u.role}`);
    });
  } catch (e) {
    console.error(e);
  } finally {
    await prisma.$disconnect();
  }
}

checkUsers();
