// ================================
// router.js  (ES module – micro‑router hash‑based)
// Solo lo usaremos para alternar paneles dentro de main.html sin recargar.
// ================================



export class Router {
  constructor() {
    this.routes = {};
    window.addEventListener('hashchange', () => this._load(location.hash));
  }
  on(hash, cb) { this.routes[hash] = cb; }
  init()        { this._load(location.hash || '#home'); }
  _load(h)      { this.routes[h]?.(); }
}

// Ejemplo de uso dentro de main.js:
// import { Router } from './router.js';
// const r = new Router();
// r.on('#home', drawSubjects);
// r.on('#subs', drawSubscriptions);
// r.init();
