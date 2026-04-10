import { PrismaClient } from '@prisma/client';

const prisma = new PrismaClient();

async function main() {
  console.log('🚀 Starting Database Purge...');

  const protectedAdmins = ['ADM-1-pt--kamaju', 'adminPama'];

  try {
    // 1. Transactional/Dependent Data
    console.log('🗑 Clearing Transactional Data...');
    await prisma.attendances.deleteMany({});
    await prisma.rosters.deleteMany({});
    await prisma.ftw_reports.deleteMany({});
    await prisma.user_sessions.deleteMany({});
    await prisma.employee_locations.deleteMany({});
    
    // 2. Master Data with FKs to above
    console.log('🗑 Clearing Employee Data...');
    await prisma.employees.deleteMany({});
    
    // 3. Other Master Data
    console.log('🗑 Clearing Other Master Data...');
    await prisma.position_locations.deleteMany({});
    await prisma.positions.deleteMany({});
    await prisma.divisions.deleteMany({});
    await prisma.departments.deleteMany({});
    await prisma.locations.deleteMany({});
    await prisma.shifts.deleteMany({});
    await prisma.mitras.deleteMany({});
    
    // 4. Users (Preserving specific Admins)
    console.log('🗑 Clearing Users (except protected admins)...');
    await prisma.users.deleteMany({
      where: {
        nrp: {
          notIn: protectedAdmins
        }
      }
    });

    console.log('✅ Database Purge Successful!');
    console.log('👑 Protected Admins retained:', protectedAdmins.join(', '));

  } catch (error) {
    console.error('❌ Error during purge:', error);
  } finally {
    await prisma.$disconnect();
  }
}

main();
