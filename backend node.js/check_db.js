const { PrismaClient } = require('@prisma/client');
const prisma = new PrismaClient();

async function main() {
  try {
    const tables = await prisma.$queryRaw`SHOW TABLES;`;
    console.log('Tables in database:', tables);
    
    const locationsDesc = await prisma.$queryRaw`DESCRIBE locations;`.catch(e => 'Table locations not found');
    console.log('Description of locations:', locationsDesc);

    const mitraDesc = await prisma.$queryRaw`DESCRIBE mitra_kerja;`.catch(e => 'Table mitra_kerja not found');
    console.log('Description of mitra_kerja:', mitraDesc);

  } catch (e) {
    console.error(e);
  } finally {
    await prisma.$disconnect();
  }
}

main();
