const { PrismaClient } = require('@prisma/client');
const prisma = new PrismaClient();

async function main() {
  try {
    console.log('--- TABLES ---');
    const tables = await prisma.$queryRaw`SHOW TABLES`;
    console.log(JSON.stringify(tables, null, 2));

    console.log('\n--- COLUMNS divisions ---');
    const divCols = await prisma.$queryRaw`SHOW COLUMNS FROM divisions`;
    console.log(JSON.stringify(divCols, null, 2));

    console.log('\n--- COLUMNS department_divisions (if exists) ---');
    try {
      const ddCols = await prisma.$queryRaw`SHOW COLUMNS FROM department_divisions`;
      console.log(JSON.stringify(ddCols, null, 2));
    } catch(e) {
      console.log('department_divisions table does not exist.');
    }

  } catch (err) {
    console.error('ERROR:', err);
  } finally {
    await prisma.$disconnect();
  }
}

main();
