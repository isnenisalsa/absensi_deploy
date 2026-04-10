import 'dotenv/config';
import { PrismaClient } from '@prisma/client';
const prisma = new PrismaClient();

async function check() {
  try {
    const locs = await prisma.locations.findMany();
    console.log("LOCATIONS:", locs);
  } catch(e) {
    console.error("ERROR CONNECTING", e);
  } finally {
    prisma.$disconnect();
  }
}
check();
