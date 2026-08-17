<!DOCTYPE html> 
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1" />
<title>MISI // Portofolio Antariksa</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>
  :root{
    --bg: #05070d;
    --bg-deep: #02030700;
    --ink: #eef2fb;
    --muted: #8a93ab;
    --hud: #6fd6ff;
    --hud-dim: rgba(111,214,255,0.35);
    --panel: rgba(10,14,26,0.72);
    --line: rgba(255,255,255,0.09);
    --mercury: #d99a6c;
    --venus: #e8c37a;
    --earth: #5ec9c0;
    --mars: #d1603d;
    --saturn: #e8b86d;
  }
  *{box-sizing:border-box;}
  html,body{margin:0;padding:0;height:100%;background:var(--bg);color:var(--ink);
    font-family:'Inter',sans-serif; overflow:hidden;}
  #scene-canvas{position:fixed;inset:0;display:block;}

  /* ---------- HUD TOP BAR ---------- */
  .hud-top{
    position:fixed; top:0; left:0; right:0; z-index:30;
    display:flex; align-items:center; justify-content:space-between;
    padding:20px 28px; pointer-events:none;
  }
  .brand{
    font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:15px;
    letter-spacing:0.14em; color:var(--ink); pointer-events:auto;
    display:flex; align-items:center; gap:10px;
  }
  .brand .dot{width:7px;height:7px;border-radius:50%;background:var(--hud);
    box-shadow:0 0 8px var(--hud), 0 0 16px var(--hud); animation:pulse 2.4s ease-in-out infinite;}
  @keyframes pulse{0%,100%{opacity:1;}50%{opacity:0.35;}}
  .brand small{display:block;font-family:'IBM Plex Mono',monospace;font-weight:400;
    font-size:10px;letter-spacing:0.12em;color:var(--muted);margin-top:2px;}

  nav.hud-nav{pointer-events:auto; display:flex; gap:6px; flex-wrap:wrap; justify-content:flex-end; max-width:640px;}
  .nav-pill{
    font-family:'IBM Plex Mono',monospace; font-size:11px; letter-spacing:0.06em;
    color:var(--muted); background:rgba(255,255,255,0.03); border:1px solid var(--line);
    padding:9px 14px; border-radius:999px; cursor:pointer; transition:all .35s ease;
    backdrop-filter:blur(6px); white-space:nowrap;
  }
  .nav-pill:hover, .nav-pill:focus-visible{color:var(--ink); border-color:var(--hud-dim); background:rgba(111,214,255,0.08);}
  .nav-pill.active{color:var(--bg); background:var(--hud); border-color:var(--hud);}
  .nav-pill:focus-visible{outline:2px solid var(--hud); outline-offset:2px;}

  /* ---------- TELEMETRY STRIP (bottom left) ---------- */
  .telemetry{
    position:fixed; left:24px; bottom:22px; z-index:25; pointer-events:none;
    font-family:'IBM Plex Mono',monospace; font-size:11px; color:var(--muted);
    display:flex; flex-direction:column; gap:4px; letter-spacing:0.04em;
    text-shadow:0 1px 6px rgba(0,0,0,0.8);
  }
  .telemetry .state{color:var(--hud); font-weight:500;}
  .telemetry .bar{width:120px;height:2px;background:rgba(255,255,255,0.1);margin-top:6px;position:relative;overflow:hidden;}
  .telemetry .bar i{position:absolute;left:0;top:0;bottom:0;width:40%;background:var(--hud);animation:scan 2.6s linear infinite;}
  @keyframes scan{0%{left:-40%;}100%{left:100%;}}

  .hint{
    position:fixed; right:24px; bottom:22px; z-index:25; pointer-events:none;
    font-family:'IBM Plex Mono',monospace; font-size:10.5px; color:var(--muted);
    letter-spacing:0.05em; text-align:right; line-height:1.6; text-shadow:0 1px 6px rgba(0,0,0,0.8);
  }

  /* ---------- CROSSHAIR LABEL (on planet hover) ---------- */
  #target-label{
    position:fixed; z-index:26; pointer-events:none; transform:translate(-50%,-50%);
    font-family:'IBM Plex Mono',monospace; font-size:11px; color:var(--hud);
    opacity:0; transition:opacity .2s ease; white-space:nowrap;
  }
  #target-label .box{
    border:1px solid var(--hud); padding:14px 18px; position:relative;
  }
  #target-label .box::before,#target-label .box::after{content:'';position:absolute;width:8px;height:8px;border:1px solid var(--hud);}
  #target-label .box::before{top:-1px;left:-1px;border-right:none;border-bottom:none;}
  #target-label .box::after{bottom:-1px;right:-1px;border-left:none;border-top:none;}
  #target-label .name{font-family:'Space Grotesk',sans-serif;font-size:13px;color:var(--ink);letter-spacing:0.05em;}
  #target-label .code{font-size:9.5px;color:var(--muted);margin-top:2px;}

  /* ---------- LOADER ---------- */
  #loader{
    position:fixed; inset:0; z-index:100; background:var(--bg);
    display:flex; flex-direction:column; align-items:center; justify-content:center; gap:18px;
    transition:opacity .9s ease, visibility .9s ease;
  }
  #loader.hidden{opacity:0; visibility:hidden; pointer-events:none;}
  .loader-mark{font-family:'Space Grotesk',sans-serif;font-size:13px;letter-spacing:0.3em;color:var(--muted);}
  .loader-track{width:220px;height:1px;background:rgba(255,255,255,0.12);position:relative;overflow:hidden;}
  .loader-fill{height:100%;width:0%;background:var(--hud);transition:width .25s ease;}
  .loader-pct{font-family:'IBM Plex Mono',monospace;font-size:11px;color:var(--hud);}

  /* ---------- HERO / ENTRY ---------- */
  #hero{
    position:fixed; inset:0; z-index:40; display:flex; flex-direction:column;
    align-items:center; justify-content:center; text-align:center; gap:22px;
    transition:opacity 1s ease, visibility 1s ease;
  }
  #hero.hidden{opacity:0; visibility:hidden; pointer-events:none;}
  .eyebrow{font-family:'IBM Plex Mono',monospace; font-size:11px; letter-spacing:0.35em;
    color:var(--hud); text-transform:uppercase;}
  h1.hero-title{
    font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:clamp(2.4rem,7vw,5.2rem);
    line-height:1.02; margin:0; letter-spacing:-0.01em;
    background:linear-gradient(180deg,#ffffff 0%, #aeb9d4 100%);
    -webkit-background-clip:text; background-clip:text; color:transparent;
  }
  .hero-sub{font-size:15px; color:var(--muted); max-width:460px; line-height:1.6;}
  .hero-cta{
    margin-top:10px; font-family:'IBM Plex Mono',monospace; font-size:12px; letter-spacing:0.12em;
    color:var(--bg); background:var(--hud); border:none; padding:15px 30px; border-radius:999px;
    cursor:pointer; text-transform:uppercase; transition:transform .3s ease, box-shadow .3s ease;
  }
  .hero-cta:hover{transform:translateY(-2px); box-shadow:0 8px 30px rgba(111,214,255,0.35);}
  .hero-cta:focus-visible{outline:2px solid #fff; outline-offset:3px;}

  /* ---------- SIDE PANEL ---------- */
  #panel-wrap{position:fixed; inset:0; z-index:50; pointer-events:none;}
  #panel{
    position:fixed; top:0; right:0; height:100%; width:min(440px,100%);
    background:var(--panel); backdrop-filter:blur(18px);
    border-left:1px solid var(--line);
    transform:translateX(100%); transition:transform .6s cubic-bezier(.16,.84,.32,1);
    pointer-events:auto; overflow-y:auto;
    padding:90px 34px 40px;
  }
  #panel.open{transform:translateX(0);}
  #panel .accent-bar{position:absolute; top:0; left:0; width:3px; height:100%; background:var(--accent,var(--hud));}
  #panel .p-eyebrow{font-family:'IBM Plex Mono',monospace; font-size:10.5px; letter-spacing:0.2em;
    color:var(--accent,var(--hud)); text-transform:uppercase; margin-bottom:10px;}
  #panel h2{font-family:'Space Grotesk',sans-serif; font-size:2rem; margin:0 0 6px; letter-spacing:-0.01em;}
  #panel .p-tagline{color:var(--muted); font-size:13.5px; margin-bottom:26px;}
  .telemetry-row{
    display:flex; gap:22px; font-family:'IBM Plex Mono',monospace; font-size:10.5px;
    color:var(--muted); border-top:1px solid var(--line); border-bottom:1px solid var(--line);
    padding:14px 0; margin-bottom:26px; flex-wrap:wrap;
  }
  .telemetry-row div span{display:block; color:var(--ink); font-size:12.5px; margin-top:3px;}
  #panel .p-body{font-size:14.5px; line-height:1.75; color:#c9d0e3;}
  #panel .p-body p{margin:0 0 16px;}
  #panel .tag-list{display:flex; flex-wrap:wrap; gap:8px; margin:8px 0 20px;}
  #panel .tag-list span{
    font-family:'IBM Plex Mono',monospace; font-size:10.5px; padding:6px 11px;
    border:1px solid var(--line); border-radius:999px; color:var(--ink);
  }
  #panel .proj-card{border:1px solid var(--line); border-radius:12px; padding:16px; margin-bottom:12px;}
  #panel .proj-card h3{margin:0 0 6px; font-family:'Space Grotesk',sans-serif; font-size:15px;}
  #panel .proj-card p{margin:0; font-size:13px; color:var(--muted); line-height:1.6;}
  #panel .contact-row{display:flex; align-items:center; justify-content:space-between;
    border:1px solid var(--line); border-radius:10px; padding:13px 16px; margin-bottom:10px;
    font-family:'IBM Plex Mono',monospace; font-size:12.5px; text-decoration:none; color:var(--ink);
    transition:border-color .3s ease, background .3s ease;}
  #panel .contact-row:hover{border-color:var(--accent,var(--hud)); background:rgba(255,255,255,0.03);}
  #panel .contact-row span:last-child{color:var(--muted);}

  #panel-close{
    position:fixed; top:22px; right:min(440px,100%); margin-right:18px; z-index:55;
    font-family:'IBM Plex Mono',monospace; font-size:11px; letter-spacing:0.1em;
    color:var(--ink); background:rgba(10,14,26,0.72); border:1px solid var(--line);
    padding:11px 18px; border-radius:999px; cursor:pointer;
    display:flex; align-items:center; gap:8px; backdrop-filter:blur(10px);
    opacity:0; transform:translateX(20px); pointer-events:none;
    transition:opacity .5s ease .15s, transform .5s ease .15s, right .6s cubic-bezier(.16,.84,.32,1);
  }
  #panel-close.open{opacity:1; transform:translateX(0); pointer-events:auto;}
  #panel-close:focus-visible{outline:2px solid var(--hud);}

  @media (max-width:720px){
    .hud-top{padding:16px;flex-direction:column;align-items:flex-start;gap:12px;}
    nav.hud-nav{justify-content:flex-start;max-width:100%;}
    .hint{display:none;}
    #panel{width:100%; padding:80px 22px 40px;}
    #panel-close{right:0; margin-right:16px;}
  }

  @media (prefers-reduced-motion: reduce){
    *{animation-duration:0.001ms !important; transition-duration:0.001ms !important;}
  }
