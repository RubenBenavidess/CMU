// ================================
// api.js (ES module)
// ================================

const API_ORIGIN = window.location.origin;

function buildUrl(endpoint) {
  const clean = endpoint.startsWith('/') ? endpoint.slice(1) : endpoint;
  const [pathPart, queryPart] = clean.split('?');
  const qs = queryPart ? `&${queryPart}` : '';
  return `${API_ORIGIN}/?path=${pathPart}${qs}`;
}

const JSON_HEADER = { 'Content-Type': 'application/json' };

export async function apiFetch(endpoint, opts = {}) {
  const { headers, body, ...rest } = opts;
  const finalHeaders = body instanceof FormData
    ? headers
    : { ...JSON_HEADER, ...headers };

  const res = await fetch(buildUrl(endpoint), {
    credentials: 'include',
    headers: finalHeaders,
    body,
    ...rest,
  });

  if (!res.ok) {
    let payload;
    try { payload = await res.json(); } catch { /* no-json */ }
    const msg = payload?.msg || res.statusText;
    throw new Error(msg);
  }

  const ct = res.headers.get('Content-Type') || '';
  return ct.includes('application/json') ? res.json() : res.blob();
}

export const Api = {
  // ── Autenticación ──
  login        : d => apiFetch('/api/login',    { method: 'POST', body: JSON.stringify(d) }),
  logout       : ()  => apiFetch('/api/logout',   { method: 'POST' }),
  register     : d => apiFetch('/api/register', { method: 'POST', body: JSON.stringify(d) }),
  loggedIn     : ()  => apiFetch('/api/isLoggedIn'),

  // Recursos:
  myResources: () => apiFetch('/api/resources/getByUser'),
  createResource: (idV, fd) => apiFetch(`/api/resources/create?idVariante=${idV}`, { method: 'POST', body: fd }),
  deleteResource: (idR) => apiFetch(`/api/resources/delete?idRecurso=${idR}`, { method: 'DELETE' }),
  downloadResource: (idR) => `${API_ORIGIN}/?path=api/resources/download&idRecurso=${idR}`,

  // *** Aquí va nuestro nuevo método para traer TODOS los recursos de una variante ***
  resourcesByVariant: (idV) => apiFetch(`/api/resources/getByVariant?idVariante=${idV}`),

  // Variantes / Asignaturas / Suscripciones:
  allSubjects: () => apiFetch('/api/subjects/getAll'),
  variantsBySubject: (id) => apiFetch(`/api/variants/getBySubject?idAsignatura=${id}`),
  userSubs: () => apiFetch('/api/userSubs'),
  subscribe: (idV) => apiFetch(`/api/createSub?idVariante=${idV}`, { method: 'POST' }),
  toggleSubState: (idSub, state) => apiFetch(`/api/updateSubState?idSuscripcion=${idSub}`, { method: 'PUT', body: JSON.stringify({ state }) })

};
