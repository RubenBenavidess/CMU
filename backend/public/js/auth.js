import { Api } from './api.js';

const formLogin    = document.querySelector('#formLogin');
const formRegister = document.querySelector('#formRegister');

/* ---------- Login ---------- */
if (formLogin) {
  formLogin.addEventListener('submit', async (e) => {
    e.preventDefault();

    const username = formLogin.user.value.trim();
    const password = formLogin.password.value.trim();

    try {
      const res = await Api.login({ username, password });
      if (res.ok) window.location.href = 'main.html';
    } catch (err) {
      handleLoginError(err.message);
    }
  });
}

/* ---------- Register ---------- */
if (formRegister) {
  formRegister.addEventListener('submit', async (e) => {
    e.preventDefault();

    const { username, password, email, bornDate } = formRegister;

    try {
      const res = await Api.register({
        username:  username.value.trim(),
        password:  password.value.trim(),
        email:     email.value.trim(),
        bornDate:  bornDate.value,
      });
      if (res.ok) window.location.href = 'login.html';
    } catch (err) {
      showError('#msgInvUser', err.message);
    }
  });
}

function handleLoginError(msg) {
  switch (msg) {
    case 'invalid-password':
    case 'wrong-password':
      showError('#msgInvPwd',  'Contraseña incorrecta');
      break;
    case 'user-not-found':
    case 'invalid-user':
      showError('#msgInvUser', 'Usuario incorrecto');
      break;
    default:
      alert(msg);
  }
}

function showError(selector, message) {
  const el = document.querySelector(selector);
  if (!el) return;

  el.textContent   = message;
  el.style.display = 'block';
  setTimeout(() => (el.style.display = 'none'), 3500);
}