</style>
</head>
<body>

<canvas id="scene-canvas"></canvas>

<!-- LOADER -->
<div id="loader">
  <div class="loader-mark">MENYIAPKAN SISTEM TATA SURYA</div>
  <div class="loader-track"><div class="loader-fill" id="loader-fill"></div></div>
  <div class="loader-pct" id="loader-pct">0%</div>
</div>

<!-- HERO -->
<div id="hero">
  <div class="eyebrow">Portofolio Digital</div>
  <h1 class="hero-title">[Nama Anda]</h1>
  <p class="hero-sub">Setiap planet menyimpan satu babak dari perjalanan saya — arahkan pandangan, lalu berlabuh untuk membacanya.</p>
  <button class="hero-cta" id="start-btn">Mulai Penjelajahan</button>
</div>

<!-- HUD TOP -->
<div class="hud-top">
  <div class="brand"><span class="dot"></span>
    <div>[NAMA ANDA]<small>MISSION CONTROL // PORTOFOLIO</small></div>
  </div>
  <nav class="hud-nav" id="nav-pills"></nav>
</div>

<!-- TELEMETRY -->
<div class="telemetry">
  <div>STATUS: <span class="state" id="tm-state">ORBIT BEBAS</span></div>
  <div id="tm-target">TARGET: —</div>
  <div class="bar"><i></i></div>
