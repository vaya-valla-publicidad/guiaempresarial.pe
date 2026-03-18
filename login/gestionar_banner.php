<?php
require_once __DIR__ . '/proteger.php';
include '../db.php';
$rol = $_SESSION['rol'];

$banners = $conexion->query("SELECT * FROM banner_carrusel ORDER BY orden ASC, id_banner ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Gestionar Banner – Panel <?= ucfirst($rol) ?></title>
<link rel="stylesheet" href="/guiaempresarial.pe/assets/css/login.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.2/Sortable.min.js"></script>
<style>
:root {
  --accent:  #f5a623;
  --danger:  #e74c3c;
  --success: #27ae60;
  --muted:   #888;
  --card-bg: #1e1e2e;
  --border:  #2e2e42;
}

.banner-manager { max-width: 960px; margin: 30px auto; padding: 0 16px; }

.size-tip {
  background: #1a2a1a;
  border: 1px solid #2d4a2d;
  border-radius: 10px;
  padding: 12px 16px;
  margin-bottom: 20px;
  display: flex;
  align-items: flex-start;
  gap: 10px;
}
.size-tip i { color: #4caf50; font-size: 18px; flex-shrink: 0; margin-top: 2px; }
.size-tip-text { font-size: 13px; color: #aad5aa; line-height: 1.6; }
.size-tip-text strong { color: #6ee06e; }
.size-tip-text .size-tags { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 6px; }
.size-tag {
  background: #2d4a2d; color: #7de07d;
  font-size: 11px; font-weight: 700;
  padding: 2px 9px; border-radius: 20px;
  border: 1px solid #3d6a3d;
}

.drop-zone {
  background: var(--card-bg);
  border: 2px dashed var(--border);
  border-radius: 14px;
  padding: 32px 24px 24px;
  margin-bottom: 28px;
  transition: border-color .25s, background .25s;
  cursor: pointer;
  text-align: center;
  position: relative;
}
.drop-zone.drag-over {
  border-color: var(--accent);
  background: #f5a62312;
}
.drop-zone.has-file { border-color: var(--success); }

.drop-icon {
  font-size: 42px; color: var(--muted);
  display: block; margin-bottom: 8px;
  transition: color .2s, transform .25s;
  pointer-events: none;
}
.drop-zone.drag-over .drop-icon { color: var(--accent); transform: scale(1.18) translateY(-4px); }
.drop-zone.has-file   .drop-icon { color: var(--success); }

.drop-label {
  font-size: 15px; color: #ccc;
  display: block; margin-bottom: 4px;
  pointer-events: none;
}
.drop-label strong { color: var(--accent); }
.drop-sublabel {
  font-size: 12px; color: var(--muted);
  margin-bottom: 20px; display: block;
  pointer-events: none;
}

.drop-preview-wrap { display: none; justify-content: center; flex-direction: column; align-items: center; gap: 6px; margin-bottom: 16px; }
.drop-preview-wrap.show { display: flex; }
.drop-preview-wrap img {
  max-height: 110px; max-width: 100%;
  border-radius: 10px;
  border: 2px solid var(--success);
  box-shadow: 0 4px 16px #0005;
}
.drop-preview-name { font-size: 11px; color: var(--muted); }

.drop-footer {
  display: flex; align-items: center;
  justify-content: center; gap: 14px; flex-wrap: wrap;
}
.btn-file-select {
  background: #2a2a3e; color: #ccc;
  border: 1px solid var(--border); border-radius: 8px;
  padding: 8px 16px; cursor: pointer; font-size: 13px;
  transition: background .2s; display: flex; align-items: center; gap: 6px;
}
.btn-file-select:hover { background: #3a3a55; color: #fff; }
.time-upload-wrap { display: flex; align-items: center; gap: 8px; }
.time-upload-wrap label { color: #bbb; font-size: 13px; white-space: nowrap; }
.time-upload-wrap input[type="range"] { width: 110px; accent-color: var(--accent); }
.time-upload-val { color: var(--accent); font-size: 13px; font-weight: 700; min-width: 34px; }
.btn-upload {
  background: var(--accent); color: #111;
  font-weight: 700; border: none; padding: 9px 22px;
  border-radius: 8px; cursor: pointer; font-size: 14px;
  display: flex; align-items: center; gap: 6px; transition: opacity .2s;
}
.btn-upload:hover { opacity: .85; }
.btn-upload:disabled { opacity: .4; cursor: not-allowed; }
#inp-file { display: none; }

.banner-toolbar {
  display: flex; align-items: center;
  justify-content: space-between; flex-wrap: wrap;
  gap: 10px; margin-bottom: 14px;
}
.banner-toolbar span { color: var(--muted); font-size: 13px; }
.btn-save-order {
  display: none; background: var(--success); color: #fff;
  border: none; padding: 8px 18px; border-radius: 8px;
  font-size: 13px; font-weight: 700; cursor: pointer;
  align-items: center; gap: 6px; transition: opacity .2s;
}
.btn-save-order.visible { display: flex; }
.btn-save-order:hover { opacity: .85; }

.banner-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 16px;
}
.banner-card {
  background: var(--card-bg);
  border: 2px solid var(--border);
  border-radius: 12px; overflow: hidden;
  cursor: grab; transition: border-color .2s, box-shadow .2s, opacity .2s;
  user-select: none; position: relative;
}
.banner-card:active { cursor: grabbing; }
.banner-card.sortable-chosen { border-color: var(--accent); box-shadow: 0 0 0 3px #f5a62333; opacity: .85; }
.banner-card.sortable-ghost  { opacity: .2; }
.banner-card.inactivo .banner-card-img img { filter: grayscale(1) brightness(.55); }

.card-orden {
  position: absolute; top: 8px; left: 8px; z-index: 5;
  background: rgba(0,0,0,.65); color: #fff;
  font-size: 12px; font-weight: 700;
  padding: 2px 8px; border-radius: 20px; pointer-events: none;
}
.banner-card-img { width: 100%; height: 130px; overflow: hidden; }
.banner-card-img img { width: 100%; height: 100%; object-fit: cover; display: block; transition: filter .3s; }
.img-placeholder {
  width: 100%; height: 100%; background: #2a2a3e;
  display: flex; align-items: center; justify-content: center;
  color: var(--muted); font-size: 30px;
}

.banner-card-footer { padding: 10px 12px; display: flex; flex-direction: column; gap: 8px; }

.time-control { display: flex; flex-direction: column; gap: 4px; }
.time-control-row { display: flex; align-items: center; gap: 6px; }
.time-control label { font-size: 11px; color: var(--muted); display: flex; align-items: center; gap: 4px; }
.time-control input[type="range"] { flex: 1; accent-color: var(--accent); height: 4px; }
.time-val { font-size: 12px; font-weight: 700; color: var(--accent); min-width: 34px; text-align: right; }
.btn-save-time {
  font-size: 11px; padding: 3px 10px;
  background: #2a2a3e; color: #ccc;
  border: 1px solid var(--border); border-radius: 6px;
  cursor: pointer; transition: background .2s, color .2s; align-self: flex-end;
}
.btn-save-time:hover { background: var(--success); color: #fff; border-color: var(--success); }
.btn-save-time.saved  { background: var(--success); color: #fff; border-color: var(--success); }

.card-controls { display: flex; align-items: center; justify-content: space-between; }
.switch { position: relative; display: inline-block; width: 38px; height: 20px; }
.switch input { opacity: 0; width: 0; height: 0; }
.slider-sw {
  position: absolute; cursor: pointer; inset: 0;
  background: #444; border-radius: 20px; transition: .3s;
}
.slider-sw:before {
  content: ''; position: absolute;
  height: 14px; width: 14px; left: 3px; bottom: 3px;
  background: #fff; border-radius: 50%; transition: .3s;
}
input:checked + .slider-sw { background: var(--success); }
input:checked + .slider-sw:before { transform: translateX(18px); }
.estado-label { font-size: 11px; color: var(--muted); }
.btn-del {
  background: none; border: 1px solid var(--danger);
  color: var(--danger); border-radius: 6px;
  padding: 4px 9px; cursor: pointer; font-size: 14px;
  transition: background .2s, color .2s;
}
.btn-del:hover { background: var(--danger); color: #fff; }

.empty-state { text-align: center; padding: 56px 20px; color: var(--muted); grid-column: 1/-1; }
.empty-state i { font-size: 48px; display: block; margin-bottom: 14px; }

#toast {
  position: fixed; bottom: 24px; right: 24px; z-index: 9999;
  background: #1e1e2e; color: #eee;
  padding: 12px 20px; border-radius: 10px;
  border-left: 4px solid var(--success);
  box-shadow: 0 8px 24px #0008; font-size: 14px;
  transform: translateY(80px); opacity: 0;
  transition: transform .3s, opacity .3s; pointer-events: none;
}
#toast.show  { transform: translateY(0); opacity: 1; }
#toast.error { border-color: var(--danger); }
</style>
</head>
<body>

<div class="panel-container">
<section class="panel">

<h1 class="panel-title">🖼 Gestionar Banner Principal</h1>

<div class="usuario-info">
    Bienvenido, <?= htmlspecialchars($_SESSION['usuario']) ?> |
    <a href="cerrar.php">Cerrar sesión</a>
</div>

<?php
$panel_url = ($rol === 'admin') ? 'admin.php' : 'editor.php';
?>
<a href="<?= $panel_url ?>" class="btn" style="margin-bottom:20px;display:inline-block;">← Volver al Panel</a>

<div class="banner-manager">

  <div class="size-tip">
    <i class="bi bi-info-circle-fill"></i>
    <div class="size-tip-text">
      <strong>📐 Tamaño recomendado para las imágenes del banner:</strong><br>
      Usa imágenes <strong>1920 × 600 px</strong> para que se vean perfectas en PC y celular.
      El alto mínimo es 480 px — imágenes más altas se recortarán verticalmente al centro.
      <div class="size-tags">
        <span class="size-tag">✅ Ideal: 1920 × 600 px</span>
        <span class="size-tag">✅ Bueno: 1280 × 480 px</span>
        <span class="size-tag">⚠️ Mínimo: 800 × 400 px</span>
        <span class="size-tag">📁 Máx. 5 MB</span>
        <span class="size-tag">🖼 JPG · PNG · WEBP</span>
      </div>
    </div>
  </div>

  <div class="drop-zone" id="drop-zone">
    <i class="bi bi-cloud-arrow-up drop-icon"></i>
    <span class="drop-label">Arrastra una imagen aquí o <strong>haz clic para seleccionar</strong></span>
    <span class="drop-sublabel">JPG, PNG, WEBP, GIF — máximo 5 MB</span>

    <div class="drop-preview-wrap" id="drop-preview-wrap">
      <img id="drop-preview-img" src="" alt="preview">
      <span class="drop-preview-name" id="drop-preview-name"></span>
    </div>

    <div class="drop-footer">
      <button class="btn-file-select" id="btn-file-select" type="button">
        <i class="bi bi-folder2-open"></i> Seleccionar archivo
      </button>
      <div class="time-upload-wrap">
        <label for="inp-tiempo"><i class="bi bi-clock"></i> Tiempo:</label>
        <input type="range" id="inp-tiempo" min="1" max="30" value="5" step="1">
        <span class="time-upload-val" id="inp-tiempo-val">5s</span>
      </div>
      <button class="btn-upload" id="btn-subir" disabled>
        <i class="bi bi-plus-circle"></i> Agregar al banner
      </button>
    </div>
  </div>
  <input type="file" id="inp-file" accept="image/*">

  <div class="banner-toolbar">
    <span><i class="bi bi-grip-horizontal"></i> Arrastra las tarjetas para reordenar</span>
    <button class="btn-save-order" id="btn-guardar-orden">
      <i class="bi bi-check2-circle"></i> Guardar orden
    </button>
  </div>

  <div class="banner-grid" id="banner-grid">
    <?php if ($banners && $banners->num_rows > 0): ?>
      <?php $pos = 1; while ($b = $banners->fetch_assoc()): ?>
      <div class="banner-card <?= !$b['activo'] ? 'inactivo' : '' ?>"
           data-id="<?= $b['id_banner'] ?>">

        <span class="card-orden">#<?= $pos++ ?></span>

        <div class="banner-card-img">
          <img src="/guiaempresarial.pe/assets/img/banner/<?= htmlspecialchars($b['imagen']) ?>"
               alt="banner"
               onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
          <div class="img-placeholder" style="display:none;"><i class="bi bi-image"></i></div>
        </div>

        <div class="banner-card-footer">
          <div class="time-control">
            <div class="time-control-row">
              <label><i class="bi bi-clock"></i> Duración</label>
              <input type="range" class="rng-tiempo" data-id="<?= $b['id_banner'] ?>"
                     min="1" max="30" value="<?= round($b['tiempo_ms'] / 1000) ?>">
              <span class="time-val"><?= round($b['tiempo_ms'] / 1000) ?>s</span>
            </div>
            <button class="btn-save-time" data-id="<?= $b['id_banner'] ?>">Guardar tiempo</button>
          </div>
          <div class="card-controls">
            <div style="display:flex;align-items:center;gap:7px;">
              <label class="switch">
                <input type="checkbox" class="chk-activo" data-id="<?= $b['id_banner'] ?>"
                       <?= $b['activo'] ? 'checked' : '' ?>>
                <span class="slider-sw"></span>
              </label>
              <span class="estado-label"><?= $b['activo'] ? 'Activa' : 'Inactiva' ?></span>
            </div>
            <button class="btn-del btn-eliminar" data-id="<?= $b['id_banner'] ?>" title="Eliminar">
              <i class="bi bi-trash3"></i>
            </button>
          </div>
        </div>
      </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="empty-state">
        <i class="bi bi-images"></i>
        No hay imágenes en el banner aún. ¡Sube la primera!
      </div>
    <?php endif; ?>
  </div>

</div>
</section>
</div>
<div id="toast"></div>

<script>
const ACTIONS_URL = 'banner_actions.php';

function toast(msg, error = false) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = 'show' + (error ? ' error' : '');
  clearTimeout(t._tid);
  t._tid = setTimeout(() => t.className = '', 3200);
}

let selectedFile = null;

function setFile(file) {
  if (!file) return;
  const allowed = ['image/jpeg','image/png','image/webp','image/gif'];
  if (!allowed.includes(file.type)) { toast('Formato no permitido. Usa JPG, PNG, WEBP o GIF.', true); return; }
  if (file.size > 5 * 1024 * 1024) { toast('El archivo supera los 5 MB.', true); return; }

  selectedFile = file;

  const wrap = document.getElementById('drop-preview-wrap');
  const img  = document.getElementById('drop-preview-img');
  const name = document.getElementById('drop-preview-name');
  img.src  = URL.createObjectURL(file);
  name.textContent = file.name + ' (' + (file.size / 1024).toFixed(0) + ' KB)';
  wrap.classList.add('show');

  document.getElementById('drop-zone').classList.add('has-file');
  document.getElementById('btn-subir').disabled = false;
}

document.getElementById('drop-zone').addEventListener('click', e => {
  if (e.target.closest('#btn-subir') || e.target.closest('.time-upload-wrap')) return;
  document.getElementById('inp-file').click();
});
document.getElementById('btn-file-select').addEventListener('click', e => {
  e.stopPropagation();
  document.getElementById('inp-file').click();
});
document.getElementById('inp-file').addEventListener('change', function () {
  if (this.files[0]) setFile(this.files[0]);
});

const dropZone = document.getElementById('drop-zone');

dropZone.addEventListener('dragenter', e => { e.preventDefault(); dropZone.classList.add('drag-over'); });
dropZone.addEventListener('dragover',  e => { e.preventDefault(); dropZone.classList.add('drag-over'); });
dropZone.addEventListener('dragleave', e => {
  if (!dropZone.contains(e.relatedTarget)) dropZone.classList.remove('drag-over');
});
dropZone.addEventListener('drop', e => {
  e.preventDefault();
  dropZone.classList.remove('drag-over');
  const file = e.dataTransfer.files[0];
  if (file) setFile(file);
});

window.addEventListener('dragover', e => e.preventDefault());
window.addEventListener('drop',     e => e.preventDefault());

const inpTiempo    = document.getElementById('inp-tiempo');
const inpTiempoVal = document.getElementById('inp-tiempo-val');
inpTiempo.addEventListener('input', () => inpTiempoVal.textContent = inpTiempo.value + 's');

document.getElementById('btn-subir').addEventListener('click', async () => {
  if (!selectedFile) return;
  const btn = document.getElementById('btn-subir');
  btn.disabled = true; btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Subiendo…';

  const fd = new FormData();
  fd.append('accion',    'subir');
  fd.append('imagen',    selectedFile);
  fd.append('tiempo_ms', inpTiempo.value * 1000);

  try {
    const r = await fetch(ACTIONS_URL, { method: 'POST', body: fd });
    const d = await r.json();
    if (d.ok) { toast('✅ Imagen agregada al banner.'); setTimeout(() => location.reload(), 800); }
    else       toast(d.error || 'Error al subir.', true);
  } catch { toast('Error de conexión.', true); }
  finally {
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-plus-circle"></i> Agregar al banner';
  }
});

document.getElementById('banner-grid').addEventListener('input', e => {
  if (!e.target.classList.contains('rng-tiempo')) return;
  const card = e.target.closest('.banner-card');
  const val  = card?.querySelector('.time-val');
  if (val) val.textContent = e.target.value + 's';
  const btn = card?.querySelector('.btn-save-time');
  if (btn) { btn.textContent = 'Guardar tiempo ●'; btn.classList.remove('saved'); }
});

document.getElementById('banner-grid').addEventListener('click', async e => {

  // Guardar tiempo
  const btnT = e.target.closest('.btn-save-time');
  if (btnT) {
    const id  = btnT.dataset.id;
    const rng = document.querySelector(`.rng-tiempo[data-id="${id}"]`);
    const ms  = rng ? rng.value * 1000 : 5000;
    const fd  = new FormData();
    fd.append('accion', 'set_tiempo'); fd.append('id', id); fd.append('tiempo_ms', ms);
    const r = await fetch(ACTIONS_URL, { method: 'POST', body: fd });
    const d = await r.json();
    if (d.ok) {
      btnT.textContent = 'Guardado ✓'; btnT.classList.add('saved');
      toast('⏱ Tiempo guardado.');
      setTimeout(() => { btnT.textContent = 'Guardar tiempo'; btnT.classList.remove('saved'); }, 2500);
    } else toast(d.error || 'Error.', true);
    return;
  }

  const btnD = e.target.closest('.btn-eliminar');
  if (btnD) {
    if (!confirm('¿Eliminar esta imagen del banner?')) return;
    const id = btnD.dataset.id;
    const fd = new FormData();
    fd.append('accion', 'eliminar'); fd.append('id', id);
    const r = await fetch(ACTIONS_URL, { method: 'POST', body: fd });
    const d = await r.json();
    if (d.ok) {
      document.querySelector(`.banner-card[data-id="${id}"]`)?.remove();
      renumerarOrden();
      toast('🗑 Imagen eliminada.');
    } else toast(d.error || 'Error.', true);
    return;
  }
});

document.getElementById('banner-grid').addEventListener('change', async e => {
  if (!e.target.classList.contains('chk-activo')) return;
  const id = e.target.dataset.id;
  const fd = new FormData();
  fd.append('accion', 'toggle_activo'); fd.append('id', id);
  const r = await fetch(ACTIONS_URL, { method: 'POST', body: fd });
  const d = await r.json();
  if (d.ok) {
    const card  = document.querySelector(`.banner-card[data-id="${id}"]`);
    const label = card?.querySelector('.estado-label');
    card?.classList.toggle('inactivo', d.activo === 0);
    if (label) label.textContent = d.activo ? 'Activa' : 'Inactiva';
    toast(d.activo ? '✅ Imagen activada.' : '⏸ Imagen desactivada.');
  } else toast(d.error || 'Error.', true);
});

Sortable.create(document.getElementById('banner-grid'), {
  animation: 200,
  ghostClass: 'sortable-ghost',
  chosenClass: 'sortable-chosen',
  filter: 'input, button, label',
  preventOnFilter: false,
  onEnd: () => {
    renumerarOrden();
    document.getElementById('btn-guardar-orden').classList.add('visible');
  }
});

function renumerarOrden() {
  document.querySelectorAll('.banner-card').forEach((c, i) => {
    const b = c.querySelector('.card-orden');
    if (b) b.textContent = '#' + (i + 1);
  });
}

document.getElementById('btn-guardar-orden').addEventListener('click', async () => {
  const ids = [...document.querySelectorAll('.banner-card')].map(c => c.dataset.id);
  const fd  = new FormData();
  fd.append('accion', 'reordenar'); fd.append('ids', JSON.stringify(ids));
  const r = await fetch(ACTIONS_URL, { method: 'POST', body: fd });
  const d = await r.json();
  if (d.ok) {
    toast('✅ Orden guardado.');
    document.getElementById('btn-guardar-orden').classList.remove('visible');
  } else toast(d.error || 'Error.', true);
});
</script>

</body>
</html>