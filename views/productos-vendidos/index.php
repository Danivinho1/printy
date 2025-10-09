<?php
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * Requiere (del controlador):
 * - int    $year
 * - array  $rows            // [ ['producto'=>string,'m'=>[1..12=>['u'=>int,'m'=>float]], 'total_u'=>int,'total_m'=>float], ... ]
 * - array  $totalsByMonth   // [1..12 => ['u'=>int,'m'=>float]]
 * - float  $grandUnits
 * - float  $grandMoney
 * - string $today
 * - array  $months          // [1=>'Ene', 2=>'Feb', ... 12=>'Dic']
 */

$this->title = 'Productos vendidos';
$this->params['breadcrumbs'][] = ['label' => 'Marketing', 'url' => ['campanas/index']];
$this->params['breadcrumbs'][] = $this->title;

$fmtMoney = fn($n) => Yii::$app->formatter->asCurrency((float)$n, 'MXN');
$fmtInt   = fn($n) => number_format((float)$n, 0, '.', ',');

// Derivados para analítica/encabezados
$monthsIdx    = array_keys($months);              // [1..12]
$monthsLabels = array_values($months);            // ['Ene', ...]
$moneyByMonth = [];
$unitsByMonth = [];
$bestMonthIdx = null;
$bestMonthVal = -INF;
$worstMonthIdx = null;
$worstMonthVal = INF;

foreach ($monthsIdx as $mi) {
    $m  = (float)($totalsByMonth[$mi]['m'] ?? 0);
    $u  = (int)  ($totalsByMonth[$mi]['u'] ?? 0);
    $moneyByMonth[] = $m;
    $unitsByMonth[] = $u;

    if ($m > $bestMonthVal) { $bestMonthVal = $m; $bestMonthIdx = $mi; }
    if ($m < $worstMonthVal) { $worstMonthVal = $m; $worstMonthIdx = $mi; }
}

// Ranking por monto anual (1 = más vendido en $)
$rankByProduct = [];
$_tmp = $rows;
usort($_tmp, fn($a,$b) => ($b['total_m'] <=> $a['total_m']));
$_r = 1;
foreach ($_tmp as $_row) {
    $pname = (string)$_row['producto'];
    if (!isset($rankByProduct[$pname])) $rankByProduct[$pname] = $_r++;
}

// Inyectar dataset para JS (gráficas/CSV)
$rowsJs = [];
foreach ($rows as $r) {
    $monthlyU = [];
    $monthlyM = [];
    foreach ($monthsIdx as $mi) {
        $cell = $r['m'][$mi] ?? ['u'=>0,'m'=>0];
        $monthlyU[] = (int)($cell['u'] ?? 0);
        $monthlyM[] = (float)($cell['m'] ?? 0);
    }
    $totU = (int)$r['total_u'];
    $totM = (float)$r['total_m'];
    $avg  = $totU > 0 ? $totM / $totU : 0.0;
    $share= $grandMoney > 0 ? ($totM / $grandMoney) * 100.0 : 0.0;

    $rowsJs[] = [
        'producto'  => (string)$r['producto'],
        'monthlyU'  => $monthlyU,
        'monthlyM'  => $monthlyM,
        'total_u'   => $totU,
        'total_m'   => round($totM, 2),
        'avg'       => round($avg, 2),
        'share'     => round($share, 4),
        'rank'      => (int)($rankByProduct[$r['producto']] ?? null),
    ];
}

