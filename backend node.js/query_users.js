const { PrismaClient } = require('@prisma/client');
const prisma = new PrismaClient();

async function main() {
  const users = await prisma.users.findMany({
    where: { role: 'admin' },
    select: { nrp: true, role: true, mitra_id: true }
  });
  console.log('--- ADMIN USERS ---');
  console.table(users);
}

main().catch(console.error).finally(() => prisma.$disconnect());
