import { PrismaClient } from '@prisma/client';

const prisma = new PrismaClient();

async function main() {
  const users = await prisma.users.findMany({
    select: {
      nrp: true,
      role: true,
      is_active: true
    }
  });
  console.log('--- USERS IN DATABASE ---');
  console.table(users);
}

main()
  .catch((e) => {
    console.error(e);
    process.exit(1);
  })
  .finally(async () => {
    await prisma.$disconnect();
  });
