async function request(method: string, path: string, body?: unknown) {
  const opts: RequestInit = {
    method,
    headers: { 'Content-Type': 'application/json' },
    credentials: 'include',
  };
  if (body) opts.body = JSON.stringify(body);

  const res = await fetch(`/api/v1${path}`, opts);
  const json = await res.json();
  if (!json.success) throw new Error(json.message || 'Request failed');
  return json;
}

export const api = {
  get: (path: string) => request('GET', path),
  post: (path: string, body: unknown) => request('POST', path, body),
  put: (path: string, body: unknown) => request('PUT', path, body),
  del: (path: string) => request('DELETE', path),
};
