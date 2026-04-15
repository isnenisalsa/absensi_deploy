const fs = require('fs');
const path = require('path');

const searchDir = 'c:/Users/isnae/OneDrive/Dokumen/PKL PAMA PERSADA/Project HC/website_absensi_1pama_duplicate/frontend laravel_admin';
const searchText = 'No results found matching';

function walk(dir) {
  const files = fs.readdirSync(dir);
  for (const file of files) {
    const fullPath = path.join(dir, file);
    if (fs.statSync(fullPath).isDirectory()) {
      if (file !== 'node_modules' && file !== '.git') {
        walk(fullPath);
      }
    } else {
      const content = fs.readFileSync(fullPath, 'utf8');
      if (content.includes(searchText)) {
        console.log('FOUND IN:', fullPath);
      }
    }
  }
}

try {
  walk(searchDir);
} catch (e) {
  console.error(e);
}
