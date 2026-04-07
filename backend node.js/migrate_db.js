const { PrismaClient } = require('@prisma/client');
const prisma = new PrismaClient();

async function main() {
  try {
    console.log('--- Database Migration: Mitra Kerja to Locations ---');
    
    // 1. Rename mitra_kerja to locations
    console.log('Renaming mitra_kerja to locations...');
    await prisma.$executeRawUnsafe('RENAME TABLE mitra_kerja TO locations;');
    
    // 2. Rename columns in locations
    console.log('Renaming columns in locations...');
    await prisma.$executeRawUnsafe('ALTER TABLE locations CHANGE mitra_kerja_id location_id INT AUTO_INCREMENT;');
    await prisma.$executeRawUnsafe('ALTER TABLE locations CHANGE mitra_kerja_name location_name VARCHAR(255);');
    
    // 3. Rename columns in employees
    console.log('Renaming column in employees...');
    await prisma.$executeRawUnsafe('ALTER TABLE employees CHANGE mitra_kerja_id location_id INT;');

    // 4. Cleanup districts column in employees if it exists
    console.log('Checking for old dist_id column...');
    const employeesDesc = await prisma.$queryRaw`DESCRIBE employees;`;
    if (employeesDesc.find(col => col.Field === 'dist_id')) {
        console.log('Dropping old dist_id column...');
        await prisma.$executeRawUnsafe('ALTER TABLE employees DROP COLUMN dist_id;');
    }

    console.log('--- Migration Completed Successfully ---');

  } catch (e) {
    console.error('Migration Failed:', e);
  } finally {
    await prisma.$disconnect();
  }
}

main();
