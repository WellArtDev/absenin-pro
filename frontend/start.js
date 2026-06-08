const { createServer } = require('http');
const { parse } = require('url');

const port = process.env.PORT || 3000;
const apiUrl = process.env.NEXT_PUBLIC_API_URL || 'https://api.absenin.com';

async function proxyToApi(req, res) {
  const target = new URL(req.url.replace(/^\/api\/v1/, '/api/v1'), apiUrl);
  const headers = { 'Content-Type': req.headers['content-type'] || 'application/json' };
  if (req.headers['authorization']) headers['Authorization'] = req.headers['authorization'];
  if (req.headers['cookie']) headers['Cookie'] = req.headers['cookie'];

  const body = req.method !== 'GET' && req.method !== 'HEAD'
    ? await new Promise(resolve => { let b = ''; req.on('data', c => b += c); req.on('end', () => resolve(b)); })
    : undefined;

  try {
    const r = await fetch(target.toString(), { method: req.method, headers, body });
    const text = await r.text();
    res.setHeader('Content-Type', r.headers.get('content-type') || 'application/json');

    try {
      const data = JSON.parse(text);
      if (data.success && data.data?.access_token) {
        res.setHeader('Set-Cookie', `jwt=${data.data.access_token}; Path=/; HttpOnly; SameSite=Lax; Domain=.absenin.com; Max-Age=${data.data.expires_in || 86400}`);
      }
    } catch (_) {}

    const sc = r.headers.get('set-cookie');
    if (sc) res.setHeader('Set-Cookie', sc);

    res.statusCode = r.status;
    res.end(text);
  } catch {
    res.statusCode = 502;
    res.end(JSON.stringify({ success: false, error: 'Bad Gateway' }));
  }
}

try {
  const next = require('next');
  const app = next({ dev: false, hostname: '0.0.0.0', port });
  const handle = app.getRequestHandler();

  app.prepare().then(() => {
    createServer((req, res) => {
      const parsedUrl = parse(req.url, true);
      if (parsedUrl.pathname?.startsWith('/api/v1/')) return proxyToApi(req, res);
      handle(req, res, parsedUrl);
    }).listen(port, () => console.log(`Absenin Dashboard: http://0.0.0.0:${port}`));
  }).catch(err => { console.error('Next.js failed:', err.message); process.exit(1); });
} catch (e) { console.error('Next.js not found, run npm install'); process.exit(1); }
