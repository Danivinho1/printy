<?php
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * Requiere (del controlador):
 * - int    $year
 * - array  $months   // [ ['num'=>1..12,'label'=>string,'start'=>date,'end'=>date,'total'=>float], ... ] en orden cronológico
 * - float  $maxTotal
 * - int    $maxMonth
 * - string $today
 *
 * Campos opcionales por mes (si el controlador los provee se muestran, si no, se omiten):
 * - 'orders'  => int   // número de ventas del mes
 * - 'avg'     => float // ticket promedio del mes
 * - 'nivel'   => string
 */

// Ensure $maxMonth is assigned if not set
if (!isset($maxMonth) || !is_int($maxMonth)) {
    $maxMonth = !empty($months) ? (int)$months[0]['num'] : 1;
}

$this->title = 'Comparativas mensuales';
$this->params['breadcrumbs'][] = ['label' => 'Marketing', 'url' => ['campanas/index']];
$this->params['breadcrumbs'][] = $this->title;

$fmtMoney = fn($n) => Yii::$app->formatter->asCurrency((float)$n, 'MXN');
$fmtPct   = fn($n) => number_format((float)$n, 2) . '%';

// Derivados para analítica (desde $months)
$labels = [];
$totals = [];
$ranges = [];
$orders = [];
$avgs   = [];
$niveles= [];

foreach ($months as $m) {
    $labels[] = (string)($m['label'] ?? '');
    $ranges[] = (string)($m['start'] ?? '') . ' · ' . (string)($m['end'] ?? '');
    $totals[] = (float)($m['total'] ?? 0);
    $orders[] = isset($m['orders']) ? (int)$m['orders'] : null;
    $avgs[]   = isset($m['avg'])    ? (float)$m['avg']    : null;
    $niveles[]= isset($m['nivel'])  ? (string)$m['nivel'] : null;
}

// Métricas anuales
$monthsCountWithData = count($months);
$totalYear = array_sum($totals);
$avgYear   = $monthsCountWithData > 0 ? ($totalYear / $monthsCountWithData) : 0.0;

// Mejor y peor mes (considera todos, incluso 0)
$maxIdx = $maxMonth ? array_search($maxMonth, array_column($months, 'num'), true) : null;
if ($maxIdx === false || $maxIdx === null) {
    $maxIdx = (int)array_keys($totals, max($totals))[0] ?? 0;
}
$maxTotalCalc = (float)($totals[$maxIdx] ?? 0);
$maxLabel     = (string)($labels[$maxIdx] ?? '—');

$minIdx = (int)array_keys($totals, min($totals))[0] ?? 0;
$minTotalCalc = (float)($totals[$minIdx] ?? 0);
$minLabel     = (string)($labels[$minIdx] ?? '—');

// Serie acumulada y MoM
$cumulative = [];
$momDelta   = [];
$momPct     = [];
$running = 0.0;
for ($i=0; $i<$monthsCountWithData; $i++) {
    $cur = (float)$totals[$i];
    $prev = $i>0 ? (float)$totals[$i-1] : 0.0;
    $running += $cur;
    $cumulative[] = $running;
    $momDelta[] = $cur - $prev;
    $momPct[]   = ($prev > 0) ? (($cur - $prev) / $prev) * 100 : null; // null si no hay base
}

// Último mes con dato para tarjetas
$lastIdx = $monthsCountWithData - 1;
$lastMonthLabel = $labels[$lastIdx] ?? '—';
$lastTotal      = $totals[$lastIdx] ?? 0;
$lastMomDelta   = $momDelta[$lastIdx] ?? 0;
$lastMomPct     = $momPct[$lastIdx] ?? null;

// Contribución por mes
$contrib = [];
if ($totalYear > 0) {
    foreach ($totals as $t) { $contrib[] = ($t / $totalYear) * 100; }
} else {
    foreach ($totals as $_) { $contrib[] = 0.0; }
}

// Ranking (1 = mayor)
$sortedTotals = $totals;
arsort($sortedTotals);
$rankMap = [];
$rank = 1;
foreach ($sortedTotals as $i => $_val) {
    $rankMap[$i] = $rank++;
}

// Mostrar columnas opcionales si hay al menos un valor no-nulo
$showOrders = array_filter($orders, fn($v) => $v !== null) ? true : false;
$showAvg    = array_filter($avgs,   fn($v) => $v !== null) ? true : false;
$showNivel  = array_filter($niveles,fn($v) => !empty($v))  ? true : false;

// Inyectar datos a JS para Chart.js
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js', ['position'=>\yii\web\View::POS_END]);
$this->registerJs(
    'window.__monthsLabels = ' . json_encode($labels, JSON_UNESCAPED_UNICODE) . ';
     window.__monthsTotals = ' . json_encode($totals, JSON_NUMERIC_CHECK) . ';
     window.__monthsCumuls = ' . json_encode($cumulative, JSON_NUMERIC_CHECK) . ';
     window.__monthsMoM    = ' . json_encode(array_map(fn($v)=> is_null($v) ? null : round($v,2), $momPct)) . ';
     window.__yearLabel    = ' . json_encode($year) . ';',
    \yii\web\View::POS_HEAD
);

