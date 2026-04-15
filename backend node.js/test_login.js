const { PrismaClient } = require('@prisma/client');
const bcrypt = require('bcrypt');
const prisma = new PrismaClient();

async function main() {
  const nrp = 'ADM-3-cv--bina-i';
  const password = 'P4ssw0rd4ria' + nrp;
  
  const user = await prisma.users.findUnique({
    where: { nrp }
  });

  if (!user) {
    console.log('User not found');
    return;
  }

  const isMatch = await bcrypt.compare(password, user.password_hash);
  console.log(`NRP: ${nrp}`);
  console.log(`Password tested: ${password}`);
  console.log(`Hash in DB: ${user.password_hash}`);
  console.log(`Match? ${isMatch}`);
}

main().catch(console.error).finally(() => prisma.$disconnect());
