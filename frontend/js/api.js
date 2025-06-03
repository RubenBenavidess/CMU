// ================================
// api.js  (ES module)
// Utilidad central para todas las peticiones fetch → backend PHP
// ================================
import fetch from 'node-fetch';


const API_ORIGIN = window.location.origin;            //  http://localhost:8000  (mismo host)
const JSON_HEADER = { 'Content-Type': 'application/json' };

/**
 * wrapper con credenciales incluidas + manejo de errores homogéneo
 * @param {string} endpoint  ej: '/api/login'
 * @param {RequestInit} opts  opciones extra para fetch
 * @returns {Promise<any>}   json decodificado (ya Parseado)
 */
export async function apiFetch(endpoint, opts = {}) {
  const { headers, body, ...rest } = opts;
  const finalHeaders = body instanceof FormData ? headers : { ...JSON_HEADER, ...headers };

  const res = await fetch(`${API_ORIGIN}${endpoint}`, {
    credentials: 'include',
    headers: finalHeaders,
    body,
    ...rest,
  });

  if (!res.ok) {
    // intenta extraer msg del backend
    let payload;
    try { payload = await res.json(); } catch { /* binary / vacío */ }
    const msg = payload?.msg || res.statusText;
    throw new Error(msg);
  }
  // si es descarga binaria (PDF) deja al consumidor decidir
  const ct = res.headers.get('Content-Type') || '';
  return ct.includes('application/json') ? res.json() : res.blob();
}

export const Api = {
  login:  (data) => apiFetch('/api/login',  { method: 'POST', body: JSON.stringify(data) }),
  logout: () => apiFetch('/api/logout',    { method: 'POST' }),
  register: (data) => apiFetch('/api/register', { method: 'POST', body: JSON.stringify(data) }),
  loggedIn: () => apiFetch('/api/isLoggedIn'),
  // recursos
  myResources: () => apiFetch('/api/resources/getByUser'),
  createResource: (idVariante, fd) => apiFetch(`/api/resources/create?idVariante=${idVariante}`, { method: 'POST', body: fd }),
  deleteResource: (idRecurso) => apiFetch(`/api/resources/delete?idRecurso=${idRecurso}`, { method: 'DELETE' }),
  downloadResource: (idRecurso) => `${API_ORIGIN}/api/resources/download?idRecurso=${idRecurso}`,
  // variantes / asignaturas
  allSubjects: () => apiFetch('/api/subjects/getAll'), // si existiera
  allVariants: () => apiFetch('/api/variants/getAll'),
  variantsBySubject: (id) => apiFetch(`/api/variants/getBySubject?idAsignatura=${id}`),
  userSubs: () => apiFetch('/api/userSubs'),
  subscribe: (idVariante) => apiFetch(`/api/createSub?idVariante=${idVariante}`, { method: 'POST' }),
  toggleSubState: (idSuscripcion, state) => apiFetch(`/api/updateSubState?idSuscripcion=${idSuscripcion}`, { method: 'PUT', body: JSON.stringify({ state }) }),
};
