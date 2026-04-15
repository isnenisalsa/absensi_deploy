const { PrismaClient } = require('@prisma/client');
const bcrypt = require('bcrypt');
const prisma = new PrismaClient();

async function main() {
  const users = await prisma.users.findMany({
    where: { nrp: { startsWith: 'ADM-' } }
  });

  for (const user of users) {
    if (user.nrp === 'adminPama') continue;
    const defaultPassword = 'P4ssw0rd4ria' + user.nrp;
    const isMatch = await bcrypt.compare(defaultPassword, user.password_hash);
    console.log(`NRP: ${user.nrp} | Match: ${isMatch}`);
  }
}

main().catch(console.error).finally(() => prisma.$disconnect());
