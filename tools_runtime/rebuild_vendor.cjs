#!/usr/bin/env node
/* Offline vendor rebuilder: downloads pinned packages from codeload (no API needed),
   lays out vendor/, generates composer-style autoload files + metadata,
   and applies the termwind ext-dom fallback patch. */
const { execFile, execSync } = require('child_process');
const fs = require('fs');
const path = require('path');

const ROOT = '/home/user/dhakafin';
const VENDOR = path.join(ROOT, 'vendor');
const WORK = '/tmp/pkgwork';
fs.mkdirSync(WORK, { recursive: true });
const pinned = require(path.join(ROOT, 'tools_runtime/pinned.json'));
const rootCj = JSON.parse(fs.readFileSync(path.join(ROOT, 'composer.json'), 'utf8'));

(async () => {
  const jobs = Object.entries(pinned).map(([name, r]) => ({
    name, repo: r.repo, tag: r.tag,
    f: path.join(WORK, name.replace('/', '__') + '.tgz'),
  }));

  // download (6-way parallel, retry)
  async function dl(j) {
    for (let a = 1; a <= 4; a++) {
      const ok = await new Promise(res => execFile('curl', ['-sfL', '-m', '180', '-o', j.f,
        `https://codeload.github.com/${j.repo}/tar.gz/${encodeURIComponent(j.tag)}`], e => res(!e)));
      if (ok && fs.existsSync(j.f) && fs.statSync(j.f).size > 1000) return true;
      await new Promise(r => setTimeout(r, 1500 * a));
    }
    return false;
  }
  const pending = jobs.filter(j => !(fs.existsSync(j.f) && fs.statSync(j.f).size > 1000));
  console.log(`downloading ${pending.length}/${jobs.length} packages...`);
  for (let i = 0; i < pending.length; i += 6) {
    const res = await Promise.all(pending.slice(i, i + 6).map(dl));
    res.forEach((ok, k) => { if (!ok) throw new Error('download failed: ' + pending[i + k].name); });
    process.stdout.write(`  ${Math.min(i + 6, pending.length)}\r`);
  }

  // extract
  console.log('extracting...');
  fs.rmSync(VENDOR, { recursive: true, force: true });
  fs.mkdirSync(VENDOR, { recursive: true });
  for (const j of jobs) {
    const dest = path.join(VENDOR, j.name);
    execSync(`mkdir -p '${dest}' && tar xzf '${j.f}' -C '${dest}' --strip-components=1`);
  }
  execSync(`find ${VENDOR} -type d \\( -iname tests -o -iname docs \\) -prune -exec rm -rf {} + 2>/dev/null || true`);

  // composer runtime sources
  const cf = path.join(WORK, 'composer-src.tgz');
  if (!fs.existsSync(cf)) execSync(`curl -sfL -o '${cf}' 'https://codeload.github.com/composer/composer/tar.gz/refs/tags/2.10.3'`);
  fs.rmSync(`${WORK}/compsrc`, { recursive: true, force: true });
  execSync(`mkdir -p ${WORK}/compsrc && tar xzf '${cf}' -C ${WORK}/compsrc --strip-components=1`);
  const cDir = `${VENDOR}/composer`;
  fs.mkdirSync(cDir, { recursive: true });
  fs.copyFileSync(`${WORK}/compsrc/src/Composer/Autoload/ClassLoader.php`, `${cDir}/ClassLoader.php`);
  fs.copyFileSync(`${WORK}/compsrc/src/Composer/InstalledVersions.php`, `${cDir}/InstalledVersions.php`);

  // autoload maps
  const psr4 = {}, psr0 = {}, files = [], classmapDirs = [];
  const installed = [];
  function addAuto(auto, baseDir, key) {
    for (const [ns, dirs] of Object.entries(auto['psr-4'] || {}))
      for (const d of Array.isArray(dirs) ? dirs : [dirs])
        (psr4[ns] = psr4[ns] || []).push(path.join(baseDir, d).replace(/\/+$/, ''));
    for (const [ns, dirs] of Object.entries(auto['psr-0'] || {}))
      for (const d of Array.isArray(dirs) ? dirs : [dirs])
        (psr0[ns] = psr0[ns] || []).push(path.join(baseDir, d).replace(/\/+$/, ''));
    for (const f of auto.files || []) {
      const full = path.join(baseDir, f);
      if (fs.existsSync(full)) files.push({ id: Buffer.from(key + ':' + f).toString('base64url'), path: full });
    }
    for (const d of auto.classmap || []) classmapDirs.push(path.join(baseDir, d));
  }
  addAuto(rootCj.autoload || {}, ROOT, 'root');
  for (const [name, r] of Object.entries(pinned)) {
    const dir = path.join(VENDOR, name);
    const cj = JSON.parse(fs.readFileSync(path.join(dir, 'composer.json'), 'utf8'));
    addAuto(cj.autoload || {}, dir, name);
    installed.push({ name, version: r.tag.replace(/^v/, ''), repo: r.repo, tag: r.tag, cj });
  }

  const classmap = {};
  function scan(pth) {
    if (!fs.existsSync(pth)) return;
    const st = fs.statSync(pth);
    if (!st.isDirectory()) {
      if (!pth.endsWith('.php')) return;
      const src = fs.readFileSync(pth, 'utf8');
      const nsM = src.match(/^\s*namespace\s+([\w\\\\]+)\s*;/m);
      const ns = nsM ? nsM[1] + '\\' : '';
      for (const d of src.matchAll(/^\s*(?:(?:abstract|final|readonly)\s+)*(?:class|interface|trait|enum)\s+([A-Za-z_][\w]*)/gm))
        classmap[ns + d[1]] = pth;
      return;
    }
    for (const it of fs.readdirSync(pth)) scan(path.join(pth, it));
  }
  for (const d of classmapDirs) scan(d);

  const rel = p => path.relative(VENDOR, p).replace(/\\/g, '/');
  const mapPhp = (obj, indent) => Object.entries(obj).map(([ns, dirs]) => {
    const k = ns.replace(/\\/g, '\\\\').replace(/'/g, "\\'");
    return `${indent}'${k}' => array(${dirs.map(d => `\$vendorDir . '/${rel(d)}'`).join(', ')}),`;
  }).join('\n');

  fs.writeFileSync(`${cDir}/autoload_psr4.php`, `<?php\n\n$vendorDir = dirname(__DIR__);\n\nreturn array(\n${mapPhp(psr4, '    ')}\n);\n`);
  fs.writeFileSync(`${cDir}/autoload_namespaces.php`, `<?php\n\n$vendorDir = dirname(__DIR__);\n\nreturn array(\n${mapPhp(psr0, '    ')}\n);\n`);
  fs.writeFileSync(`${cDir}/autoload_classmap.php`, `<?php\n\n$vendorDir = dirname(__DIR__);\n\nreturn array(\n${Object.entries(classmap).sort((a, b) => a[0].localeCompare(b[0])).map(([k, v]) => `    '${k.replace(/\\/g, '\\\\').replace(/'/g, "\\'")}' => \$vendorDir . '/${rel(v)}',`).join('\n')}\n);\n`);
  fs.writeFileSync(`${cDir}/autoload_files.php`, `<?php\n\n$vendorDir = dirname(__DIR__);\n\nreturn array(\n${files.map(x => `    '${x.id}' => \$vendorDir . '/${rel(x.path)}',`).join('\n')}\n);\n`);

  fs.writeFileSync(`${VENDOR}/autoload.php`, `<?php

require_once __DIR__ . '/composer/ClassLoader.php';

$loader = new \\Composer\\Autoload\\ClassLoader(__DIR__);

foreach (require __DIR__ . '/composer/autoload_namespaces.php' as $ns => $paths) {
    $loader->set($ns, $paths);
}
foreach (require __DIR__ . '/composer/autoload_psr4.php' as $ns => $paths) {
    $loader->setPsr4($ns, $paths);
}
$classMap = require __DIR__ . '/composer/autoload_classmap.php';
if ($classMap) {
    $loader->addClassMap($classMap);
}
$loader->register(true);

$includeFiles = require __DIR__ . '/composer/autoload_files.php';
foreach ($includeFiles as $fileIdentifier => $file) {
    if (empty($GLOBALS['__composer_autoload_files'][$fileIdentifier])) {
        $GLOBALS['__composer_autoload_files'][$fileIdentifier] = true;
        require $file;
    }
}

return $loader;
`);

  fs.writeFileSync(`${cDir}/installed.json`, JSON.stringify({
    packages: installed.map(p => ({
      name: p.name, version: p.version,
      version_normalized: (p.version.split('.').concat(['0','0','0']).slice(0, 4).map(x => String(parseInt(x) || 0).padStart(9, '0')).join('.')),
      type: p.cj.type || 'library', license: p.cj.license || [],
      source: { type: 'git', url: `https://github.com/${p.repo}.git`, reference: p.tag },
      require: p.cj.require || {}, replace: p.cj.replace || {}, provide: p.cj.provide || {},
      conflict: p.cj.conflict || {},
      'install-path': p.name, autoload: p.cj.autoload || {}, extra: p.cj.extra || {},
    })),
    'dev-package-names': [], dev: false,
  }, null, 4));

  fs.writeFileSync(`${cDir}/installed.php`, `<?php return array(
    'root' => array(
        'pretty_version' => '1.0.0', 'version' => '1.0.0.0', 'reference' => null,
        'type' => 'project', 'install_path' => __DIR__ . '/../../',
        'aliases' => array(), 'dev' => false, 'name' => 'laravel/laravel',
    ),
    'versions' => array(
${installed.map(p => `        '${p.name}' => array(
            'pretty_version' => '${p.version}', 'version' => '${p.version}',
            'reference' => '${p.tag}', 'type' => '${p.cj.type || 'library'}',
            'install_path' => __DIR__ . '/../${p.name}',
            'aliases' => array(), 'dev_requirement' => false,
        ),`).join('\n')}
    ),
);
`);

  // termwind ext-dom fallback patch
  const tw = `${VENDOR}/nunomaduro/termwind/src/HtmlRenderer.php`;
  let src = fs.readFileSync(tw, 'utf8');
  src = src.replace(
    `        $dom = new DOMDocument;\n\n        if (strip_tags($html) === $html) {`,
    `        if (! class_exists('DOMDocument')) {
            $plain = preg_replace('/<br\\\\s*\\\\/?>/i', "\\n", $html);
            $plain = preg_replace('/<\\\\/(div|p|li|tr|h[1-6]|table)>/i', "\\n", $plain);
            $plain = preg_replace('/<li[^>]*>/i', ' • ', $plain);
            $plain = preg_replace('/<table[^>]*>|<td[^>]*>/i', ' ', $plain);
            $plain = html_entity_decode(strip_tags($plain), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $plain = preg_replace("/\\\\n{3,}/", "\\n\\n", $plain);
            return Termwind::span(trim($plain));
        }

        $dom = new DOMDocument;

        if (strip_tags($html) === $html) {`
  );
  if (!src.includes("class_exists('DOMDocument')")) throw new Error('termwind patch failed to apply');
  fs.writeFileSync(tw, src);

  fs.mkdirSync(`${ROOT}/bootstrap/cache`, { recursive: true });
  console.log(`vendor ready: ${installed.length} packages`);
  console.log('[VENDOR COMPLETE]');
})().catch(e => { console.error('FAILED:', e.message); process.exit(1); });
