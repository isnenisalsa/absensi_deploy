const { PrismaClient } = require('@prisma/client');
const prisma = new PrismaClient();

async function main() {
  try {
    console.log('Fetching divisions...');
    const divisions = await prisma.divisions.findMany();
    console.log('Result:', JSON.stringify(divisions, null, 2));
    
    console.log('Attempting to create a test division...');
    // const test = await prisma.divisions.create({ data: { div_name: 'DEBUG TEST' } });
    // console.log('Successfully created:', test);
  } catch (err) {
    console.error('ERROR:', err);
  } finally {
    await prisma.$disconnect();
  }
}

main();
