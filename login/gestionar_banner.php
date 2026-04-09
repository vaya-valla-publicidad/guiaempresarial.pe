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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gestionar Banner – Panel <?= ucfirst($rol) ?></title>
<link rel="stylesheet" href="/guiaempresarial.pe/assets/css/login.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.2/Sortable.min.js"></script>
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
<a href="<?= $panel_url ?>" class="btn btn-panel-nav-sep">← Volver al Panel</a>

<div class="banner-manager">

  <div class="size-tip">
    <i class="bi bi-info-circle-fill"></i>
    <div class="size-tip-text">
      <strong>📐 Tamaño recomendado para las imágenes del banner:</strong><br>
      Usa imágenes <strong>1920 × 600 px</strong> para que se vean perfectas en PC y celular.
      El alto mínimo es 480 px — imágenes más altas se recortarán verticalmente al centro.
      <div class="size-tags">
        <span class="size-tag success">✅ Ideal: 1920 × 600 px</span>
        <span class="size-tag success">✅ Bueno: 1280 × 480 px</span>
        <span class="size-tag warning">⚠️ Mínimo: 800 × 400 px</span>
        <span class="size-tag">📁 Máx. 5 MB</span>
        <span class="size-tag">🖼 JPG · PNG · WEBP</span>
      </div>
    </div>
  </div>

  <div class="drop-zone" id="drop-zone">
    <i class="bi bi-cloud-arrow-up drop-icon"></i>
    <span class="drop-label">Arrastra una imagen aquí para subirla</span>
    <span class="drop-sublabel">JPG, PNG, WEBP — máximo 5 MB</span>

    <div class="drop-preview-wrap" id="drop-preview-wrap">
      <img id="drop-preview-img" src="" alt="preview">
      <span class="drop-preview-name" id="drop-preview-name"></span>
    </div>

    <div class="drop-footer">
      <button class="btn btn-file-select" id="btn-file-select" type="button">
        <i class="bi bi-folder2-open"></i> Seleccionar archivo
      </button>
      <div class="time-upload-wrap" style="margin: 0 20px;">
        <label for="inp-tiempo"><i class="bi bi-clock"></i> Tiempo:</label>
        <input type="range" id="inp-tiempo" min="1" max="30" value="5" step="1">
        <span class="time-upload-val" id="inp-tiempo-val">5s</span>
      </div>
      <button class="btn btn-upload" id="btn-subir" disabled>
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

        <div class="banner-card-header">#<?= $pos++ ?></div>

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
            <button class="btn btn-save-time" data-id="<?= $b['id_banner'] ?>">Guardar tiempo</button>
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