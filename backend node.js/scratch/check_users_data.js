const { PrismaClient } = require('@prisma/client');
const prisma = new PrismaClient();

async function check() {
  const users = await prisma.users.findMany({
    where: {
      role: { in: ['admin', 'admin_mitra'] }
    },
    include: {
      employee: {
        select: {
          full_name: true,
          position: true,
          division: true
        }
      },
      mitra: {
        select: {
          mitra_name: true
        }
      }
    }
  });

  console.log(JSON.stringify(users, null, 2));
}

check().catch(console.error).finally(() => prisma.$disconnect());
