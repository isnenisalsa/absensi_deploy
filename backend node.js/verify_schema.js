const { PrismaClient } = require('@prisma/client');
const prisma = new PrismaClient();

async function main() {
  try {
    console.log('--- Verifying Employees Table ---');
    const employeesDesc = await prisma.$queryRaw`DESCRIBE employees;`;
    console.log(employeesDesc);
    
    const hasDeptId = employeesDesc.some(col => col.Field === 'dept_id');
    if (hasDeptId) {
      console.log('SUCCESS: dept_id column found!');
    } else {
      console.log('FAILURE: dept_id column NOT found.');
    }

  } catch (e) {
    console.error(e);
  } finally {
    await prisma.$disconnect();
  }
}

main();
