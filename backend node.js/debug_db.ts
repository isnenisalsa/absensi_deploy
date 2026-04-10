
import { PrismaClient } from '@prisma/client';

const prisma = new PrismaClient();

async function main() {
  console.log("Checking database...");
  const shifts = await prisma.shifts.findMany();
  console.log("Shifts found:", shifts.length);
  shifts.forEach(s => console.log(`- ID: ${s.shift_id}, Code: ${s.shift_code}`));

  const locations = await prisma.locations.findMany();
  console.log("Locations found:", locations.length);
  locations.forEach(l => console.log(`- ID: ${l.location_id}, Name: ${l.location_name}`));

  const employees = await prisma.employees.findFirst();
  console.log("Sample Employee:", employees?.nrp, employees?.full_name);
}

main().catch(e => {
  console.error(e);
  process.exit(1);
}).finally(() => prisma.$disconnect());
