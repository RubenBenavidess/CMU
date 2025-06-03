// ================================
// main.js (ES module) – VERSIÓN DEFINITIVA
// ================================

import { Api } from './api.js';

const resourcesContainer = document.querySelector('#resources-container');
const titleContainer     = document.querySelector('#title-container');

// Creamos el contenedor superior donde estarán "Mis suscripciones" y "Mis materias".
const subsContainer = document.createElement('section');
Object.assign(subsContainer.style, {
  width:        '100%',
  marginBottom: '32px',
  padding:      '0 16px'
});
resourcesContainer.parentElement.insertBefore(subsContainer, resourcesContainer);

init(); // Arrancamos

async function init() {
  try {
    // 1) Verificamos sesión
    const session = await Api.loggedIn().catch(() => ({ ok: false }));
    const logged  = session.ok;
    titleContainer.textContent = 'Recursos Académicos';

    // Vaciamos ambos contenedores antes de re‐dibujar
    subsContainer.innerHTML      = '';
    resourcesContainer.innerHTML = '';

    // 2) Si NO está logueado, simplemente listamos todas las asignaturas sin botones.
    if (!logged) {
      const subjects    = await Api.allSubjects().then(r => r.data);
      const variantInfo = await buildVariantInfoMap(subjects);
      subjects.forEach(sub => renderSubject(sub, false, variantInfo, []));
      return;
    }

    // 3) Si está logueado, traemos:
    //    • subsRaw: todas las suscripciones (activas / inactivas / admin) del usuario
    //    • subjects: todas las asignaturas
    const subsRaw  = await Api.userSubs().then(r => r.data);
    const subjects = await Api.allSubjects().then(r => r.data);

    // 4) Construimos un mapa { idVariante → { nombre_asignatura, nombre_variante, rol } }
    const variantInfo = await buildVariantInfoMap(subjects);

    // 5) Inyectamos nombre_asignatura + nombre_variante desde variantInfo en cada elemento de subsRaw
    subsRaw.forEach(s => {
      const info = variantInfo[s.idVariante] || {};
      s.nombre_asignatura = info.nombre_asignatura || '';
      s.nombre_variante   = info.nombre_variante   || '';
      // El rol (s.rol) y el estado (s.estado) provienen directamente de userSubs()
      // por eso aquí no los sobreescribimos.
    });

    // 6) Separamos en tres listas:
    //    • susActivas:   rol==='suscriptor'  && estado==='activa'
    //    • susInactivas: rol==='suscriptor'  && estado==='inactiva'
    //    • susAdmins:    rol==='admin'
    const susActivas   = subsRaw.filter(s => s.rol === 'suscriptor' && s.estado === 'activa');
    
    const susAdmins    = subsRaw.filter(s => s.rol === 'admin');

    // 7) Renderizamos “Mis suscripciones” → SOLO susActivas
    if (susActivas.length) {
      renderMySubscriptions(susActivas);
    }

    // 8) Renderizamos “Mis materias” → SOLO susAdmins
    if (susAdmins.length) {
      renderMyCourses(susAdmins);
    }

    // 9) Por último, pintamos el listado general de asignaturas → renderSubject(…)
    subjects.forEach(sub => {
      renderSubject(sub, true, variantInfo, subsRaw);
    });

  } catch (e) {
    titleContainer.textContent = `Error: ${e.message}`;
  }
}


/**
 * Construye un mapa para saber, dada cada asignatura,
 * cuáles son sus variantes y cómo se llaman.
 * El objeto devuelto será:
 *   { 
 *     idVariante1: { idVariante, nombre_asignatura, nombre_variante, rol: undefined }, 
 *     idVariante2: { … }, 
 *     … 
 *   }
 * (el “rol” aquí NO es el rol de usuario, sino que se inicializa a undefined;
 *  la propiedad correcta “rol” de cada variante la completaremos
 *  desde los registros que venga en subsRaw). 
 */