$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js', ['position'=>\yii\web\View::POS_END]);
$this->registerJs(
    'window.__pv = ' . json_encode([
        'year'         => $year,
        'monthsIdx'    => $monthsIdx,
        'monthsLabels' => $monthsLabels,
        'moneyByMonth' => $moneyByMonth,
        'unitsByMonth' => $unitsByMonth,
        'grandUnits'   => (float)$grandUnits,
        'grandMoney'   => (float)$grandMoney,
        'rows'         => $rowsJs,
    ], JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK) . ';',
    \yii\web\View::POS_HEAD
);

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
.navtab { display:inline-flex; align-items:center; padding:.5rem .75rem; border-radius:.75rem; border:1px solid #e5e7eb; color:#334155; transition:background-color .15s; text-decoration:none; }
.navtab:hover { background:#f8fafc; }
.navtab-active { background-image:linear-gradient(90deg,#4f46e5,#7c3aed); color:#fff; border-color:transparent; box-shadow:0 8px 24px rgba(79,70,229,.25); }

/* Controles */
.toolbar { gap:.5rem; display:flex; align-items:center; flex-wrap:wrap; }
.toolbar input[type="search"]{ border:1px solid #e5e7eb; border-radius:.75rem; padding:.45rem .75rem; }
.toolbar select{ border:1px solid #e5e7eb; border-radius:.75rem; padding:.45rem .75rem; }

/* Tabla */
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

/* Indicadores */
.kpi .label { font-size:.8rem; color:#64748b; }
.kpi .value { font-size:1.35rem; font-weight:800; color:#0f172a; }
.kpi .sub { font-size:.85rem; color:#475569; }
.progress-wrap { background:#eef2f7; border-radius:9999px; overflow:hidden; height:10px; }
.progress-bar  { height:100%; border-radius:9999px; background-image:linear-gradient(90deg,#4f46e5,#7c3aed); }

/* Helpers */
.text-emerald { color:#059669; }
.text-rose    { color:#e11d48; }
.btn { display:inline-flex; align-items:center; gap:.4rem; border:1px solid #e5e7eb; border-radius:.75rem; padding:.45rem .7rem; background:#fff; color:#111827; }
.btn:hover { background:#f8fafc; }
.btn-primary { background:linear-gradient(90deg,#4f46e5,#7c3aed); border-color:transparent; color:#fff; }
.btn-primary:hover { filter:brightness(0.98); }
CSS);
?>

<div class="pv-bg min-h-screen text-slate-900">
  <div class="mx-auto max-w-7xl px-4 py-6 space-y-6">

    <!-- Tabs + Selector de año -->
    <div class="flex items-center justify-between">
      <div class="flex gap-2 text-sm">
        <?= Html::a('Campañas', ['campanas/index'], ['class'=>'navtab']) ?>
        <?= Html::a('Retorno', ['retorno/index'], ['class'=>'navtab']) ?>
        <?= Html::a('Comparativas', ['ventas-mensuales/index'], ['class'=>'navtab']) ?>
        <?= Html::a('Productos', ['productos-vendidos/index'], ['class'=>'navtab navtab-active']) ?>
      </div>
      <div class="toolbar">
        <label class="text-sm text-slate-700">Año</label>
        <select id="yearSelect" class="text-sm" onchange="changeYear()">
          <?php $thisYear = (int)date('Y'); for ($y=$thisYear+1; $y >= $thisYear-5; $y--): ?>
            <option value="<?= $y ?>" <?= $y===$year?'selected':'' ?>><?= $y ?></option>
          <?php endfor; ?>
        </select>
        <button class="btn" onclick="changeYear()">Ver</button>
        <div class="vr" style="width:1px;height:22px;background:#e5e7eb;margin:0 .5rem;"></div>
        <input id="filterBox" type="search" placeholder="Buscar producto..." class="text-sm" />
        <select id="sortSelect" class="text-sm">
          <option value="money_desc">Ordenar: Monto anual ↓</option>
          <option value="money_asc">Ordenar: Monto anual ↑</option>
          <option value="units_desc">Ordenar: Unidades ↓</option>
          <option value="units_asc">Ordenar: Unidades ↑</option>
          <option value="name_asc">Ordenar: Producto A–Z</option>
          <option value="rank_asc">Ordenar: Ranking $ (1→n)</option>
        </select>
        <button id="btnExportCsv" class="btn">Exportar CSV</button>
      </div>
    </div>

    <!-- Encabezado + KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="card p-4">
        <div class="flex items-center justify-between kpi">
          <div>
            <div class="label">Productos vendidos · <?= Html::encode((string)$year) ?></div>
            <div class="value"><?= $fmtMoney($grandMoney) ?> · <?= $fmtInt($grandUnits) ?> pzs</div>
            <div class="sub">
              Ticket promedio:
              <strong><?= $fmtMoney($grandUnits > 0 ? ($grandMoney / max(1,$grandUnits)) : 0) ?></strong>
            </div>
          </div>
          <div class="text-right">
            <div class="label">Mejor mes (importe)</div>
            <div class="value"><?= Html::encode($months[$bestMonthIdx] ?? '—') ?></div>
            <div class="sub"><?= $fmtMoney($bestMonthVal) ?></div>
          </div>
        </div>
      </div>

      <div class="card p-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 kpi">
          <div>
            <div class="label">Mes más bajo</div>
            <div class="value"><?= Html::encode($months[$worstMonthIdx] ?? '—') ?></div>
            <div class="sub"><?= $fmtMoney($worstMonthVal) ?></div>
          </div>
          <div>
            <div class="label"># de productos</div>
            <div class="value"><?= $fmtInt(count($rows)) ?></div>
          </div>
          <div>
            <div class="label">Meses con ventas</div>
            <div class="value">
              <?php
                $monthsActive = 0;
                foreach ($moneyByMonth as $mv) if ($mv > 0) $monthsActive++;
                echo $fmtInt($monthsActive);
              ?>
            </div>
          </div>
          <div>
            <div class="label">Top share (producto)</div>
            <?php
              $topRow = !empty($_tmp[0]) ? $_tmp[0] : null;
              $topName = $topRow['producto'] ?? '—';
              $topShare = ($grandMoney>0 && $topRow) ? ($topRow['total_m']/$grandMoney*100) : 0;
            ?>
            <div class="value"><?= Html::encode($topName) ?></div>
            <div class="sub"><?= number_format($topShare, 2) ?>% del total</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Gráficas -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
      <div class="card p-4">
        <div class="text-sm text-slate-700 mb-2">Tendencia mensual (importe)</div>
        <canvas id="chartMoney" height="120"></canvas>
      </div>
      <div class="card p-4">
        <div class="text-sm text-slate-700 mb-2">Tendencia mensual (unidades)</div>
        <canvas id="chartUnits" height="120"></canvas>
      </div>
    </div>

    <!-- Tabla -->
    <div class="card">
      <div class="table-wrap">
        <table class="table" id="pv-table">
          <thead>
            <tr>
              <th class="sticky-left z-10">Producto</th>
              <?php foreach ($months as $i => $ml): ?>
                <th><?= Html::encode($ml) ?></th>
              <?php endforeach; ?>
              <th class="sticky-right">Tot. Pzs</th>
              <th class="sticky-right">Tot. $</th>
              <th class="sticky-right">Ticket prom.</th>
              <th class="sticky-right">% del año</th>
              <th class="sticky-right">Ranking $</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $row):
              $avg   = ($row['total_u'] ?? 0) > 0 ? ((float)$row['total_m'] / (int)$row['total_u']) : 0.0;
              $share = ($grandMoney ?? 0) > 0 ? ((float)$row['total_m'] / (float)$grandMoney) * 100.0 : 0.0;
              $rank  = $rankByProduct[$row['producto']] ?? null;
            ?>
              <tr
                data-producto="<?= Html::encode($row['producto']) ?>"
                data-total-u="<?= (int)$row['total_u'] ?>"
                data-total-m="<?= (float)$row['total_m'] ?>"
                data-rank="<?= (int)$rank ?>"
              >
                <td class="sticky-left font-medium"><?= Html::encode($row['producto']) ?></td>
                <?php foreach ($months as $i => $ml): $cell = $row['m'][$i] ?? ['u'=>0,'m'=>0]; ?>
                  <td class="cell-month">
                    <?= $fmtInt($cell['u']) ?>
                    <small><?= $fmtMoney($cell['m']) ?></small>
                  </td>
                <?php endforeach; ?>
                <td class="sticky-right font-semibold"><?= $fmtInt($row['total_u']) ?></td>
                <td class="sticky-right font-semibold"><?= $fmtMoney($row['total_m']) ?></td>
                <td class="sticky-right"><?= $fmtMoney($avg) ?></td>
                <td class="sticky-right"><?= number_format($share, 2) ?>%</td>
                <td class="sticky-right">
                  <span class="badge" style="background:#eef2ff;color:#3730a3;">#<?= (int)$rank ?></span>
                </td>
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
              <td class="sticky-right"><?= $grandUnits>0 ? $fmtMoney($grandMoney/$grandUnits) : $fmtMoney(0) ?></td>
              <td class="sticky-right"><?= $grandMoney>0 ? '100.00%' : '0.00%' ?></td>
              <td class="sticky-right">—</td>
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

// ====== Chart.js ======
(function(){
  if (!window.__pv) return;

  // Importes
  var elM = document.getElementById('chartMoney');
  if (elM) {
    var ctxM = elM.getContext('2d');
    var gradM = ctxM.createLinearGradient(0,0,0,220);
    gradM.addColorStop(0,'rgba(99,102,241,0.35)');
    gradM.addColorStop(1,'rgba(99,102,241,0.04)');

    new Chart(ctxM, {
      type: 'bar',
      data: {
        labels: window.__pv.monthsLabels || [],
        datasets: [
          {
            label: 'Total $ por mes',
            data: window.__pv.moneyByMonth || [],
            backgroundColor: gradM,
            borderColor: '#4f46e5',
            borderWidth: 1.25
          }
        ]
      },
      options: {
        responsive:true,
        plugins:{ legend:{ display:false } },
        scales:{
          y: { ticks:{ callback:(v)=>'$'+Number(v).toLocaleString('es-MX') } },
          x: { grid:{ display:false } }
        }
      }
    });
  }

  // Unidades
  var elU = document.getElementById('chartUnits');
  if (elU) {
    var ctxU = elU.getContext('2d');
    new Chart(ctxU, {
      type: 'line',
      data: {
        labels: window.__pv.monthsLabels || [],
        datasets: [{
          label: 'Unidades por mes',
          data: window.__pv.unitsByMonth || [],
          borderColor: '#7c3aed',
          backgroundColor: 'rgba(124,58,237,.12)',
          borderWidth: 2,
          pointRadius: 3,
          tension: .35
        }]
      },
      options: {
        responsive:true,
        plugins:{ legend:{ display:false } },
        scales:{
          y: { ticks:{ callback:(v)=>Number(v).toLocaleString('es-MX') } },
          x: { grid:{ display:false } }
        }
      }
    });
  }
})();

// ====== Filtro, orden y CSV ======
(function(){
  const tbody = document.querySelector('#pv-table tbody');
  const rows  = Array.from(tbody.querySelectorAll('tr'));
  const filterBox = document.getElementById('filterBox');
  const sortSelect= document.getElementById('sortSelect');

  function normalize(s){ return (s||'').toString().toLowerCase().normalize('NFD').replace(/[\\u0300-\\u036f]/g,''); }

  function applyFilterAndSort(){
    const q = normalize(filterBox.value);
    let visibleRows = [];

    rows.forEach(tr=>{
      const name = normalize(tr.getAttribute('data-producto'));
      const match = q === '' || name.includes(q);
      tr.style.display = match ? '' : 'none';
      if (match) visibleRows.push(tr);
    });

    const mode = sortSelect.value;
    visibleRows.sort((a,b)=>{
      const ma = parseFloat(a.getAttribute('data-total-m'))||0;
      const mb = parseFloat(b.getAttribute('data-total-m'))||0;
      const ua = parseInt(a.getAttribute('data-total-u'))||0;
      const ub = parseInt(b.getAttribute('data-total-u'))||0;
      const ra = parseInt(a.getAttribute('data-rank'))||99999;
      const rb = parseInt(b.getAttribute('data-rank'))||99999;
      const na = (a.getAttribute('data-producto')||'').toLowerCase();
      const nb = (b.getAttribute('data-producto')||'').toLowerCase();

      switch(mode){
        case 'money_desc': return mb - ma;
        case 'money_asc':  return ma - mb;
        case 'units_desc': return ub - ua;
        case 'units_asc':  return ua - ub;
        case 'name_asc':   return na.localeCompare(nb);
        case 'rank_asc':   return ra - rb;
      }
      return 0;
    });

    // Re-ordenar en el DOM
    visibleRows.forEach(tr=> tbody.appendChild(tr));
  }

  if (filterBox) filterBox.addEventListener('input', applyFilterAndSort);
  if (sortSelect) sortSelect.addEventListener('change', applyFilterAndSort);
  applyFilterAndSort();

  // Export CSV (cliente)
  const btnCsv = document.getElementById('btnExportCsv');
  if (btnCsv && window.__pv){
    btnCsv.addEventListener('click', function(){
      const months = window.__pv.monthsLabels || [];
      // Cabeceras
      const headers = ['Producto'];
      months.forEach(m => { headers.push(m+' Pzs'); headers.push(m+' $'); });
      headers.push('Tot. Pzs','Tot. $','Ticket prom.','$ Share %','Ranking $');

      // Tomar datos del DOM (respeta filtros y orden actual)
      const trs = Array.from(document.querySelectorAll('#pv-table tbody tr')).filter(tr=> tr.style.display !== 'none');

      const lines = [headers.join(',')];
      trs.forEach(tr=>{
        const tds = Array.from(tr.querySelectorAll('td'));
        const producto = (tds[0]?.innerText||'').replace(/\\s+/g,' ').trim();
        const cells = [];

        // Meses: cada mes tiene "unidades" y <small>monto</small>
        for (let i=1;i<=months.length;i++){
          const td = tds[i];
          if (!td){ cells.push('0','0'); continue; }
          const parts = td.innerText.split('\\n').map(s=>s.trim()).filter(Boolean);
          const units = (parts[0]||'0').replace(/[,]/g,'');
          const money = (parts[1]||'0').replace(/[^0-9.\\-]/g,'');
          cells.push(units, money);
        }

        const totU = (tds[months.length+1]?.innerText||'0').replace(/[,]/g,'');
        const totM = (tds[months.length+2]?.innerText||'0').replace(/[^0-9.\\-]/g,'');
        const avg  = (tds[months.length+3]?.innerText||'0').replace(/[^0-9.\\-]/g,'');
        const share= (tds[months.length+4]?.innerText||'0').replace('%','');
        const rank = (tds[months.length+5]?.innerText||'').replace(/[^0-9]/g,'');

        const rowCsv = [producto].concat(cells).concat([totU,totM,avg,share,rank]);
        // Escapar comas en texto
        lines.push(rowCsv.map(v=>{
          v = String(v);
          if (v.includes(',') || v.includes('"')) return '"' + v.replace(/"/g,'""') + '"';
          return v;
        }).join(','));
      });

      const blob = new Blob([lines.join('\\r\\n')], {type:'text/csv;charset=utf-8;'});
      const a = document.createElement('a');
      a.href = URL.createObjectURL(blob);
      a.download = 'productos_' + (window.__pv.year || '') + '.csv';
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
    });
  }
})();
JS);