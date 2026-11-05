// Petit serveur pour le test vocal. Le micro du téléphone exige HTTPS, d'où le certificat local.
// Usage : node tools/voice-test/serve.mjs            (HTTPS, réseau local)
//         node tools/voice-test/serve.mjs --local    (HTTP, sur ce PC seulement)
import { execFileSync } from 'node:child_process';
import { existsSync, mkdirSync, readFileSync, writeFileSync } from 'node:fs';
import http from 'node:http';
import https from 'node:https';
import os from 'node:os';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = path.dirname(fileURLToPath(import.meta.url));
const PORT = 8443;
const LOCAL_ONLY = process.argv.includes('--local');
const MAX_BODY = 2 * 1024 * 1024;
const FILES = {
  '/': ['index.html', 'text/html; charset=utf-8'],
  '/index.html': ['index.html', 'text/html; charset=utf-8'],
  '/matcher.js': ['matcher.js', 'text/javascript; charset=utf-8'],
};

function lanAddresses() {
  return Object.values(os.networkInterfaces())
    .flat()
    .filter((net) => net.family === 'IPv4' && !net.internal)
    .map((net) => net.address);
}

function findOpenssl() {
  const candidates = ['openssl', 'C:\\Program Files\\Git\\usr\\bin\\openssl.exe', 'C:\\Program Files\\Git\\mingw64\\bin\\openssl.exe'];
  for (const candidate of candidates) {
    try {
      execFileSync(candidate, ['version'], { stdio: 'ignore' });
      return candidate;
    } catch {
      // Essai suivant.
    }
  }
  throw new Error('OpenSSL est introuvable : installez Git for Windows ou ajoutez openssl au PATH.');
}

// Le certificat est refait si l'adresse du PC a changé.
function certificate(addresses) {
  const dir = path.join(ROOT, '.cert');
  const keyFile = path.join(dir, 'key.pem');
  const certFile = path.join(dir, 'cert.pem');
  const stampFile = path.join(dir, 'addresses.txt');
  const stamp = addresses.join(',');
  const fresh = existsSync(certFile) && existsSync(stampFile) && readFileSync(stampFile, 'utf8') === stamp;
  if (!fresh) {
    mkdirSync(dir, { recursive: true });
    const altNames = ['DNS:localhost', 'IP:127.0.0.1', ...addresses.map((address) => `IP:${address}`)].join(',');
    execFileSync(findOpenssl(), [
      'req', '-x509', '-newkey', 'rsa:2048', '-nodes', '-sha256', '-days', '30',
      '-keyout', keyFile, '-out', certFile,
      '-subj', '/CN=BeeNote test vocal',
      '-addext', `subjectAltName=${altNames}`,
    ], { stdio: 'ignore' });
    writeFileSync(stampFile, stamp);
  }
  return { key: readFileSync(keyFile), cert: readFileSync(certFile) };
}

function saveResults(body) {
  const data = JSON.parse(body);
  if (!Array.isArray(data.runs)) throw new Error('Format inattendu');
  const dir = path.join(ROOT, 'results');
  mkdirSync(dir, { recursive: true });
  const name = `resultats-${new Date().toISOString().replace(/[:.]/g, '-')}.json`;
  writeFileSync(path.join(dir, name), JSON.stringify(data, null, 2));
  return name;
}

function handler(req, res) {
  const url = new URL(req.url, 'http://localhost');
  res.setHeader('Cache-Control', 'no-store');

  if (req.method === 'POST' && url.pathname === '/results') {
    let body = '';
    let tooLarge = false;
    req.on('data', (chunk) => {
      if (tooLarge) return;
      body += chunk;
      if (body.length > MAX_BODY) tooLarge = true;
    });
    req.on('end', () => {
      if (tooLarge) {
        res.writeHead(413).end();
        return;
      }
      try {
        const name = saveResults(body);
        console.log(`Résultats enregistrés : tools/voice-test/results/${name}`);
        res.writeHead(200, { 'Content-Type': 'application/json' }).end(JSON.stringify({ name }));
      } catch {
        res.writeHead(400).end();
      }
    });
    return;
  }

  const file = req.method === 'GET' ? FILES[url.pathname] : null;
  if (!file) {
    res.writeHead(404).end();
    return;
  }
  res.writeHead(200, { 'Content-Type': file[1] }).end(readFileSync(path.join(ROOT, file[0])));
}

if (LOCAL_ONLY) {
  http.createServer(handler).listen(PORT, '127.0.0.1', () => {
    console.log(`Test vocal, sur ce PC seulement : http://localhost:${PORT}/`);
  });
} else {
  const addresses = lanAddresses();
  https.createServer(certificate(addresses), handler).listen(PORT, () => {
    console.log('Test vocal prêt. Sur le téléphone, connecté au même Wi-Fi, ouvrez :');
    addresses.forEach((address) => console.log(`  https://${address}:${PORT}/`));
    console.log('Le navigateur préviendra que le certificat n\'est pas reconnu : c\'est normal, continuez.');
    console.log('Ctrl+C pour arrêter.');
  });
}