async function buildVariantInfoMap(subjects) {
  const map = {};
  await Promise.all(
    subjects.map(async subj => {
      // Llamamos /api/variants/getBySubject?idAsignatura=<…>
      const vars = await Api.variantsBySubject(subj.idAsignatura).then(r => r.data);
      vars.forEach(v => {
        map[v.idVariante] = {
          idVariante:        v.idVariante,
          nombre_asignatura: subj.nombre_asignatura,
          nombre_variante:   v.nombre_variante,
          rol:               undefined
        };
      });
    })
  );
  return map;
}


/**
 * Renderiza la sección “Mis suscripciones” (solo las activas).
 * Cada entrada en susActivas es un objeto con:
 *   { idSuscripcion, idVariante, nombre_asignatura, nombre_variante, rol, estado }
 */
function renderMySubscriptions(susActivas) {
  // 7.1) Título H4
  const h = document.createElement('h4');
  h.textContent = 'Mis suscripciones';
  Object.assign(h.style, {
    marginBottom: '12px',
    color:        '#FFF'
  });
  subsContainer.appendChild(h);

  // 7.2) Un grid con CSS flex para alinear cada “chip”
  const grid = document.createElement('div');
  Object.assign(grid.style, {
    display:      'flex',
    flexWrap:     'wrap',
    gap:          '12px',
    marginBottom: '24px'
  });
  subsContainer.appendChild(grid);

  // 7.3) Por cada suscripción activa, construimos un “chip”
  susActivas.forEach(s => {
    const chip = document.createElement('div');
    Object.assign(chip.style, {
      display:      'flex',
      alignItems:   'center',
      gap:          '8px',
      border:       '1px solid #FF9A62',
      padding:      '6px 12px',
      borderRadius: '6px',
      background:   'rgb(33,47,60)'
    });

    // 7.3.1) Texto “Asignatura – Variante”
    const texto = document.createElement('span');
    texto.textContent = `${s.nombre_asignatura} – ${s.nombre_variante}`;
    texto.style.color = '#FFF';
    chip.appendChild(texto);

    // 7.3.2) Botón [ANULAR]
    const btnAnular = document.createElement('button');
    btnAnular.className   = 'nav-button';
    btnAnular.textContent = 'ANULAR';
    btnAnular.onclick = async () => {
      try {
        await Api.toggleSubState(s.idSuscripcion, 'inactiva');
        location.reload();
      } catch (err) {
        alert(`Error al anular: ${err.message}`);
      }
    };
    chip.appendChild(btnAnular);

    // 7.3.3) Botón [VER RECURSOS]
    const btnVer = document.createElement('button');
    btnVer.className   = 'nav-button';
    btnVer.textContent = 'VER RECURSOS';
    btnVer.onclick = () => toggleResourcesFor(s, chip);
    chip.appendChild(btnVer);

    // 7.3.4) Contenedor oculto donde aparecerán los PDFs (si el usuario hace “Ver Recursos”).
    const contRec = document.createElement('div');
    Object.assign(contRec.style, {
      marginTop:    '10px',
      border:       '1px solid #555',
      background:   'rgba(0,0,0,0.2)',
      padding:      '8px',
      borderRadius: '4px',
      color:        '#ddd',
      fontSize:     '0.9rem',
      display:      'none'
    });
    contRec.id = `recursosCard-${s.idVariante}`;
    chip.appendChild(contRec);

    grid.appendChild(chip);
  });
}


/**
 * Renderiza la sección “Mis materias” (solo las variantes donde el usuario es admin).
 * Cada admin‐chip muestra:
 *   • “Asignatura – Variante”
 *   • Botón [+] que abre el modal de subir recurso.
 */