// Estilos (morado)
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
.navtab { display:inline-flex; align-items:center; padding:.5rem .75rem; border-radius:.75rem; border:1px solid #e5e7eb; color:#334155; transition:background-color .15s; text-decoration:none; }
.navtab:hover { background:#f8fafc; }
.navtab-active { background-image:linear-gradient(90deg,#4f46e5,#7c3aed); color:#fff; border-color:transparent; box-shadow:0 8px 24px rgba(79,70,229,.25); }

/* Tabla y barras */
.table-lite th { font-size:.8rem; letter-spacing:.02em; text-transform:uppercase; color:#64748b; }
.table-lite td { color:#0f172a; }
.progress-wrap { background:#eef2f7; border-radius:9999px; overflow:hidden; height:10px; }
.progress-bar { height:100%; border-radius:9999px; background-image:linear-gradient(90deg,#4f46e5,#7c3aed); }

/* KPI cards */
.kpi .label { font-size:.8rem; color:#64748b; }
.kpi .value { font-size:1.35rem; font-weight:800; color:#0f172a; }
.kpi .delta-up { color:#16a34a; font-weight:700; }
.kpi .delta-down { color:#dc2626; font-weight:700; }
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

    <!-- Encabezado + KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="card p-4">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-xl font-semibold mb-1">Comparativas mensuales</h1>
            <div class="text-sm text-slate-600">Fecha actual: <span class="font-medium"><?= Html::encode($today) ?></span></div>
          </div>
          <div class="text-right">
            <div class="text-sm text-slate-700">Mejor mes</div>
            <div class="text-2xl font-extrabold text-slate-900"><?= Html::encode($maxLabel) ?> · <?= $fmtMoney($maxTotalCalc) ?></div>
          </div>
        </div>
      </div>

      <div class="card p-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 kpi">
          <div>
            <div class="label">Total anual</div>
            <div class="value"><?= $fmtMoney($totalYear) ?></div>
          </div>
          <div>
            <div class="label">Promedio mensual</div>
            <div class="value"><?= $fmtMoney($avgYear) ?></div>
          </div>
          <div>
            <div class="label">Peor mes</div>
            <div class="value"><?= Html::encode($minLabel) ?> · <?= $fmtMoney($minTotalCalc) ?></div>
          </div>
          <div>
            <div class="label">Último mes (<?= Html::encode($lastMonthLabel) ?>) MoM</div>
            <div class="value">
              <?php if (!is_null($lastMomPct)): ?>
                <span class="<?= ($lastMomPct>=0 ? 'delta-up' : 'delta-down') ?>">
                  <?= ($lastMomPct>=0 ? '+' : '') . $fmtPct($lastMomPct) ?>
                </span>
                <span class="text-slate-500 text-sm"> (<?= ($lastMomDelta>=0?'+':'') . $fmtMoney($lastMomDelta) ?>)</span>
              <?php else: ?>
                <span class="text-slate-500">s/d</span>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Gráficos: Barras (mensual) + Línea (acumulado) y Línea MoM% -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      <div class="card p-4 lg:col-span-2">
        <div class="text-sm text-slate-700 mb-2">Tendencia y acumulado <?= Html::encode((string)$year) ?></div>
        <canvas id="chartTotals" height="120"></canvas>
      </div>
      <div class="card p-4">
        <div class="text-sm text-slate-700 mb-2">% variación mes vs mes anterior</div>
        <canvas id="chartMoM" height="120"></canvas>
        <div class="text-xs text-slate-500 mt-2">Nota: el primer mes no tiene base para MoM.</div>
      </div>
    </div>

    <!-- Tabla detallada -->
    <div class="card">
      <div class="overflow-x-auto">
        <table class="min-w-full table-lite">
          <thead>
            <tr>
              <th class="text-left px-4 py-3">Mes</th>
              <th class="text-left px-4 py-3">Rango</th>
              <th class="text-right px-4 py-3">Total vendido</th>
              <th class="text-left px-4 py-3">Nivel</th>
              <?php if ($showOrders): ?><th class="text-right px-4 py-3"># Ventas</th><?php endif; ?>
              <?php if ($showAvg):    ?><th class="text-right px-4 py-3">Ticket prom.</th><?php endif; ?>
              <th class="text-left px-4 py-3">Contribución</th>
              <th class="text-right px-4 py-3">MoM Δ</th>
              <th class="text-right px-4 py-3">MoM %</th>
              <th class="text-right px-4 py-3">Acumulado</th>
              <th class="text-left px-4 py-3">Ranking</th>
            </tr>
          </thead>
          <tbody>
            <?php for ($i=0; $i<$monthsCountWithData; $i++):
              $isTop = ($i === $maxIdx) && $maxTotalCalc > 0;
              $pctBar = ($maxTotalCalc > 0) ? max(2, round(($totals[$i] / $maxTotalCalc) * 100)) : 0;
              $momPctCell = $momPct[$i];
              $rankCell = $rankMap[$i] ?? null;
            ?>
              <tr class="border-t border-slate-100">
                <td class="px-4 py-3 font-medium"><?= Html::encode($labels[$i]) ?></td>
                <td class="px-4 py-3 text-slate-700"><?= Html::encode($ranges[$i]) ?></td>
                <td class="px-4 py-3 text-right font-semibold"><?= $fmtMoney($totals[$i]) ?></td>
                <td class="px-4 py-3">
                  <?php if ($showNivel && !empty($niveles[$i])): ?>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                      <?= Html::encode($niveles[$i]) ?>
                    </span>
                  <?php else: ?>
                    <div class="progress-wrap"><div class="progress-bar" style="width: <?= $pctBar ?>%"></div></div>
                  <?php endif; ?>
                </td>
                <?php if ($showOrders): ?>
                  <td class="px-4 py-3 text-right"><?= $orders[$i]===null ? '—' : number_format((int)$orders[$i]) ?></td>
                <?php endif; ?>
                <?php if ($showAvg): ?>
                  <td class="px-4 py-3 text-right"><?= $avgs[$i]===null ? '—' : $fmtMoney($avgs[$i]) ?></td>
                <?php endif; ?>
                <td class="px-4 py-3">
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                    <?= $fmtPct($contrib[$i]) ?>
                  </span>
                </td>
                <td class="px-4 py-3 text-right <?= ($momDelta[$i]??0)>=0?'text-emerald-700':'text-red-600' ?>">
                  <?= ($momDelta[$i]>=0?'+':'') . $fmtMoney($momDelta[$i]) ?>
                </td>
                <td class="px-4 py-3 text-right <?= (is_null($momPctCell) ? '' : ($momPctCell>=0?'text-emerald-700':'text-red-600')) ?>">
                  <?= is_null($momPctCell) ? '—' : (($momPctCell>=0?'+':'') . $fmtPct($momPctCell)) ?>
                </td>
                <td class="px-4 py-3 text-right"><?= $fmtMoney($cumulative[$i]) ?></td>
                <td class="px-4 py-3">
                  <?=
                    $rankCell
                      ? '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold ' .
                        ($rankCell===1 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700') .
                        '">#'.$rankCell.($rankCell===1?' TOP':'').'</span>'
                      : '—';
                  ?>
                </td>
              </tr>
            <?php endfor; ?>
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

// Chart.js
(function(){
  var el1 = document.getElementById('chartTotals');
  if (el1) {
    var ctx1 = el1.getContext('2d');
    var grad = ctx1.createLinearGradient(0,0,0,220);
    grad.addColorStop(0,'rgba(99,102,241,0.35)');
    grad.addColorStop(1,'rgba(99,102,241,0.04)');

    new Chart(ctx1, {
      type: 'bar',
      data: {
        labels: window.__monthsLabels || [],
        datasets: [
          {
            type:'bar',
            label: 'Total mensual',
            data: window.__monthsTotals || [],
            backgroundColor: grad,
            borderColor: '#4f46e5',
            borderWidth: 1.5,
            yAxisID: 'y',
          },
          {
            type:'line',
            label: 'Acumulado',
            data: window.__monthsCumuls || [],
            borderColor: '#7c3aed',
            backgroundColor: 'transparent',
            borderWidth: 2,
            pointRadius: 3,
            tension: .3,
            yAxisID: 'y',
          }
        ]
      },
      options: {
        responsive: true,
        plugins: { legend: { display: true } },
        scales: {
          y: {
            position:'left',
            ticks: { callback: (v)=> '$' + Number(v).toLocaleString('es-MX') }
          },
          x: { grid: { display:false } }
        }
      }
    });
  }

  var el2 = document.getElementById('chartMoM');
  if (el2) {
    var ctx2 = el2.getContext('2d');
    new Chart(ctx2, {
      type: 'line',
      data: {
        labels: window.__monthsLabels || [],
        datasets: [{
          label: '% MoM',
          data: (window.__monthsMoM || []).map(x=> x===null ? null : Number(x.toFixed(2))),
          borderColor: '#ec4899',
          backgroundColor: 'rgba(236,72,153,.12)',
          borderWidth: 2,
          pointRadius: 3,
          tension: .35,
          spanGaps: true
        }]
      },
      options: {
        plugins: { legend: { display: false } },
        scales: {
          y: {
            ticks: { callback: (v)=> Number(v).toFixed(0) + '%' }
          },
          x: { grid: { display:false } }
        }
      }
    });
  }
})();
JS);