</div>
<div class="hint">Seret untuk memutar &middot; Scroll untuk zoom<br>Klik planet untuk berlabuh</div>

<div id="target-label"><div class="box"><div class="name"></div><div class="code"></div></div></div>

<!-- PANEL -->
<button id="panel-close">&larr; Kembali ke luar angkasa</button>
<div id="panel-wrap"><div id="panel"><div class="accent-bar"></div><div id="panel-content"></div></div></div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script>
(function(){
"use strict";

/* ============================================================
   DATA — edit teks di sini untuk mengisi portofolio Anda
   ============================================================ */
const TEX = "https://cdn.jsdelivr.net/gh/ArcusDeri/ThreeJS-solarSystem@master/imgCw3/";
const PLANETS = [
  {
    key:"mercury", name:"Merkurius", code:"MRK-01", accent:"var(--mercury)",
    eyebrow:"Profil", title:"Tentang Saya", tagline:"Planet terdekat — titik awal cerita.",
    distance:"0.39 AU", period:"88 hari", texture:TEX+"planetTextures/merc.jpg",
    size:0.62, orbit:9.2, speed:0.28, spin:0.9,
    body:`<p>[Tulis perkenalan singkat di sini — siapa Anda, latar belakang, dan apa yang Anda kerjakan sehari-hari.]</p>
          <p>[Ceritakan nilai atau pendekatan kerja yang Anda pegang. Dua sampai tiga kalimat sudah cukup.]</p>
          <div class="tag-list"><span>[Peran]</span><span>[Lokasi]</span><span>[Fokus Utama]</span></div>`
  },
  {
    key:"venus", name:"Venus", code:"VNS-02", accent:"var(--venus)",
    eyebrow:"Kapabilitas", title:"Keahlian", tagline:"Atmosfer tebal — kemampuan yang menopang setiap karya.",
    distance:"0.72 AU", period:"225 hari", texture:TEX+"planetTextures/venus.jpg",
    size:0.9, orbit:12.8, speed:0.19, spin:-0.5,
    body:`<p>[Jelaskan singkat keahlian inti Anda dan bagaimana kombinasinya menghasilkan nilai.]</p>
          <div class="tag-list"><span>[Skill 1]</span><span>[Skill 2]</span><span>[Skill 3]</span><span>[Skill 4]</span><span>[Skill 5]</span><span>[Skill 6]</span></div>`
  },
  {
    key:"earth", name:"Bumi", code:"ERT-03", accent:"var(--earth)",
    eyebrow:"Portofolio", title:"Proyek Terpilih", tagline:"Rumah — tempat ide menjadi hasil nyata.",
    distance:"1.00 AU", period:"365 hari", texture:TEX+"planetTextures/earth.jpg",
    size:1, orbit:16.6, speed:0.16, spin:1.2,
    body:`
      <div class="proj-card"><h3>[Nama Proyek 1]</h3><p>[Deskripsi singkat: masalah, peran Anda, dan hasilnya.]</p></div>
      <div class="proj-card"><h3>[Nama Proyek 2]</h3><p>[Deskripsi singkat: masalah, peran Anda, dan hasilnya.]</p></div>
      <div class="proj-card"><h3>[Nama Proyek 3]</h3><p>[Deskripsi singkat: masalah, peran Anda, dan hasilnya.]</p></div>`
  },
  {
    key:"mars", name:"Mars", code:"MRS-04", accent:"var(--mars)",
    eyebrow:"Rekam Jejak", title:"Pengalaman", tagline:"Planet merah — medan yang telah dijelajahi.",
    distance:"1.52 AU", period:"687 hari", texture:TEX+"planetTextures/mars.jpg",
    size:0.78, orbit:20.6, speed:0.13, spin:1.0,
    body:`<p><strong>[Jabatan]</strong> — [Nama Perusahaan/Organisasi] <span style="color:var(--muted)">([Tahun Mulai]–[Tahun Selesai])</span></p>
          <p>[Ringkasan tanggung jawab dan pencapaian utama pada peran ini.]</p>
          <p><strong>[Jabatan]</strong> — [Nama Perusahaan/Organisasi] <span style="color:var(--muted)">([Tahun Mulai]–[Tahun Selesai])</span></p>
          <p>[Ringkasan tanggung jawab dan pencapaian utama pada peran ini.]</p>`
  },
  {
    key:"saturn", name:"Saturnus", code:"SAT-05", accent:"var(--saturn)",
    eyebrow:"Sambungkan", title:"Kontak", tagline:"Bercincin — gerbang menuju kolaborasi baru.",
    distance:"9.58 AU", period:"29 tahun", texture:TEX+"planetTextures/saturn.jpg",
    size:1.55, orbit:27, speed:0.07, spin:1.6, rings:true,
    body:`<p>[Tulis ajakan singkat untuk berkolaborasi atau berdiskusi.]</p>
          <a class="contact-row" href="mailto:[email@anda.com]"><span>Email</span><span>[email@anda.com]</span></a>
          <a class="contact-row" href="#"><span>LinkedIn</span><span>[/in/username]</span></a>
          <a class="contact-row" href="#"><span>GitHub</span><span>[@username]</span></a>`
  }
];

/* ============================================================
   THREE.JS SETUP
   ============================================================ */
const canvas = document.getElementById('scene-canvas');
const renderer = new THREE.WebGLRenderer({canvas, antialias:true});
renderer.setPixelRatio(Math.min(window.devicePixelRatio,2));
renderer.setSize(window.innerWidth, window.innerHeight);
if (renderer.outputEncoding !== undefined) renderer.outputEncoding = THREE.sRGBEncoding;
renderer.toneMapping = THREE.ACESFilmicToneMapping;
renderer.toneMappingExposure = 1.1;

const scene = new THREE.Scene();
const camera = new THREE.PerspectiveCamera(45, window.innerWidth/window.innerHeight, 0.1, 3000);

const loadManager = new THREE.LoadingManager();
const loader = new THREE.TextureLoader(loadManager);
loadManager.onProgress = function(url, loaded, total){
  const pct = Math.round((loaded/total)*100);
  document.getElementById('loader-fill').style.width = pct+'%';
  document.getElementById('loader-pct').textContent = pct+'%';
};
loadManager.onLoad = function(){
  setTimeout(()=>{ document.getElementById('loader').classList.add('hidden'); }, 350);
};

/* ---- Starfield skybox ---- */
const skyTex = loader.load(TEX+"space1.jpg");
skyTex.wrapS = skyTex.wrapT = THREE.RepeatWrapping;
const sky = new THREE.Mesh(
  new THREE.SphereGeometry(900, 48, 48),
  new THREE.MeshBasicMaterial({map:skyTex, side:THREE.BackSide, fog:false})
);
scene.add(sky);

/* ---- Sparkle particle layer ---- */
(function starSparkle(){
  const count = 900;
  const positions = new Float32Array(count*3);
  for(let i=0;i<count;i++){
    const r = 300 + Math.random()*550;
    const theta = Math.random()*Math.PI*2;
    const phi = Math.acos((Math.random()*2)-1);
    positions[i*3]   = r*Math.sin(phi)*Math.cos(theta);
    positions[i*3+1] = r*Math.cos(phi);
    positions[i*3+2] = r*Math.sin(phi)*Math.sin(theta);
  }
  const geo = new THREE.BufferGeometry();
  geo.setAttribute('position', new THREE.BufferAttribute(positions,3));
  const mat = new THREE.PointsMaterial({color:0xffffff, size:1.15, sizeAttenuation:true, transparent:true, opacity:0.85});
  scene.add(new THREE.Points(geo, mat));
})();

/* ---- Sun ---- */
const sunTex = loader.load(TEX+"planetTextures/sun.jpg");
const sun = new THREE.Mesh(
  new THREE.SphereGeometry(3.1, 64, 64),
  new THREE.MeshBasicMaterial({map:sunTex})
);
scene.add(sun);

// sun glow sprite
(function sunGlow(){
  const c = document.createElement('canvas'); c.width=c.height=256;
  const ctx = c.getContext('2d');
  const g = ctx.createRadialGradient(128,128,0,128,128,128);
  g.addColorStop(0,'rgba(255,225,160,0.9)');
  g.addColorStop(0.4,'rgba(255,180,90,0.35)');
  g.addColorStop(1,'rgba(255,150,60,0)');
  ctx.fillStyle=g; ctx.fillRect(0,0,256,256);
  const tex = new THREE.CanvasTexture(c);
  const sprite = new THREE.Sprite(new THREE.SpriteMaterial({map:tex, transparent:true, depthWrite:false}));
  sprite.scale.set(16,16,1);
  scene.add(sprite);
})();

const sunLight = new THREE.PointLight(0xfff2d8, 2.4, 0, 0.02);
scene.add(sunLight);
scene.add(new THREE.AmbientLight(0x30354a, 1.1));

/* ---- Planets ---- */
const planetMeshes = []; // {key, pivot, mesh, data}
PLANETS.forEach(p=>{
  const pivot = new THREE.Object3D();
  pivot.rotation.y = Math.random()*Math.PI*2;
  scene.add(pivot);

  const tex = loader.load(p.texture);
  const mesh = new THREE.Mesh(
    new THREE.SphereGeometry(p.size, 48, 48),
    new THREE.MeshStandardMaterial({map:tex, roughness:0.85, metalness:0.05})
  );
  mesh.position.set(p.orbit, 0, 0);
  mesh.userData.key = p.key;
  pivot.add(mesh);

  if(p.rings){
    const ringTex = loader.load(p.texture);
    const ringGeo = new THREE.RingGeometry(p.size*1.5, p.size*2.5, 64);
    // proper UV mapping for a flat ring
    const posAttr = ringGeo.attributes.position;
    const v3 = new THREE.Vector3();
    for(let i=0;i<posAttr.count;i++){
      v3.fromBufferAttribute(posAttr,i);
      const d = (v3.length()-p.size*1.5)/(p.size*1.0);
      ringGeo.attributes.uv.setXY(i, d, 1);
    }
    const ringMat = new THREE.MeshStandardMaterial({map:ringTex, side:THREE.DoubleSide, transparent:true, opacity:0.85, roughness:1});
    const ring = new THREE.Mesh(ringGeo, ringMat);
    ring.rotation.x = Math.PI/2.15;
    mesh.add(ring);
  }

  // simple orbit path line
  const curve = new THREE.EllipseCurve(0,0, p.orbit,p.orbit);
  const pts = curve.getPoints(96).map(pt=>new THREE.Vector3(pt.x,0,pt.y));
  const orbitLine = new THREE.LineLoop(
    new THREE.BufferGeometry().setFromPoints(pts),
    new THREE.LineBasicMaterial({color:0x3a4360, transparent:true, opacity:0.5})
  );
  scene.add(orbitLine);

  planetMeshes.push({key:p.key, pivot, mesh, data:p});
});

/* ---- Asteroid belt (decorative, between Mars & Saturn) ---- */
(function belt(){
  const count = 420;
  const positions = new Float32Array(count*3);
  for(let i=0;i<count;i++){
    const r = 23 + Math.random()*2.3;
    const a = Math.random()*Math.PI*2;
    positions[i*3]   = Math.cos(a)*r;
    positions[i*3+1] = (Math.random()-0.5)*0.6;
    positions[i*3+2] = Math.sin(a)*r;
  }
  const geo = new THREE.BufferGeometry();
  geo.setAttribute('position', new THREE.BufferAttribute(positions,3));
  const mat = new THREE.PointsMaterial({color:0x8a7c6a, size:0.09});
  scene.add(new THREE.Points(geo, mat));
})();

/* ============================================================
   CAMERA CONTROL — free orbit + fly-to-planet
   ============================================================ */
let azimuth = 0.6, elevation = 0.5, radius = 42;
const minR=8, maxR=70, minEl=0.15, maxEl=1.35;
let autoRotate = true;
let dragging=false, lastX=0, lastY=0;

function sphericalToCartesian(){
  const x = radius*Math.sin(elevation)*Math.sin(azimuth);
  const y = radius*Math.cos(elevation);
  const z = radius*Math.sin(elevation)*Math.cos(azimuth);
  return new THREE.Vector3(x,y,z);
}

let camState = "OVERVIEW"; // OVERVIEW | FLYING | LOCKED
let lockedPlanet = null;
let lockOffset = new THREE.Vector3();
let tween = null; // {startPos,endPos,startTime,duration,onDone}

function easeInOutCubic(t){ return t<0.5 ? 4*t*t*t : 1-Math.pow(-2*t+2,3)/2; }

function flyTo(targetPos, lookAtPos, duration, onDone){
  camState = "FLYING";
  tween = {
    startPos: camera.position.clone(),
    endPos: targetPos.clone(),
    startTime: performance.now(),
    duration: duration,
    onDone: onDone,
    lookAt: lookAtPos.clone()
  };
}

canvas.addEventListener('pointerdown', e=>{
  if(camState!=="OVERVIEW") return;
  dragging=true; lastX=e.clientX; lastY=e.clientY; autoRotate=false;
});
window.addEventListener('pointermove', e=>{
  handleHover(e);
  if(!dragging) return;
  const dx=e.clientX-lastX, dy=e.clientY-lastY;
  azimuth -= dx*0.0045;
  elevation = Math.min(maxEl, Math.max(minEl, elevation - dy*0.0035));
  lastX=e.clientX; lastY=e.clientY;
});
window.addEventListener('pointerup', ()=>{ dragging=false; });
canvas.addEventListener('wheel', e=>{
  if(camState!=="OVERVIEW") return;
  radius = Math.min(maxR, Math.max(minR, radius + e.deltaY*0.02));
},{passive:true});

/* ---- Hover raycast + label ---- */
const raycaster = new THREE.Raycaster();
const pointerNDC = new THREE.Vector2();
const label = document.getElementById('target-label');
let hovered = null;

function handleHover(e){
  pointerNDC.x = (e.clientX/window.innerWidth)*2-1;
  pointerNDC.y = -(e.clientY/window.innerHeight)*2+1;
  raycaster.setFromCamera(pointerNDC, camera);
  const hits = raycaster.intersectObjects(planetMeshes.map(p=>p.mesh));
  if(hits.length){
    const key = hits[0].object.userData.key;
    const pd = planetMeshes.find(p=>p.key===key).data;
    hovered = key;
    canvas.style.cursor = "pointer";
    label.style.opacity = 1;
    label.style.left = e.clientX+"px";
    label.style.top = (e.clientY-70)+"px";
    label.querySelector('.name').textContent = pd.name;
    label.querySelector('.code').textContent = "TARGET LOCK // "+pd.code;
  } else {
    hovered=null; canvas.style.cursor="grab"; label.style.opacity=0;
  }
}
canvas.addEventListener('click', e=>{
  if(hovered && camState==="OVERVIEW"){ goToPlanet(hovered); }
});

/* ============================================================
   NAV PILLS + PANEL
   ============================================================ */
const navWrap = document.getElementById('nav-pills');
const homePill = document.createElement('button');
homePill.className='nav-pill'; homePill.textContent='Beranda';
homePill.addEventListener('click', ()=>returnHome());
navWrap.appendChild(homePill);

PLANETS.forEach(p=>{
  const btn = document.createElement('button');
  btn.className='nav-pill'; btn.textContent=p.title;
  btn.addEventListener('click', ()=>goToPlanet(p.key));
  btn.dataset.key = p.key;
  navWrap.appendChild(btn);
});

function setActivePill(key){
  navWrap.querySelectorAll('.nav-pill').forEach(b=>{
    b.classList.toggle('active', b.dataset.key===key);
  });
}

const panel = document.getElementById('panel');
const panelContent = document.getElementById('panel-content');
const panelClose = document.getElementById('panel-close');
const tmState = document.getElementById('tm-state');
const tmTarget = document.getElementById('tm-target');

function goToPlanet(key){
  const entry = planetMeshes.find(p=>p.key===key);
  if(!entry) return;
  const data = entry.data;
  setActivePill(key);
  tmState.textContent = "MENDEKATI TARGET";
  tmTarget.textContent = "TARGET: "+data.name.toUpperCase();
  label.style.opacity = 0;

  const planetPos = new THREE.Vector3();
  entry.mesh.getWorldPosition(planetPos);
  const dirOut = planetPos.clone().normalize();
  const camTarget = planetPos.clone().add(dirOut.multiplyScalar(data.size*4.2+2.2)).add(new THREE.Vector3(0,data.size*0.9,0));

  flyTo(camTarget, planetPos, 1900, ()=>{
    camState="LOCKED"; lockedPlanet=key;
    lockOffset = camera.position.clone().sub(planetPos);
    tmState.textContent = "BERLABUH";
    openPanel(data);
  });
}

function returnHome(){
  setActivePill(null);
  closePanel();
  camState="FLYING";
  tmState.textContent = "KEMBALI KE ORBIT";
  tmTarget.textContent = "TARGET: —";
  const overviewPos = sphericalToCartesian();
  flyTo(overviewPos, new THREE.Vector3(0,0,0), 1600, ()=>{
    camState="OVERVIEW"; lockedPlanet=null; autoRotate=true;
    tmState.textContent="ORBIT BEBAS";
  });
}

function openPanel(data){
  document.documentElement.style.setProperty('--accent-runtime', data.accent);
  panel.style.setProperty('--accent', data.accent);
  panelContent.innerHTML = `
    <div class="p-eyebrow">${data.eyebrow}</div>
    <h2>${data.title}</h2>
    <div class="p-tagline">${data.tagline}</div>
    <div class="telemetry-row">
      <div>DESIGNASI<span>${data.code}</span></div>
      <div>JARAK ORBIT<span>${data.distance}</span></div>
      <div>PERIODE<span>${data.period}</span></div>
    </div>
    <div class="p-body">${data.body}</div>
  `;
  panel.classList.add('open');
  panelClose.classList.add('open');
}
function closePanel(){
  panel.classList.remove('open');
  panelClose.classList.remove('open');
}
panelClose.addEventListener('click', returnHome);

/* ============================================================
   RENDER LOOP
   ============================================================ */
let last = performance.now();
function animate(now){
  requestAnimationFrame(animate);
  const dt = Math.min((now-last)/1000, 0.05);
  last = now;

  // planet motion
  planetMeshes.forEach(p=>{
    p.pivot.rotation.y += p.data.speed*0.05*dt*10;
    p.mesh.rotation.y += p.data.spin*0.15*dt;
  });
  sun.rotation.y += 0.01*dt*10;

  // camera tween
  if(tween){
    const t = Math.min(1,(now-tween.startTime)/tween.duration);
    const e = easeInOutCubic(t);
    camera.position.lerpVectors(tween.startPos, tween.endPos, e);
    camera.lookAt(tween.lookAt);
    if(t>=1){ const cb=tween.onDone; tween=null; if(cb) cb(); }
  } else if(camState==="OVERVIEW"){
    if(autoRotate) azimuth += 0.035*dt;
    camera.position.copy(sphericalToCartesian());
    camera.lookAt(0,0,0);
  } else if(camState==="LOCKED" && lockedPlanet){
    const entry = planetMeshes.find(p=>p.key===lockedPlanet);
    const planetPos = new THREE.Vector3();
    entry.mesh.getWorldPosition(planetPos);
    camera.position.copy(planetPos.clone().add(lockOffset));
    camera.lookAt(planetPos);
  }

  renderer.render(scene, camera);
}
requestAnimationFrame(animate);

window.addEventListener('resize', ()=>{
  camera.aspect = window.innerWidth/window.innerHeight;
  camera.updateProjectionMatrix();
  renderer.setSize(window.innerWidth, window.innerHeight);
});

/* ============================================================
   ENTRY SEQUENCE
   ============================================================ */
camera.position.set(0, 60, 160);
camera.lookAt(0,0,0);

document.getElementById('start-btn').addEventListener('click', ()=>{
  document.getElementById('hero').classList.add('hidden');
  camState="FLYING";
  tmState.textContent="MEMASUKI ORBIT";
  const overviewPos = sphericalToCartesian();
  flyTo(overviewPos, new THREE.Vector3(0,0,0), 2600, ()=>{
    camState="OVERVIEW"; autoRotate=true; tmState.textContent="ORBIT BEBAS";
  });
});

})();
</script>
</body>
</html>
