const { PrismaClient } = require('@prisma/client');
const prisma = new PrismaClient();

async function main() {
  try {
    const users = await prisma.users.findMany({
      select: {
        nrp: true,
        role: true,
        is_active: true
      }
    });
    console.log('--- USERS IN DATABASE ---');
    console.table(users);
  } catch (error) {
    console.error('Error querying users:', error);
  } finally {
    await prisma.$disconnect();
  }
}

main();
