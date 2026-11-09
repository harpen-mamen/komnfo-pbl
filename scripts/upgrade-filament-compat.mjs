import { readdirSync, readFileSync } from 'node:fs';
import { join } from 'node:path';

const files = [];
function scan(directory) {
  for (const entry of readdirSync(directory, { withFileTypes: true })) {
    const path = join(directory, entry.name);
    if (entry.isDirectory()) scan(path);
    else if (/\.(php|tsx|ts)$/.test(path)) files.push(path);
  }
}
for (const directory of ['app/Filament', 'app/Http', 'resources/views/filament', 'routes', 'frontend/src']) scan(directory);

let patch = '*** Begin Patch\n';
for (const file of files) {
  const before = readFileSync(file, 'utf8').split(/\r?\n/);
  const after = before.map((line) => {
    let result = line;
    if (file.startsWith('frontend')) {
      return result.replaceAll('bg-gradient-to-', 'bg-linear-to-')
        .replace(/<a(\s[^>]*href="\/(?!\/)[^"]*"[^>]*)>(.*?)<\/a>/g, '<Link$1>$2</Link>')
        .replaceAll('catch (e) {}', 'catch {}');
    }
    result = result.replace(/(<\/?x-filament)::page(?=[\s>])/g, '$1-panels::page');
    result = result.replace(/(<\/?x-filament)::widget(?=[\s>])/g, '$1-widgets::widget');
    result = result.replace(/(<\/?x-filament)::card(?=[\s>])/g, '$1::section');
    result = result.replace(/(\b(?:route|name|has)\s*\(\s*['"])filament\.(pages|resources|auth)\./g, '$1filament.admin.$2.');
    result = result.replaceAll('filament::components.layouts.base', 'filament-panels::components.layout.base');
    result = result.replaceAll('use Filament\\Pages\\Actions;', 'use Filament\\Actions;');
    result = result.replace(/\$this->notify\('(success|warning|danger|info)', (.+)\);/, (_, status, message) => `\\Filament\\Notifications\\Notification::make()->${status}()->title(${message})->send();`);
    return result;
  });
  if (file.startsWith('frontend') && after.some((line) => line.includes('<Link')) && ! before.some((line) => /import Link from/.test(line))) {
    after[0] = "import Link from 'next/link';\n" + after[0];
  }
  const changed = before.flatMap((line, index) => line === after[index] ? [] : [index]);
  if (changed.length === 0) continue;
  patch += `*** Update File: ${file.replaceAll('\\', '/')}\n`;
  const groups = [];
  for (const index of changed) {
    const start = Math.max(0, index - 2);
    const end = Math.min(before.length - 1, index + 2);
    if (groups.length && start <= groups.at(-1).end + 1) groups.at(-1).end = end;
    else groups.push({ start, end });
  }
  for (const { start, end } of groups) {
    patch += '@@\n';
    for (let index = start; index <= end; index++) {
      patch += before[index] === after[index] ? ` ${before[index]}\n` : `-${before[index]}\n` + after[index].split('\n').map((line) => `+${line}\n`).join('');
    }
  }
}
patch += '*** End Patch';
process.stdout.write(patch);
