// ================================
// main.js (ES module)
// ================================

import { Api } from './api.js';

const resourcesContainer=document.querySelector('#resources-container');
const titleContainer    =document.querySelector('#title-container');

const subsContainer=document.createElement('section');
Object.assign(subsContainer.style,{width:'100%',marginBottom:'32px'});
resourcesContainer.parentElement.insertBefore(subsContainer,resourcesContainer);

init();

async function init(){
  try{
    const session = await Api.loggedIn().catch(()=>({ok:false}));
    const logged  = session.ok;
    const subs    = logged? (await Api.userSubs()).data: [];
    const subjects= (await Api.allSubjects()).data;
    const variantInfo=await buildVariantInfoMap(subjects);
    subs.forEach(s=>Object.assign(s,variantInfo[s.idVariante]||{}));
    subsContainer.innerHTML=''; resourcesContainer.innerHTML='';
    if(logged&&subs.length) renderMySubs(subs);
    subjects.forEach(s=>renderSubject(s,logged,variantInfo,subs));
  }catch(e){titleContainer.textContent=e.message;}
}

async function buildVariantInfoMap(subjects){
  const map={};
  await Promise.all(subjects.map(async subj=>{
    const vars=await Api.variantsBySubject(subj.idAsignatura).then(r=>r.data);
    vars.forEach(v=>{map[v.idVariante]={idVariante:v.idVariante,nombre_asignatura:subj.nombre_asignatura,nombre_variante:v.nombre_variante,rol:v.rol};});
  }));
  return map;
}

function renderMySubs(subs){
  const h=document.createElement('h4');h.textContent='Mis suscripciones';subsContainer.appendChild(h);
  const grid=document.createElement('div');Object.assign(grid.style,{display:'flex',flexWrap:'wrap',gap:'12px',marginTop:'12px'});subsContainer.appendChild(grid);
  subs.forEach(s=>grid.appendChild(buildSubChip(s)));
}

function buildSubChip(sub){
  const chip=document.createElement('div');Object.assign(chip.style,{display:'flex',alignItems:'center',gap:'10px',border:'1px solid #FF9A62',padding:'6px 12px',borderRadius:'6px'});
  chip.textContent=`${sub.nombre_asignatura} – ${sub.nombre_variante}`;
  const btn=document.createElement('button');btn.className='nav-button';btn.style.whiteSpace='nowrap';btn.textContent=sub.rol==='admin'?'+':'ANULAR';
  if(sub.rol==='admin'){btn.onclick=()=>openUploadOverlay(sub.idVariante);}else{btn.onclick=async()=>{try{await Api.toggleSubState(sub.idSuscripcion,'inactiva');location.reload();}catch(e){alert(e.message);}};}
  chip.appendChild(btn);return chip;
}

function renderSubject(sub,logged,variantInfo,subs){
  const det=document.createElement('details');const sum=document.createElement('summary');sum.textContent=sub.nombre_asignatura;det.appendChild(sum);resourcesContainer.appendChild(det);
  det.addEventListener('toggle',()=>{
    if(det.open&&det.children.length===1){
      const vars=Object.values(variantInfo).filter(v=>v.nombre_asignatura===sub.nombre_asignatura);
      vars.forEach(v=>{const merged={...v,...(subs.find(s=>s.idVariante===v.idVariante)||{})};det.appendChild(buildVariantCard(merged,logged));});
    }
  },{once:true});
}

function buildVariantCard(v,logged){
  const card=document.createElement('div');Object.assign(card.style,{border:'1px solid #444',margin:'6px',padding:'6px',position:'relative',overflow:'hidden'});card.textContent=v.nombre_variante;
  if(logged&&v.rol!=='admin'){
    const btn=document.createElement('button');btn.className='nav-button';btn.style.marginLeft='8px';
    if(v.idSuscripcion){btn.textContent='Anular';btn.onclick=async()=>{try{await Api.toggleSubState(v.idSuscripcion,'inactiva');location.reload();}catch(e){alert(e.message);}};}
    else{btn.textContent='Suscribirse';btn.onclick=async()=>{try{await Api.subscribe(v.idVariante);location.reload();}catch(e){alert(e.message);}};}
    card.appendChild(btn);
  }
  if(v.rol==='admin'){
    const plus=document.createElement('button');plus.textContent='+';plus.title='Subir recurso';Object.assign(plus.style,{position:'absolute',right:'8px',bottom:'8px',background:'#FF9A62',border:'none',borderRadius:'50%',width:'36px',height:'36px',fontWeight:'900',cursor:'pointer'});plus.onclick=()=>openUploadOverlay(v.idVariante);card.appendChild(plus);
  }
  return card;
}

function openUploadOverlay(idVariante){
  const overlay=document.createElement('div');Object.assign(overlay.style,{position:'fixed',inset:'0',background:'rgba(0,0,0,0.6)',display:'flex',alignItems:'center',justifyContent:'center',zIndex:'999'});
  overlay.innerHTML=`<div style="background:#fff;color:#000;padding:20px;border-radius:8px;max-width:420px;width:90%">
  <h3 style="margin-top:0">Subir recurso</h3>
  <form id="uploadForm">
    <input name="titulo" placeholder="Título" required style="width:100%;margin-bottom:8px" />
    <textarea name="descripcion" placeholder="Descripción" style="width:100%;margin-bottom:8px"></textarea>
    <select name="tipo" style="width:100%;margin-bottom:8px">
      <option value="material">Material</option>
      <option value="actividad_aprendizaje">Actividad</option>
      <option value="leccion">Lección</option>
      <option value="evaluacion">Evaluación</option>
    </select>
    <input type="file" name="file" accept="application/pdf" required style="margin-bottom:12px" />
    <div style="display:flex;justify-content:flex-end;gap:8px">
      <button type="button" id="cancelUpload" class="nav-button">Cancelar</button>
      <button type="submit" class="nav-button">Enviar</button>
    </div>
  </form>
</div>`;
  document.body.appendChild(overlay);
  document.getElementById('cancelUpload').onclick=()=>overlay.remove();
  document.getElementById('uploadForm').onsubmit=async e=>{e.preventDefault();try{await Api.createResource(idVariante,new FormData(e.target));alert('Recurso subido');location.reload();}catch(err){alert(err.message);}};
}
