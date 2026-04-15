const { PrismaClient } = require('@prisma/client');
const prisma = new PrismaClient();

async function main() {
  try {
    const result = await prisma.$queryRaw`
      SELECT CONSTRAINT_NAME
      FROM information_schema.KEY_COLUMN_USAGE
      WHERE TABLE_NAME = 'divisions'
      AND COLUMN_NAME = 'dept_id'
      AND TABLE_SCHEMA = 'absensi_db'
    `;
    console.log('Foreign Key Info:', JSON.stringify(result, null, 2));
  } catch (err) {
    console.error('ERROR:', err);
  } finally {
    await prisma.$disconnect();
  }
}

main();
