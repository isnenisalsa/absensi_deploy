const { PrismaClient } = require('@prisma/client');
const prisma = new PrismaClient();

async function main() {
  try {
    console.log('Checking divisions table columns...');
    const columns = await prisma.$queryRaw`SHOW COLUMNS FROM divisions`;
    console.log('Columns:', JSON.stringify(columns, null, 2));
  } catch (err) {
    console.error('ERROR:', err);
  } finally {
    await prisma.$disconnect();
  }
}

main();
