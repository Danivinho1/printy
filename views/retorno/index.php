<?php
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * Vista: Retorno
 * Requiere variables:
 * - string $month
 * - array  $asesores          // [{id, nombre}, ...]
 * - array  $asesoresById      // [id => nombre]
 * - float  $retorno, $organico, $recompra, $desconocido, $web, $extra, $granTotal
 * - array  $porAsesor         // ['retorno'=>[asesor_id=>monto], ... por categoría]
 * - array  $deposito          // ['fecha'=>Y-m-d, 'monto'=>float, 'resta'=>float, 'confirmaciones'=>[asesor_id=>'si'|'no'|'']]
 */

$this->title = 'Retorno';
$this->params['breadcrumbs'][] = ['label' => 'Marketing', 'url' => ['campanas/index']];
$this->params['breadcrumbs'][] = $this->title;

$fmtMoney = fn($n) => Yii::$app->formatter->asCurrency((float)($n ?? 0), 'MXN');

// Asegurar orden descendente por monto en las listas por asesor
$sorted = [];
foreach (['retorno','organico','recompra','desconocido','web','extra'] as $k) {
    $sorted[$k] = $porAsesor[$k] ?? [];
    if (!empty($sorted[$k]) && is_array($sorted[$k])) {
        arsort($sorted[$k]);
    }
}

