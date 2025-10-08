<?php
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * Requiere:
 * - int   $year
 * - array $rows            // [ ['producto','m'=>[1..12=>['u','m']], 'total_u','total_m'], ... ]
 * - array $totalsByMonth   // [1..12 => ['u','m']]
 * - float $grandUnits
 * - float $grandMoney
 * - string $today
 * - array $months          // [1=>'Ene', ...]
 */

$this->title = 'Productos vendidos';
$this->params['breadcrumbs'][] = ['label' => 'Marketing', 'url' => ['campanas/index']];
$this->params['breadcrumbs'][] = $this->title;

$fmtMoney = fn($n) => Yii::$app->formatter->asCurrency((float)$n, 'MXN');
$fmtInt   = fn($n) => number_format((float)$n, 0, '.', ',');

$this->registerCss(<<<CSS
.pv-bg {
  background-image:
    radial-gradient(70% 40% at 10% -10%, rgba(99,102,241,0.08) 0%, rgba(99,102,241,0) 60%),
    radial-gradient(70% 40% at 110% 0%, rgba(236,72,153,0.08) 0%, rgba(236,72,153,0) 60%),
    linear-gradient(#f1f5f9 1px, transparent 1px),
    linear-gradient(90deg, #f1f5f9 1px, transparent 1px);
  background-size: auto, auto, 24px 24px, 24px 24px;
  background-position: center, center, -1px -1px, -1px -1px;
  background-color: #fff;
}
.card { border-radius: 1rem; border:1px solid #e5e7eb; background:#fff; box-shadow:0 8px 20px rgba(2,6,23,.04); }
.badge { display:inline-flex; align-items:center; padding:2px 8px; border-radius:9999px; font-size:12px; font-weight:600; }

/* Tabs navegación */
.navtab { display:inline-flex; align-items:center; padding:.5rem .75rem; border-radius:.75rem; border:1px solid #e5e7eb; color:#334155; transition:background-color .15s; }
.navtab:hover { background:#f8fafc; }
.navtab-active { background-image:linear-gradient(90deg,#4f46e5,#7c3aed); color:#fff; border-color:transparent; box-shadow:0 8px 24px rgba(79,70,229,.25); }

.table-wrap { overflow-x: auto; }
.table { min-width: 100%; border-collapse: separate; border-spacing: 0; }
.table thead th { position: sticky; top: 0; background: #ffffff; z-index: 2; }
.table th, .table td { padding: .6rem .75rem; border-bottom: 1px solid #eef2f7; white-space: nowrap; }
.table th { font-size:.8rem; letter-spacing:.02em; text-transform:uppercase; color:#64748b; }
.table td { color:#0f172a; }
.cell-month { font-variant-numeric: tabular-nums; }
.cell-month small { display:block; color:#334155; font-size: .7rem; }
.total-row { background:#fafafa; font-weight:600; }
.sticky-left { position: sticky; left: 0; background: #fff; z-index: 3; }
.sticky-right { position: sticky; right: 0; background: #fff; z-index: 3; }
.sum-cards .card { display:flex; align-items:center; justify-content:space-between; gap: 1rem; }
CSS);
?>

<div class="pv-bg min-h-screen text-slate-900">
  <div class="mx-auto max-w-7xl px-4 py-6 space-y-6">

    <!-- Tabs + Selector de año -->
    <div class="flex items-center justify-between">
      <div class="flex gap-2 text-sm">
        <a class="navtab" href="<?= Url::to(['campanas/index']) ?>">Campañas</a>
        <a class="navtab" href="<?= Url::to(['retorno/index']) ?>">Retorno</a>
        <a class="navtab" href="<?= Url::to(['ventas-mensuales/index']) ?>">Comparativas</a>
        <a class="navtab navtab-active" href="<?= Url::to(['productos-vendidos/index']) ?>">Productos</a>
      </div>
      <div class="flex items-center gap-2">
        <label class="text-sm text-slate-700">Año</label>
        <select id="yearSelect" class="border border-slate-300 rounded-xl px-3 py-2 text-sm" onchange="changeYear()">
          <?php $thisYear = (int)date('Y'); for ($y=$thisYear+1; $y >= $thisYear-5; $y--): ?>
            <option value="<?= $y ?>" <?= $y===$year?'selected':'' ?>><?= $y ?></option>
          <?php endfor; ?>
        </select>
        <button class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-gray-300 hover:bg-gray-50 text-sm" onclick="changeYear()">
          Ver
        </button>
      </div>
    </div>

    <!-- Encabezado -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-xl font-semibold">Productos vendidos</h1>
        <div class="text-sm text-slate-600">Fecha actual: <span class="font-medium"><?= Html::encode($today) ?></span></div>
      </div>
    </div>

    <!-- Cards de sumatorias -->
    <div class="sum-cards grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="card p-4">
        <div class="text-sm text-slate-600">Unidades totales (año)</div>
        <div class="text-2xl font-extrabold text-slate-900"><?= $fmtInt($grandUnits) ?></div>
      </div>
      <div class="card p-4">
        <div class="text-sm text-slate-600">Monto total (año)</div>
        <div class="text-2xl font-extrabold text-slate-900"><?= $fmtMoney($grandMoney) ?></div>
      </div>
    </div>

    <!-- Tabla -->
    <div class="card">
      <div class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th class="sticky-left z-10">Producto</th>
              <?php foreach ($months as $i => $ml): ?>
                <th><?= Html::encode($ml) ?></th>
              <?php endforeach; ?>
              <th class="sticky-right">Tot. Pzs</th>
              <th class="sticky-right">Tot. $</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $row): ?>
              <tr>
                <td class="sticky-left font-medium"><?= Html::encode($row['producto']) ?></td>
                <?php foreach ($months as $i => $ml): $cell = $row['m'][$i] ?? ['u'=>0,'m'=>0]; ?>
                  <td class="cell-month">
                    <?= $fmtInt($cell['u']) ?>
                    <small><?= $fmtMoney($cell['m']) ?></small>
                  </td>
                <?php endforeach; ?>
                <td class="sticky-right font-semibold"><?= $fmtInt($row['total_u']) ?></td>
                <td class="sticky-right font-semibold"><?= $fmtMoney($row['total_m']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr class="total-row">
              <td class="sticky-left">Totales</td>
              <?php foreach ($months as $i => $ml): $t = $totalsByMonth[$i] ?? ['u'=>0,'m'=>0]; ?>
                <td class="cell-month">
                  <?= $fmtInt($t['u']) ?>
                  <small><?= $fmtMoney($t['m']) ?></small>
                </td>
              <?php endforeach; ?>
              <td class="sticky-right"><?= $fmtInt($grandUnits) ?></td>
              <td class="sticky-right"><?= $fmtMoney($grandMoney) ?></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

  </div>
</div>

<?php
$indexUrl = Url::to(['productos-vendidos/index']);
$this->registerJs(<<<JS
function changeYear() {
  var y = document.getElementById('yearSelect').value;
  if (!y) return;
  const url = new URL('$indexUrl', window.location.origin);
  url.searchParams.set('year', y);
  window.location = url.toString();
}
JS);