import { PrismaClient } from '@prisma/client';
import bcrypt from 'bcrypt';

const prisma = new PrismaClient();

async function main() {
  console.log('Starting seed...');

  // 1. Create Default Mitra
  const defaultMitra = await prisma.mitras.upsert({
    where: { mitra_id: 1 },
    update: {},
    create: {
      mitra_id: 1,
      mitra_name: 'PAMA PERSADA (INTERNAL)',
      contact_pama: 'System Admin',
    },
  });

  // 2. Create Departments
  const depts = [
    { id: 1, name: 'HUMAN RESOURCES' },
    { id: 2, name: 'INFORMATION TECHNOLOGY' },
    { id: 3, name: 'OPERATION' },
    { id: 4, name: 'PLANT' },
  ];

  for (const dept of depts) {
    await prisma.departments.upsert({
      where: { dept_id: dept.id },
      update: { dept_name: dept.name },
      create: { dept_id: dept.id, dept_name: dept.name },
    });
  }

  // 3. Create Divisions
  const divs = [
    { id: 1, name: 'DEVELOPMENT' },
    { id: 2, name: 'INFRASTRUCTURE' },
    { id: 3, name: 'RECRUITMENT' },
    { id: 4, name: 'PRODUCTION' },
  ];

  for (const div of divs) {
    await prisma.divisions.upsert({
      where: { div_id: div.id },
      update: { div_name: div.name },
      create: { div_id: div.id, div_name: div.name },
    });
  }

  // 4. Create Initial Superadmin User
  const adminPassword = 'admin123';
  const salt = await bcrypt.genSalt(10);
  const hashedPassword = await bcrypt.hash(adminPassword, salt);

  await prisma.users.upsert({
    where: { nrp: 'admin' },
    update: {
      password_hash: hashedPassword,
      role: 'admin',
      is_active: true,
      mitra_id: defaultMitra.mitra_id,
    },
    create: {
      nrp: 'admin',
      password_hash: hashedPassword,
      role: 'admin',
      is_active: true,
      mitra_id: defaultMitra.mitra_id,
    },
  });

  // 5. Create Default Location (Geofence)
  await prisma.locations.upsert({
    where: { location_id: 1 },
    update: {},
    create: {
      location_id: 1,
      location_name: 'KANTOR PUSAT PAMA',
      latitude: -6.175392,
      longitude: 106.827153, // Monas example
      radius_meters: 100,
    },
  });

  // 6. Create Initial Shifts
  const shiftsData = [
    { id: 1, code: 'DS', in: '07:00:00', out: '19:00:00' },
    { id: 2, code: 'NS', in: '19:00:00', out: '07:00:00' },
  ];

  for (const s of shiftsData) {
    // Note: Prisma Time fields expect Date objects for @db.Time, 
    // but the date part is ignored
    const timeIn = new Date(`1970-01-01T${s.in}Z`);
    const timeOut = new Date(`1970-01-01T${s.out}Z`);

    await prisma.shifts.upsert({
      where: { shift_id: s.id },
      update: {
        shift_code: s.code,
        time_in_expected: timeIn,
        time_out_expected: timeOut,
      },
      create: {
        shift_id: s.id,
        shift_code: s.code,
        time_in_expected: timeIn,
        time_out_expected: timeOut,
      },
    });
  }

  console.log('Seed completed successfully.');
}

main()
  .catch((e) => {
    console.error(e);
    process.exit(1);
  })
  .finally(async () => {
    await prisma.$disconnect();
  });