function renderMyCourses(susAdmins) {
  // 8.1) Título H4
  const h = document.createElement('h4');
  h.textContent = 'Mis materias';
  Object.assign(h.style, {
    marginTop:    '16px',
    marginBottom: '12px',
    color:        '#FFF'
  });
  subsContainer.appendChild(h);

  // 8.2) Grid con CSS flex para alinear cada “chip”
  const grid = document.createElement('div');
  Object.assign(grid.style, {
    display:      'flex',
    flexWrap:     'wrap',
    gap:          '12px',
    marginBottom: '24px'
  });
  subsContainer.appendChild(grid);

  susAdmins.forEach(s => {
    const chip = document.createElement('div');
    Object.assign(chip.style, {
      display:      'flex',
      alignItems:   'center',
      gap:          '8px',
      border:       '1px solid #FF9A62',
      padding:      '6px 12px',
      borderRadius: '6px',
      background:   'rgb(33,47,60)'
    });

    // 8.2.1) Texto “Asignatura – Variante”
    const texto = document.createElement('span');
    texto.textContent = `${s.nombre_asignatura} – ${s.nombre_variante}`;
    texto.style.color = '#FFF';
    chip.appendChild(texto);

    // 8.2.2) Botón “+” (subir recurso)
    const btnPlus = document.createElement('button');
    btnPlus.className   = 'nav-button';
    btnPlus.textContent = '+';
    btnPlus.title       = 'Subir recurso';
    Object.assign(btnPlus.style, {
      marginLeft: '8px',
      width:      '30px',
      height:     '30px',
      fontSize:   '1rem',
      fontWeight: '900'
    });
    btnPlus.onclick = () => openUploadOverlay(s.idVariante);
    chip.appendChild(btnPlus);

    grid.appendChild(chip);
  });
}


/**
 * Dibuja cada asignatura como <details>… con sus variantes.
 *
 * Parámetros:
 *   • sub         = { idAsignatura, nombre_asignatura, descripcion }
 *   • logged      = true / false
 *   • variantInfo = mapa idVariante → { nombre_asignatura, nombre_variante }
 *   • subsRaw     = lista completa de suscripciones (activas / inactivas / admin)
 */
function renderSubject(sub, logged, variantInfo, subsRaw) {
  const det = document.createElement('details');
  const sum = document.createElement('summary');
  sum.textContent = sub.nombre_asignatura;
  det.appendChild(sum);
  resourcesContainer.appendChild(det);

  det.addEventListener(
    'toggle',
    () => {
      // Solo la primera vez que se abre, <details> tenía un solo hijo (el <summary>).
      if (det.open && det.children.length === 1) {
        // 1) Recolectamos todas las variantes de esta asignatura
        const vars = Object.values(variantInfo).filter(
          v => v.nombre_asignatura === sub.nombre_asignatura
        );

        // 2) Por cada variante, buscamos en subsRaw si hay coincidencia
        vars.forEach(v => {
          // subsRaw puede tener: { idSuscripcion, idVariante, nombre_asignatura, nombre_variante, rol, estado }
          const match = subsRaw.find(s => s.idVariante === v.idVariante) || {};
          const merged = {
            idVariante:        v.idVariante,
            nombre_asignatura: v.nombre_asignatura,
            nombre_variante:   v.nombre_variante,
            rol:               match.rol           || null,    // 'suscriptor', 'admin' o null
            idSuscripcion:     match.idSuscripcion || null,
            estado:            match.estado        || null     // 'activa', 'inactiva' o null
          };
          det.appendChild(buildVariantCard(merged, logged));
        });
      }
    },
    { once: true }
  );
}


/**
 * Construye la “tarjeta” para cada variante (dentro de <details>):
 *
 * Casos:
 *   A) Si NO está logueado → Solo mostramos el nombre de la variante.
 *   B) Si v.rol==='suscriptor' y v.estado==='activa':
 *       • Botón [Anular]
 *       • Botón [Ver Recursos] con contenedor oculto de PDFs
 *   C) Si v.rol==='suscriptor' y v.estado==='inactiva':
 *       • Botón [Re-suscribirse]
 *   D) Si v.rol==='admin':
 *       • Botón [+] para subir recurso
 *   E) Si v.idSuscripcion===null (nunca suscrito) → Botón [Suscribirse]
 */
