const { createServer } = require('http');
const { parse } = require('url');
const next = require('next');

const dev = false;
const hostname = '0.0.0.0';
const port = process.env.PORT || 3000;

const app = next({ dev, hostname, port });
const handle = app.getRequestHandler();

const proxyToApi = async (req, res) => {
  const apiUrl = process.env.NEXT_PUBLIC_API_URL || 'https://api.absenin.com';
  const target = new URL(req.url.replace(/^\/api\/v1/, '/api/v1'), apiUrl);

  try {
    const fetchRes = await fetch(target.toString(), {
      method: req.method,
      headers: {
        'Content-Type': req.headers['content-type'] || 'application/json',
        Authorization: req.headers['authorization'] || '',
      },
      body: req.method !== 'GET' && req.method !== 'HEAD' ? await new Promise((resolve) => {
        let body = '';
        req.on('data', (c) => body += c);
        req.on('end', () => resolve(body));
      }) : undefined,
    });

    const data = await fetchRes.json();
    res.setHeader('Content-Type', 'application/json');

    const setCookie = fetchRes.headers.get('set-cookie');
    if (setCookie) res.setHeader('Set-Cookie', setCookie);

    res.statusCode = fetchRes.status;
    res.end(JSON.stringify(data));
  } catch {
    res.statusCode = 502;
    res.end(JSON.stringify({ success: false, error: 'Bad Gateway' }));
  }
};

app.prepare().then(() => {
  createServer((req, res) => {
    const parsedUrl = parse(req.url, true);

    if (parsedUrl.pathname?.startsWith('/api/v1/')) {
      return proxyToApi(req, res);
    }

    handle(req, res, parsedUrl);
  }).listen(port, hostname, () => {
    console.log(`Absenin dashboard running on http://${hostname}:${port}`);
  });
});
