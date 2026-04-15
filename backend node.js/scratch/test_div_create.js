const { PrismaClient } = require('@prisma/client');
const prisma = new PrismaClient();

async function main() {
  try {
    console.log('Attempting to create a test division...');
    const test = await prisma.divisions.create({ 
      data: { 
        div_name: 'TEST DIVISI ' + Date.now() 
      } 
    });
    console.log('Successfully created:', test);
  } catch (err) {
    console.error('DATABASE ERROR:', err);
  } finally {
    await prisma.$disconnect();
  }
}

main();