function buildVariantCard(v, logged) {
  const card = document.createElement('div');
  Object.assign(card.style, {
    border:       '1px solid #444',
    margin:       '6px',
    padding:      '6px',
    background:   'rgba(255,255,255,0.03)',
    borderRadius: '4px',
    color:        '#ddd',
    position:     'relative',
    overflow:     'hidden'
  });

  // 0) Nombre de la variante (siempre aparece)
  const nombreVar = document.createElement('span');
  nombreVar.textContent    = v.nombre_variante;
  nombreVar.style.marginRight = '8px';
  card.appendChild(nombreVar);

  // A) Si NO está logueado, devolvemos solo el nombre:
  if (!logged) {
    return card;
  }

  // B) Si es suscriptor ("suscriptor")
  if (v.rol === 'suscriptor') {
    // B.1) Si 'activa' → botones [Anular] + [Ver Recursos] + contenedor oculto
    if (v.estado === 'activa') {
      // Botón ANULAR
      const btnAnular = document.createElement('button');
      btnAnular.className   = 'nav-button';
      btnAnular.textContent = 'Anular';
      btnAnular.onclick = async () => {
        try {
          await Api.toggleSubState(v.idSuscripcion, 'inactiva');
          location.reload();
        } catch (err) {
          alert(`Error al anular: ${err.message}`);
        }
      };
      card.appendChild(btnAnular);

      // Botón VER RECURSOS
      const btnVer = document.createElement('button');
      btnVer.className   = 'nav-button';
      btnVer.textContent = 'Ver Recursos';
      btnVer.onclick = () => toggleResourcesFor(v, card);
      card.appendChild(btnVer);

      // Contenedor oculto para PDFs
      const contRec = document.createElement('div');
      Object.assign(contRec.style, {
        marginTop:    '10px',
        border:       '1px solid #555',
        background:   'rgba(0,0,0,0.2)',
        padding:      '8px',
        borderRadius: '4px',
        color:        '#ddd',
        fontSize:     '0.9rem',
        display:      'none'
      });
      contRec.id = `recursosCard-${v.idVariante}`;
      card.appendChild(contRec);

      return card;
    }

    // B.2) Si 'inactiva' → Botón [Re-suscribirse]
    if (v.estado === 'inactiva') {
      const btnReSub = document.createElement('button');
      btnReSub.className   = 'nav-button';
      btnReSub.textContent = 'Re-suscribirse';
      btnReSub.onclick = async () => {
        try {
          await Api.toggleSubState(v.idSuscripcion, 'activa');
          location.reload();
        } catch (err) {
          alert(`Error al re-suscribir: ${err.message}`);
        }
      };
      card.appendChild(btnReSub);
      return card;
    }
  }

  // C) Si es admin ('admin') → Botón [+ subir recurso]
  if (v.rol === 'admin') {
    const plus = document.createElement('button');
    plus.textContent = '+';
    plus.title       = 'Subir recurso';
    Object.assign(plus.style, {
      position:     'absolute',
      right:        '8px',
      bottom:       '8px',
      background:   '#FF9A62',
      border:       'none',
      borderRadius: '50%',
      width:        '32px',
      height:       '32px',
      color:        '#fff',
      fontWeight:   '900',
      cursor:       'pointer'
    });
    plus.onclick = () => openUploadOverlay(v.idVariante);
    card.appendChild(plus);
    return card;
  }

  // D) Si NO hay idSuscripcion (== null) → Botón [Suscribirse]
  if (v.idSuscripcion === null) {
    const btnSub = document.createElement('button');
    btnSub.className   = 'nav-button';
    btnSub.textContent = 'Suscribirse';
    btnSub.onclick = async () => {
      try {
        await Api.subscribe(v.idVariante);
        location.reload();
      } catch (err) {
        alert(`Error al suscribir: ${err.message}`);
      }
    };
    card.appendChild(btnSub);
    return card;
  }

  return card;
}


/**
 * Al hacer clic en “Ver Recursos”, alternamos el contenedor y pedimos al servidor los PDFs.
 */
