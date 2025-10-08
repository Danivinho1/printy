<?php
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * Requiere:
 * - int    $year
 * - array  $months   // [ ['num'=>1..12,'label','start','end','total'], ... ]
 * - float  $maxTotal
 * - int    $maxMonth
 * - string $today
 */

// Ensure $maxMonth is assigned if not set
if (!isset($maxMonth) || !is_int($maxMonth)) {
    $maxMonth = !empty($months) ? (int)$months[0]['num'] : 1;
}

$this->title = 'Comparativas mensuales';
$this->params['breadcrumbs'][] = ['label' => 'Marketing', 'url' => ['campanas/index']];
$this->params['breadcrumbs'][] = $this->title;

$fmtMoney = fn($n) => Yii::$app->formatter->asCurrency((float)$n, 'MXN');

$this->registerCss(<<<CSS
.vm-bg {
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

/* Tabs navegación */
.navtab { display:inline-flex; align-items:center; padding:.5rem .75rem; border-radius:.75rem; border:1px solid #e5e7eb; color:#334155; transition:background-color .15s; }
.navtab:hover { background:#f8fafc; }
.navtab-active { background-image:linear-gradient(90deg,#4f46e5,#7c3aed); color:#fff; border-color:transparent; box-shadow:0 8px 24px rgba(79,70,229,.25); }

/* Tabla y barras */
.table-lite th { font-size:.8rem; letter-spacing:.02em; text-transform:uppercase; color:#64748b; }
.table-lite td { color:#0f172a; }
.progress-wrap { background:#eef2f7; border-radius:9999px; overflow:hidden; height:10px; }
.progress-bar { height:100%; border-radius:9999px; background-image:linear-gradient(90deg,#4f46e5,#7c3aed); }
CSS);
?>

<div class="vm-bg min-h-screen text-slate-900">
  <div class="mx-auto max-w-6xl px-4 py-6 space-y-6">

    <!-- Tabs + Selector de año -->
    <div class="flex items-center justify-between">
      <div class="flex gap-2 text-sm">
        <a class="navtab" href="<?= Url::to(['campanas/index']) ?>">Campañas</a>
        <a class="navtab" href="<?= Url::to(['retorno/index']) ?>">Retorno</a>
        <a class="navtab navtab-active" href="<?= Url::to(['ventas-mensuales/index']) ?>">Comparativas</a>
        <a class="navtab" href="<?= Url::to(['productos-vendidos/index']) ?>">Productos</a>
      </div>
      <div class="flex items-center gap-2">
        <label class="text-sm text-slate-700">Año</label>
        <select id="yearSelect" class="border border-slate-300 rounded-xl px-3 py-2 text-sm" onchange="changeYear()">
          <?php
          $thisYear = (int)date('Y');
          for ($y=$thisYear+1; $y >= $thisYear-5; $y--): ?>
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
        <h1 class="text-xl font-semibold">Comparativas mensuales</h1>
        <div class="text-sm text-slate-600">Fecha actual: <span class="font-medium"><?= Html::encode($today) ?></span></div>
      </div>
      <div class="card p-3">
        <div class="flex items-center justify-between gap-6">
          <div class="text-sm text-slate-700">
            Mes más alto: <span class="font-semibold">
              <?php
                $top = array_values(array_filter($months, fn($m) => (int)$m['num'] === (int)$maxMonth));
                echo Html::encode($top[0]['label'] ?? '—');
              ?>
            </span>
          </div>
          <div class="text-2xl font-extrabold text-slate-900"><?= $fmtMoney($maxTotal) ?></div>
        </div>
      </div>
    </div>

    <!-- Tabla -->
    <div class="card">
      <div class="overflow-x-auto">
        <table class="min-w-full table-lite">
          <thead>
            <tr>
              <th class="text-left px-4 py-3">Mes</th>
              <th class="text-left px-4 py-3">Rango</th>
              <th class="text-right px-4 py-3">Total vendido</th>
              <th class="text-left px-4 py-3">Nivel</th>
              <th class="text-left px-4 py-3">Etiqueta</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($months as $m):
              $isTop = ((int)$m['num'] === (int)$maxMonth) && $maxTotal > 0;
              $pct = $maxTotal > 0 ? max(2, round(($m['total'] / $maxTotal) * 100)) : 0;
            ?>
              <tr class="border-t border-slate-100">
                <td class="px-4 py-3 font-medium"><?= Html::encode($m['label']) ?></td>
                <td class="px-4 py-3 text-slate-700"><?= Html::encode($m['start']) ?> · <?= Html::encode($m['end']) ?></td>
                <td class="px-4 py-3 text-right font-semibold"><?= $fmtMoney($m['total']) ?></td>
                <td class="px-4 py-3" style="min-width:180px;">
                  <div class="progress-wrap">
                    <div class="progress-bar" style="width: <?= $maxTotal>0 ? $pct : 0 ?>%"></div>
                  </div>
                </td>
                <td class="px-4 py-3">
                  <?php if ($isTop): ?>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">TOP</span>
                  <?php else: ?>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700"><?= $pct ?>%</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>

<?php
$indexUrl = Url::to(['ventas-mensuales/index']);
$this->registerJs(<<<JS
function changeYear() {
  var y = document.getElementById('yearSelect').value;
  if (!y) return;
  const url = new URL('$indexUrl', window.location.origin);
  url.searchParams.set('year', y);
  window.location = url.toString();
}
JS);