// FR-Nachrichten-Gerüst aus allen Yii::t('<category>', '<text>')-Aufrufen erzeugen.
// Werte bleiben LEER (Platzhalter) -> Yii fällt auf die deutschen Keys zurück,
// bis der Lektor FR füllt. Aufruf: node extract-i18n.mjs <moduleDir> <category> <outFile>
import { readdirSync, statSync, readFileSync, writeFileSync, mkdirSync } from 'node:fs';
import { join, dirname } from 'node:path';

const [, , moduleDir, category, outFile] = process.argv;
if (!moduleDir || !category || !outFile) {
  console.error('Usage: node extract-i18n.mjs <moduleDir> <category> <outFile>');
  process.exit(1);
}

function walk(dir) {
  const out = [];
  for (const name of readdirSync(dir)) {
    if (['messages', 'runtime', 'assets', 'vendor', '.git'].includes(name)) continue;
    const p = join(dir, name);
    const s = statSync(p);
    if (s.isDirectory()) out.push(...walk(p));
    else if (name.endsWith('.php')) out.push(p);
  }
  return out;
}

// Matcht Yii::t('cat', 'text' ...) – text mit \'-Escapes erlaubt.
const catEsc = category.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
const re = new RegExp(`Yii::t\\(\\s*'${catEsc}'\\s*,\\s*'((?:\\\\'|[^'])*)'`, 'g');

const messages = new Set();
for (const file of walk(moduleDir)) {
  const code = readFileSync(file, 'utf8');
  let m;
  while ((m = re.exec(code)) !== null) {
    // \' im Quelltext -> echtes ' für den Key
    messages.add(m[1].replace(/\\'/g, "'"));
  }
}

const sorted = [...messages].sort((a, b) => a.localeCompare(b, 'de'));
const lines = sorted.map((k) => `    '${k.replace(/'/g, "\\'")}' => '',`);
const php = `<?php
/**
 * ${category} – Französisch (FR). GERÜST: Werte bewusst leer; bis sie gefüllt sind,
 * zeigt die UI die deutschen Quelltexte. Auto-generiert via dev/extract-i18n.mjs.
 */
return [
${lines.join('\n')}
];
`;

mkdirSync(dirname(outFile), { recursive: true });
writeFileSync(outFile, php, 'utf8');
console.log(`${sorted.length} Strings -> ${outFile}`);