$this->registerCss(<<<CSS
/* Fondo sutil y contraste de texto */
.ret-bg {
  background-image:
    radial-gradient(80% 50% at 20% 0%, rgba(99,102,241,0.08) 0%, rgba(99,102,241,0.0) 60%),
    radial-gradient(60% 50% at 110% 20%, rgba(236,72,153,0.08) 0%, rgba(236,72,153,0.0) 60%),
    linear-gradient(#f1f5f9 1px, transparent 1px),
    linear-gradient(90deg, #f1f5f9 1px, transparent 1px);
  background-size: auto, auto, 24px 24px, 24px 24px;
  background-position: center, center, -1px -1px, -1px -1px;
  background-color: #ffffff;
}
.card { border-radius: 1rem; border: 1px solid #e5e7eb; background: #ffffff; box-shadow: 0 8px 20px rgba(2,6,23,.04); }
.card-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:.5rem; }
.badge { display:inline-flex; align-items:center; padding:2px 8px; border-radius:9999px; font-size:12px; font-weight:600; }
.navtab { display:inline-flex; align-items:center; padding:.5rem .75rem; border-radius:.75rem; border:1px solid #e5e7eb; color:#334155; transition:background-color .15s; }
.navtab:hover { background:#f8fafc; }
.navtab-active { background-image:linear-gradient(90deg,#4f46e5,#7c3aed); color:#fff; border-color:transparent; box-shadow:0 8px 24px rgba(79,70,229,.25); }
.table-lite tr + tr { border-top: 1px solid #eef2f7; }
.btn { display:inline-flex; align-items:center; gap:.5rem; padding:.5rem .75rem; border-radius:.75rem; border:1px solid #d1d5db; color:#111827; transition:background-color .15s, opacity .15s; }
.btn:hover { background:#f3f4f6; }
.btn-primary { background-image:linear-gradient(90deg,#4f46e5,#7c3aed); color:#fff; border-color:transparent; }
.btn-primary:hover { opacity:.95; }
.h-title { font-size:.875rem; font-weight:600; color:#334155; }
.h-value { font-size:1.5rem; font-weight:800; color:#0f172a; }
.icon { width:22px; height:22px; }
CSS);
?>

<div class="marketing-retorno ret-bg min-h-screen text-slate-900">
  <div class="mx-auto max-w-7xl px-4 py-6 space-y-6">

    <!-- Tabs superiores -->
    <div class="flex items-center justify-between">
      <div class="flex gap-2 text-sm">
        <a class="navtab" href="<?= Url::to(['campanas/index']) ?>">Campañas</a>
        <a class="navtab navtab-active" href="<?= Url::to(['retorno/index']) ?>">Retorno</a>
        <a class="navtab" href="<?= Url::to(['ventas-mensuales/index']) ?>">Comparativas</a>
      </div>

      <!-- Selector de mes -->
      <div class="flex items-center gap-2">
        <span class="text-sm text-slate-700">Mes</span>
        <input type="month" id="monthPicker" value="<?= Html::encode($month) ?>"
               class="border border-slate-300 rounded-xl px-3 py-2 text-sm text-slate-900 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-200" />
        <button class="btn" onclick="changeMonth()">
          <svg class="icon" viewBox="0 0 24 24" fill="none"><path d="M8 7h8M8 12h8m-8 5h8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
          Ver
        </button>
      </div>
    </div>

    <!-- Hero resumen -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      <div class="card lg:col-span-2 p-5">
        <div class="flex items-center justify-between">
          <div>
            <div class="text-xs uppercase tracking-wide text-slate-600">Retorno total del mes</div>
            <div class="h-value mt-1"><?= $fmtMoney($granTotal) ?></div>
          </div>
          <div class="hidden sm:flex items-center gap-2">
            <span class="badge bg-indigo-50 text-indigo-700">Retorno</span>
            <span class="badge bg-emerald-50 text-emerald-700">Orgánico</span>
            <span class="badge bg-amber-50 text-amber-700">Recompra</span>
            <span class="badge bg-slate-100 text-slate-800">Desconocido</span>
            <span class="badge bg-pink-50 text-pink-700">Web</span>
            <span class="badge bg-purple-50 text-purple-700">Extra</span>
          </div>
        </div>
        <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
          <div class="rounded-xl p-3 bg-indigo-50/80">
            <div class="h-title">Retorno</div>
            <div class="font-semibold text-indigo-700"><?= $fmtMoney($retorno) ?></div>
          </div>
          <div class="rounded-xl p-3 bg-emerald-50/80">
            <div class="h-title">Orgánico</div>
            <div class="font-semibold text-emerald-700"><?= $fmtMoney($organico) ?></div>
          </div>
          <div class="rounded-xl p-3 bg-amber-50/80">
            <div class="h-title">Recompra</div>
            <div class="font-semibold text-amber-700"><?= $fmtMoney($recompra) ?></div>
          </div>
          <div class="rounded-xl p-3 bg-slate-100">
            <div class="h-title">Desconocido</div>
            <div class="font-semibold text-slate-800"><?= $fmtMoney($desconocido) ?></div>
          </div>
          <div class="rounded-xl p-3 bg-pink-50/80">
            <div class="h-title">Página web</div>
            <div class="font-semibold text-pink-700"><?= $fmtMoney($web) ?></div>
          </div>
          <div class="rounded-xl p-3 bg-purple-50/80">
            <div class="h-title">Extra</div>
            <div class="font-semibold text-purple-700"><?= $fmtMoney($extra) ?></div>
          </div>
        </div>
      </div>

      <!-- Tarjeta con gradiente -->
      <div class="rounded-2xl p-5 text-white shadow-md"
           style="background-image: linear-gradient(135deg,#4f46e5 0%, #d946ef 50%, #7c3aed 100%);">
        <div class="flex items-center justify-between">
          <div>
            <div class="text-xs uppercase tracking-wider text-white/90">Gran total</div>
            <div class="text-3xl font-extrabold mt-1"><?= $fmtMoney($granTotal) ?></div>
          </div>
          <svg class="w-12 h-12 opacity-90" viewBox="0 0 24 24" fill="none">
            <path d="M3 12h4l3 8 4-16 3 8h4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
        <div class="mt-4 text-sm text-white/95">Suma de todas las categorías del mes seleccionado.</div>
      </div>
    </div>

    <!-- Tarjetas por categoría con listas -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
      <!-- Retorno -->
      <div class="card p-5">
        <div class="card-header">
          <div class="flex items-center gap-2">
            <span class="badge bg-indigo-50 text-indigo-700">Retorno</span>
          </div>
          <div class="text-lg font-bold text-indigo-700"><?= $fmtMoney($retorno) ?></div>
        </div>
        <?php if (!empty($sorted['retorno'])): ?>
          <table class="w-full text-sm table-lite">
            <tbody>
            <?php foreach ($sorted['retorno'] as $aid => $monto): ?>
              <tr>
                <td class="py-2 text-slate-800"><?= Html::encode($asesoresById[$aid] ?? ('Asesor #' . $aid)) ?></td>
                <td class="py-2 text-right font-semibold text-slate-900"><?= $fmtMoney($monto) ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <div class="text-sm text-slate-700">Sin datos para este mes.</div>
        <?php endif; ?>
      </div>

      <!-- Orgánico -->
      <div class="card p-5">
        <div class="card-header">
          <div class="badge bg-emerald-50 text-emerald-700">Orgánico</div>
          <div class="text-lg font-bold text-emerald-700"><?= $fmtMoney($organico) ?></div>
        </div>
        <?php if (!empty($sorted['organico'])): ?>
          <table class="w-full text-sm table-lite">
            <tbody>
            <?php foreach ($sorted['organico'] as $aid => $monto): ?>
              <tr>
                <td class="py-2 text-slate-800"><?= Html::encode($asesoresById[$aid] ?? ('Asesor #' . $aid)) ?></td>
                <td class="py-2 text-right font-semibold text-slate-900"><?= $fmtMoney($monto) ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <div class="text-sm text-slate-700">Sin datos para este mes.</div>
        <?php endif; ?>
      </div>

      <!-- Recompra -->
      <div class="card p-5">
        <div class="card-header">
          <div class="badge bg-amber-50 text-amber-700">Recompra</div>
          <div class="text-lg font-bold text-amber-700"><?= $fmtMoney($recompra) ?></div>
        </div>
        <?php if (!empty($sorted['recompra'])): ?>
          <table class="w-full text-sm table-lite">
            <tbody>
            <?php foreach ($sorted['recompra'] as $aid => $monto): ?>
              <tr>
                <td class="py-2 text-slate-800"><?= Html::encode($asesoresById[$aid] ?? ('Asesor #' . $aid)) ?></td>
                <td class="py-2 text-right font-semibold text-slate-900"><?= $fmtMoney($monto) ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <div class="text-sm text-slate-700">Sin datos para este mes.</div>
        <?php endif; ?>
      </div>

      <!-- Desconocido -->
      <div class="card p-5">
        <div class="card-header">
          <div class="badge bg-slate-100 text-slate-800">Desconocido</div>
          <div class="text-lg font-bold text-slate-800"><?= $fmtMoney($desconocido) ?></div>
        </div>
        <?php if (!empty($sorted['desconocido'])): ?>
          <table class="w-full text-sm table-lite">
            <tbody>
            <?php foreach ($sorted['desconocido'] as $aid => $monto): ?>
              <tr>
                <td class="py-2 text-slate-800"><?= Html::encode($asesoresById[$aid] ?? ('Asesor #' . $aid)) ?></td>
                <td class="py-2 text-right font-semibold text-slate-900"><?= $fmtMoney($monto) ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <div class="text-sm text-slate-700">Sin datos para este mes.</div>
        <?php endif; ?>
      </div>

      <!-- Página web -->
      <div class="card p-5">
        <div class="card-header">
          <div class="badge bg-pink-50 text-pink-700">Página web</div>
          <div class="text-lg font-bold text-pink-700"><?= $fmtMoney($web) ?></div>
        </div>
        <table class="w-full text-sm table-lite">
          <tbody>
            <tr>
              <td class="py-2 text-slate-800">Tienda Online</td>
              <td class="py-2 text-right font-semibold text-slate-900"><?= $fmtMoney($web) ?></td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Extra -->
      <div class="card p-5">
        <div class="card-header">
          <div class="badge bg-purple-50 text-purple-700">Extra</div>
          <div class="text-lg font-bold text-purple-700"><?= $fmtMoney($extra) ?></div>
        </div>
        <div class="space-y-2">
          <?php foreach ($asesores as $a): $aid = (int)$a['id']; $totalExtra = (float)($sorted['extra'][$aid] ?? 0); ?>
            <div class="flex items-center gap-2">
              <div class="w-44 text-sm font-medium text-slate-800"><?= Html::encode($a['nombre']) ?></div>
              <select class="border border-slate-300 rounded-xl px-3 py-2 text-sm text-slate-900 bg-white js-campanas focus:outline-none focus:ring-2 focus:ring-purple-200"
                      data-asesor-id="<?= $aid ?>">
                <option value="">-- Campañas activas --</option>
              </select>
              <div class="ml-auto text-sm text-slate-700">Total: <?= $fmtMoney($totalExtra) ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Retornos mensuales por asesor -->
    <div class="space-y-3">
      <h3 class="text-lg font-semibold text-slate-900">Retornos mensuales por asesor</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        <?php foreach ($asesores as $a):
          $aid = (int)$a['id'];
          $r  = (float)($sorted['retorno'][$aid] ?? 0);
          $o  = (float)($sorted['organico'][$aid] ?? 0);
          $rc = (float)($sorted['recompra'][$aid] ?? 0);
          $d  = (float)($sorted['desconocido'][$aid] ?? 0);
          $w  = (float)($sorted['web'][$aid] ?? 0);
          $e  = (float)($sorted['extra'][$aid] ?? 0);
          $totAsesor = $r + $o + $rc + $d + $w + $e;
        ?>
          <div class="card p-5">
            <div class="flex items-center justify-between">
              <div class="font-medium text-slate-900"><?= Html::encode($a['nombre']) ?></div>
              <div class="text-base font-bold text-slate-900"><?= $fmtMoney($totAsesor) ?></div>
            </div>
            <div class="mt-3 grid grid-cols-2 gap-2 text-xs text-slate-800">
              <div class="flex justify-between"><span>Retorno</span><strong class="text-indigo-700"><?= $fmtMoney($r) ?></strong></div>
              <div class="flex justify-between"><span>Orgánico</span><strong class="text-emerald-700"><?= $fmtMoney($o) ?></strong></div>
              <div class="flex justify-between"><span>Recompra</span><strong class="text-amber-700"><?= $fmtMoney($rc) ?></strong></div>
              <div class="flex justify-between"><span>Desconocido</span><strong class="text-slate-800"><?= $fmtMoney($d) ?></strong></div>
              <div class="flex justify-between"><span>Web</span><strong class="text-pink-700"><?= $fmtMoney($w) ?></strong></div>
              <div class="flex justify-between"><span>Extra</span><strong class="text-purple-700"><?= $fmtMoney($e) ?></strong></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Fecha de depósito -->
    <div class="card p-5">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-slate-900">Fecha de depósito</h3>
        <span class="badge bg-indigo-50 text-indigo-700">Mensual</span>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div>
          <label class="block text-sm text-slate-700 mb-1">Fecha</label>
          <input type="date" id="depFecha" value="<?= Html::encode($deposito['fecha'] ?? '') ?>"
                 class="border border-slate-300 rounded-xl px-3 py-2 w-full text-slate-900 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-200" />
        </div>
        <div>
          <label class="block text-sm text-slate-700 mb-1">Dinero depositado</label>
          <input type="number" id="depMonto" step="0.01" min="0" value="<?= Html::encode((string)($deposito['monto'] ?? 0)) ?>"
                 class="border border-slate-300 rounded-xl px-3 py-2 w-full text-slate-900 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-200" />
        </div>
        <div>
          <label class="block text-sm text-slate-700 mb-1">Restante del mes</label>
          <input type="number" id="depResta" step="0.01" min="0" value="<?= Html::encode((string)($deposito['resta'] ?? 0)) ?>"
                 class="border border-slate-300 rounded-xl px-3 py-2 w-full text-slate-900 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-200" />
        </div>
      </div>

      <!-- Totales de Depósito -->
      <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="rounded-xl p-3 bg-indigo-50/70">
          <div class="h-title">Total depositado</div>
          <div class="font-bold text-indigo-700" id="depTotalTxt"><?= $fmtMoney($deposito['monto'] ?? 0) ?></div>
        </div>
        <div class="rounded-xl p-3 bg-rose-50/70">
          <div class="h-title">Restante</div>
          <div class="font-bold text-rose-700" id="depRestaTxt"><?= $fmtMoney($deposito['resta'] ?? 0) ?></div>
        </div>
      </div>

      <div class="mt-4">
        <div class="text-sm text-slate-700 mb-2">Confirmaciones</div>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-2">
          <?php foreach ($asesores as $a): $aid = (int)$a['id']; $val = $deposito['confirmaciones'][$aid] ?? ''; ?>
            <div class="flex items-center gap-2 card px-3 py-2">
              <div class="w-44 text-sm font-medium text-slate-900"><?= Html::encode($a['nombre']) ?></div>
              <select class="border border-slate-300 rounded-xl px-2 py-1 text-sm text-slate-900 bg-white js-conf focus:outline-none focus:ring-2 focus:ring-indigo-200"
                      data-asesor-id="<?= $aid ?>">
                <option value="" <?= $val===''?'selected':'' ?>>--</option>
                <option value="si" <?= $val==='si'?'selected':'' ?>>Sí</option>
                <option value="no" <?= $val==='no'?'selected':'' ?>>No</option>
              </select>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="mt-5 flex items-center gap-3">
        <button class="btn btn-primary" onclick="saveDeposito()">
          <svg class="icon" viewBox="0 0 24 24" fill="none"><path d="M5 12l4 4L19 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
          Guardar
        </button>
        <div id="depMsg" class="text-sm text-slate-800"></div>
      </div>
    </div>

  </div>
</div>

<?php
$saveUrl  = Url::to(['retorno/save-deposito']);
$campUrl  = Url::to(['retorno/get-campanas-activas']);
$indexUrl = Url::to(['retorno/index']);
$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->getCsrfToken();

$this->registerJs(<<<JS
function formatCurrency(n) {
  try { return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(parseFloat(n||0)); }
  catch(e) { return '$' + (parseFloat(n||0).toFixed(2)); }
}

function changeMonth() {
  const m = document.getElementById('monthPicker').value;
  if (!m) return;
  const url = new URL('$indexUrl', window.location.origin);
  url.searchParams.set('month', m);
  window.location = url.toString();
}

function updateDepositPreview() {
  const monto = parseFloat(document.getElementById('depMonto').value || '0');
  const resta = parseFloat(document.getElementById('depResta').value || '0');
  document.getElementById('depTotalTxt').textContent = formatCurrency(monto);
  document.getElementById('depRestaTxt').textContent = formatCurrency(resta);
}
['depMonto','depResta'].forEach(id => {
  const el = document.getElementById(id);
  if (el) el.addEventListener('input', updateDepositPreview);
});

function saveDeposito() {
  const data = new URLSearchParams();
  data.append('month', document.getElementById('monthPicker').value);
  data.append('fecha', document.getElementById('depFecha').value);
  data.append('monto', document.getElementById('depMonto').value || '0');
  data.append('resta', document.getElementById('depResta').value || '0');
  document.querySelectorAll('.js-conf').forEach(sel => {
    data.append('confirmaciones['+sel.dataset.asesorId+']', sel.value);
  });
  data.append('$csrfParam', '$csrfToken');

  fetch('$saveUrl', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded;charset=UTF-8'},
    body: data.toString()
  }).then(r => r.json()).then(res => {
    const el = document.getElementById('depMsg');
    el.textContent = res && res.message ? res.message : 'Guardado';
    el.className = 'text-sm ' + (res && res.success ? 'text-green-600' : 'text-red-600');
    setTimeout(()=>{ el.textContent=''; }, 2500);
  }).catch(() => {
    const el = document.getElementById('depMsg');
    el.textContent = 'Error de conexión';
    el.className = 'text-sm text-red-600';
    setTimeout(()=>{ el.textContent=''; }, 2500);
  });
}

// Cargar campañas activas por asesor para la sección Extra
function loadCampanasActivas() {
  document.querySelectorAll('.js-campanas').forEach(sel => {
    const asesorId = sel.dataset.asesorId;
    const url = new URL('$campUrl', window.location.origin);
    url.searchParams.set('asesorId', asesorId);
    fetch(url.toString())
      .then(r => r.json())
      .then(res => {
        const items = (res && res.items) ? res.items : [];
        sel.innerHTML = '<option value=\"\">-- Campañas activas --</option>';
        items.forEach(it => {
          const opt = document.createElement('option');
          opt.value = it.id;
          opt.textContent = it.text;
          sel.appendChild(opt);
        });
      })
      .catch(() => {});
  });
}
document.addEventListener('DOMContentLoaded', loadCampanasActivas);
JS);