async function toggleResourcesFor(v, cardElement) {
  const contRec = cardElement.querySelector(`#recursosCard-${v.idVariante}`);
  if (!contRec) return;

  // Si ya está visible, lo ocultamos
  if (contRec.style.display === 'block') {
    contRec.style.display = 'none';
    return;
  }

  // Si estaba oculto, lo mostramos y cargamos
  contRec.style.display = 'block';
  contRec.innerHTML     = 'Cargando recursos…';

  try {
    // Llamada al endpoint GET /?path=api/resources/getByVariant&idVariante=<v.idVariante>
    const data = await Api.resourcesByVariant(v.idVariante);
    const misRecursos = data.resources;

    if (!misRecursos || misRecursos.length === 0) {
      contRec.innerHTML = '<em>No hay recursos disponibles.</em>';
    } else {
      contRec.innerHTML = '';
      misRecursos.forEach(r => {
        const div = document.createElement('div');
        div.style.marginBottom = '8px';

        // Título + tipo
        const tituloElem = document.createElement('div');
        tituloElem.innerHTML = `<strong>${r.titulo}</strong> <small>(${r.tipo})</small>`;
        div.appendChild(tituloElem);

        // Descripción
        if (r.descripcion) {
          const desc = document.createElement('div');
          desc.style.fontSize = '0.9rem';
          desc.style.color    = '#ccc';
          desc.textContent    = r.descripcion;
          div.appendChild(desc);
        }

        // Enlace “📄 Descargar PDF”
        const enlace = document.createElement('a');
        enlace.href   = Api.downloadResource(r.idRecurso);
        enlace.target = '_blank';
        enlace.style.display   = 'inline-block';
        enlace.style.marginTop = '4px';
        enlace.style.color     = '#4dabf7';
        enlace.innerHTML       = '📄 Descargar PDF';
        div.appendChild(enlace);

        contRec.appendChild(div);
      });
    }
  } catch (error) {
    contRec.innerHTML = `<span style="color:#f66">Error cargando recursos: ${error.message}</span>`;
  }
}


/**
 * Muestra un overlay/modal donde el admin puede subir un nuevo recurso.
 */
function openUploadOverlay(idVariante) {
  const overlay = document.createElement('div');
  Object.assign(overlay.style, {
    position:       'fixed',
    inset:          '0',
    background:     'rgba(0,0,0,0.6)',
    display:        'flex',
    alignItems:     'center',
    justifyContent: 'center',
    zIndex:         '9999'
  });

  overlay.innerHTML = `
    <div style="
      background:#fff;
      color:#000;
      padding:20px;
      border-radius:8px;
      max-width:420px;
      width:90%;
    ">
      <h3 style="margin-top:0">Subir recurso</h3>
      <form id="uploadForm">
        <input name="titulo" placeholder="Título" required
               style="width:100%;margin-bottom:8px" />
        <textarea name="descripcion" placeholder="Descripción"
                  style="width:100%;margin-bottom:8px"></textarea>
        <select name="tipo" style="width:100%;margin-bottom:8px">
          <option value="material">Material</option>
          <option value="actividad_aprendizaje">Actividad</option>
          <option value="leccion">Lección</option>
          <option value="evaluacion">Evaluación</option>
        </select>
        <input type="file" name="file" accept="application/pdf" required
               style="margin-bottom:12px" />
        <div style="display:flex;justify-content:flex-end;gap:8px">
          <button type="button" id="cancelUpload" class="nav-button">Cancelar</button>
          <button type="submit" class="nav-button">Enviar</button>
        </div>
      </form>
    </div>
  `;

  document.body.appendChild(overlay);
  document.getElementById('cancelUpload').onclick = () => overlay.remove();

  document.getElementById('uploadForm').onsubmit = async e => {
    e.preventDefault();
    try {
      await Api.createResource(idVariante, new FormData(e.target));
      alert('Recurso subido con éxito.');
      location.reload();
    } catch (err) {
      alert(`Error subiendo archivo: ${err.message}`);
    }
  };
}
