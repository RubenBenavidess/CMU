// ================================
// auth.js  (ES module)
// Manejador de formulario Login y Registro + barra de sesión
// Importa en login.html y register.html mediante <script type="module">
// ================================

import { Api } from './api.js';

const formLogin    = document.querySelector('#formLogin');
const formRegister = document.querySelector('#formRegister');

if (formLogin) {
  formLogin.addEventListener('submit', async (e) => {
    e.preventDefault();
    const username = formLogin.user.value.trim();
    const password = formLogin.password.value.trim();

    try {
      const res = await Api.login({ username, password });
      if (res.ok) {
        window.location.href = 'main.html';
      }
    } catch (err) {
      showError('#msgInvUser', err.message);
    }
  });
}

if (formRegister) {
  formRegister.addEventListener('submit', async (e) => {
    e.preventDefault();
    const { username, password, email, bornDate } = formRegister;

    try {
      const res = await Api.register({
        username: username.value.trim(),
        password: password.value.trim(),
        email:    email.value.trim(),
        bornDate: bornDate.value,
      });
      if (res.ok) {
        window.location.href = 'login.html';
      }
    } catch (err) {
      showError('#msgInvUser', err.message);
    }
  });
}

/** Utilidad rápida para mostrar y ocultar mensajes */
function showError(selector, msg) {
  const el = document.querySelector(selector);
  if (!el) return;
  el.style.display = 'block';
  el.textContent = msg;
  setTimeout(() => (el.style.display = 'none'), 4000);
}