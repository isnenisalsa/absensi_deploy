const { PrismaClient } = require('@prisma/client');
const prisma = new PrismaClient();

async function main() {
  try {
    console.log('--- STARTING MIGRATION ---');

    console.log('1. Creating department_divisions table...');
    await prisma.$executeRaw`
      CREATE TABLE IF NOT EXISTS department_divisions (
        dept_id INT NOT NULL,
        div_id INT NOT NULL,
        PRIMARY KEY (dept_id, div_id),
        CONSTRAINT department_divisions_dept_id_fkey FOREIGN KEY (dept_id) REFERENCES departments(dept_id) ON DELETE CASCADE,
        CONSTRAINT department_divisions_div_id_fkey FOREIGN KEY (div_id) REFERENCES divisions(div_id) ON DELETE CASCADE
      ) ENGINE=InnoDB;
    `;

    console.log('2. Migrating existing data to join table...');
    await prisma.$executeRaw`
      INSERT IGNORE INTO department_divisions (dept_id, div_id)
      SELECT dept_id, div_id FROM divisions WHERE dept_id IS NOT NULL;
    `;

    console.log('3. Dropping foreign key from divisions table...');
    try {
      await prisma.$executeRaw`ALTER TABLE divisions DROP FOREIGN KEY divisions_dept_id_fkey;`;
    } catch(e) {
      console.log('   (FK divisions_dept_id_fkey might not exist or already dropped)');
    }

    console.log('4. Dropping dept_id column from divisions table...');
    await prisma.$executeRaw`ALTER TABLE divisions DROP COLUMN dept_id;`;

    console.log('--- MIGRATION COMPLETED SUCCESSFULLY ---');
  } catch (err) {
    console.error('--- MIGRATION FAILED ---');
    console.error(err);
  } finally {
    await prisma.$disconnect();
  }
}

main();
