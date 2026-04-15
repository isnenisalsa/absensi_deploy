const { PrismaClient } = require('@prisma/client');
const bcrypt = require('bcrypt');
const prisma = new PrismaClient();

async function main() {
  const nrp = 'ADM-3-cv--bina-i';
  const newPassword = 'Admin123';
  const hash = await bcrypt.hash(newPassword, 10);

  await prisma.users.update({
    where: { nrp },
    data: { password_hash: hash }
  });

  console.log('RESET SUCCESSFUL for ' + nrp);
}

main().catch(console.error).finally(() => prisma.$disconnect());
