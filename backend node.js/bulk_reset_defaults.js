const { PrismaClient } = require('@prisma/client');
const bcrypt = require('bcrypt');
const prisma = new PrismaClient();

async function main() {
  const users = await prisma.users.findMany({
    where: { 
      role: 'admin', 
      mitra_id: { not: null } 
    }
  });

  console.log(`Menemukan ${users.length} akun Admin Mitra. Memulai reset ke password default...`);

  for (const user of users) {
    // Format Default: P4ssw0rd4ria[NRP]
    const defaultPass = 'P4ssw0rd4ria' + user.nrp;
    const hash = await bcrypt.hash(defaultPass, 10);
    
    await prisma.users.update({
      where: { nrp: user.nrp },
      data: { password_hash: hash }
    });
    
    console.log(`[SUKSES] ${user.nrp} dikembalikan ke password default.`);
  }

  console.log('--- SEMUA AKUN TELAH DIRESET KE DEFAULT ---');
}

main()
  .catch(console.error)
  .finally(async () => {
    await prisma.$disconnect();
  });
