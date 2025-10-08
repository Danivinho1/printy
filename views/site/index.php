<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\db\Query;

$this->title = 'Dashboard';
$this->params['breadcrumbs'][] = $this->title;

/**
 Este dashboard es independiente de Marketing.
 No incluye tabs ni métricas de Retorno, Campañas, Comparativas ni Productos.
 Muestra:
  - KPIs de ventas
  - Tendencia anual
  - Pipeline (Diseño y Envío)
  - Últimas ventas
  - Acciones rápidas (Ventas, Diseño, Logística)

 Puedes pasar variables desde el controlador:
 - int   $year
 - array $kpis = [
     'ventasHoy'      => float,
     'ventasMes'      => float,
     'unidadesMes'    => int,
     'ticketPromedio' => float,
     'metas' => [
       'dinero'   => ['periodo'=>'YYYY-MM','valor'=>float,'avance'=>float 0..1],
       'unidades' => ['periodo'=>'YYYY-MM','valor'=>float,'avance'=>float 0..1],
     ],
     'pipeline' => [
       'disenoPend' => int, 'disenoListo'=>int,
       'envioPend'  => int, 'envioEnviado'=>int
     ],
     'topProducto'  => ['nombre'=>string,'monto'=>float,'pzs'=>int],
   ]
 - array $tendencia = [1..12 => float]  // ventas MXN por mes del $year
 - array $ultimasVentas = [ ['id'=>int,'fecha'=>datetime,'producto'=>string,'pzs'=>int,'monto'=>float], ... ]

 Si no las pasas, esta vista hace consultas de solo lectura para calcular valores básicos.
*/

// Fallbacks y auto-cálculo si no vienen desde el controlador
$year = isset($year) ? (int)$year : (int)date('Y');

$monthStart = date('Y-m-01 00:00:00');
$monthEnd   = date('Y-m-t 23:59:59');
$todayStart = date('Y-m-d 00:00:00');
$todayEnd   = date('Y-m-d 23:59:59');
$ymPeriod   = date('Y-m');

// Si no hay $kpis, calcular mínimos viables
if (!isset($kpis) || !is_array($kpis)) {
    $ventasMes = (float)((new Query())->from('ventas')->where(['between','fecha_compra',$monthStart,$monthEnd])->sum('precio_total') ?: 0);
    $ventasHoy = (float)((new Query())->from('ventas')->where(['between','fecha_compra',$todayStart,$todayEnd])->sum('precio_total') ?: 0);
    $unidadesMes = (int)((new Query())->from('ventas')->where(['between','fecha_compra',$monthStart,$monthEnd])->sum('unidades') ?: 0);
    $countMes = (int)((new Query())->from('ventas')->where(['between','fecha_compra',$monthStart,$monthEnd])->count('*') ?: 0);
    $ticketPromedio = $countMes > 0 ? $ventasMes / $countMes : 0.0;

    // Metas (si existen en tabla metas)
    $metaDinero = (float)((new Query())->from('metas')->where(['tipo'=>'dinero','periodo'=>$ymPeriod])->scalar() ?: 0);
    $metaUnidades = (float)((new Query())->from('metas')->where(['tipo'=>'unidades','periodo'=>$ymPeriod])->scalar() ?: 0);
    $avanceDinero = $metaDinero > 0 ? min(1, $ventasMes / $metaDinero) : 0;
    $avanceUnidades = $metaUnidades > 0 ? min(1, $unidadesMes / $metaUnidades) : 0;

    // Pipeline (diseño y logística)
    $disenoPend = (int)((new Query())->from('diseno')->where(['estatus_id'=>31])->count('*') ?: 0); // 31=Pendiente
    $disenoList = (int)((new Query())->from('diseno')->where(['estatus_id'=>32])->count('*') ?: 0); // 32=Listo
    $envioPend  = (int)((new Query())->from('logistica')->where(['estatus_envio_id'=>41])->count('*') ?: 0); // 41=Pendiente
    $envioEnv   = (int)((new Query())->from('logistica')->where(['estatus_envio_id'=>42])->count('*') ?: 0); // 42=Enviado

    // Top producto del mes
    $topProd = (new Query())
        ->select(['nombre'=>'c.nombre','monto'=>'SUM(v.precio_total)','pzs'=>'SUM(v.unidades)'])
        ->from(['v'=>'ventas'])
        ->leftJoin(['c'=>'catalogos'], 'c.id = v.tipo_letrero_id')
        ->where(['between','v.fecha_compra',$monthStart,$monthEnd])
        ->andWhere(['c.tipo'=>'tipo_letrero'])
        ->groupBy(['c.id','c.nombre'])
        ->orderBy(['SUM(v.precio_total)'=>SORT_DESC])
        ->limit(1)->one();
    $topProducto = [
        'nombre' => $topProd['nombre'] ?? '—',
        'monto'  => (float)($topProd['monto'] ?? 0),
        'pzs'    => (int)($topProd['pzs'] ?? 0),
    ];

    // Tendencia anual MXN
    $rows = (new Query())
        ->select(['m'=>'MONTH(fecha_compra)','t'=>'SUM(precio_total)'])
        ->from('ventas')->where(['between','fecha_compra',"$year-01-01 00:00:00","$year-12-31 23:59:59"])
        ->groupBy(['MONTH(fecha_compra)'])->all();
    $tendencia = array_fill(1,12,0.0); foreach ($rows as $r) { $tendencia[(int)$r['m']] = (float)$r['t']; }

    // Últimas ventas
    $ultimas = (new Query())
        ->select(['id','fecha'=>'fecha_compra','pzs'=>'unidades','monto'=>'precio_total','producto'=>'nombre_letrero'])
        ->from('ventas')->orderBy(['fecha_compra'=>SORT_DESC])->limit(8)->all();
    $ultimasVentas = array_map(function($r){
        return [
            'id'=>(int)$r['id'],
            'fecha'=>$r['fecha'],
            'producto'=>$r['producto'],
            'pzs'=>(int)$r['pzs'],
            'monto'=>(float)$r['monto'],
        ];
    }, $ultimas);

    $kpis = [
        'ventasHoy'=>$ventasHoy,
        'ventasMes'=>$ventasMes,
        'unidadesMes'=>$unidadesMes,
        'ticketPromedio'=>$ticketPromedio,
        'metas'=>[
            'dinero'   => ['periodo'=>$ymPeriod,'valor'=>$metaDinero,'avance'=>$avanceDinero],
            'unidades' => ['periodo'=>$ymPeriod,'valor'=>$metaUnidades,'avance'=>$avanceUnidades],
        ],
        'pipeline'=>[
            'disenoPend'=>$disenoPend,'disenoListo'=>$disenoList,
            'envioPend'=>$envioPend,'envioEnviado'=>$envioEnv,
        ],
        'topProducto'=>$topProducto,
    ];
}

// Helpers
$money = fn($n) => Yii::$app->formatter->asCurrency((float)$n, 'MXN');
$int   = fn($n) => number_format((float)$n, 0, '.', ',');
$months = [1=>'Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];

// Datos para Chart.js
$chartLabels = array_values($months);

// Construir $chartData asegurando 12 valores numéricos
$chartData = [];
for ($i=1;$i<=12;$i++) {
    $chartData[] = isset($tendencia[$i]) ? round((float)$tendencia[$i],2) : 0.0;
}

// Exportar datos del gráfico al head (evita “Array to string conversion”)
$this->registerJs(
    'window.__chartLabels = ' . json_encode($chartLabels, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) . ';
     window.__chartData   = ' . json_encode($chartData, JSON_NUMERIC_CHECK) . ';
     window.__chartYear   = ' . json_encode($year) . ';',
    \yii\web\View::POS_HEAD
);

$this->registerCss(<<<CSS
.site-bg {
  background-image:
    radial-gradient(70% 40% at 10% -10%, rgba(99,102,241,.08) 0%, rgba(99,102,241,0) 60%),
    radial-gradient(70% 40% at 110% 0%, rgba(236,72,153,.08) 0%, rgba(236,72,153,0) 60%),
    linear-gradient(#f1f5f9 1px, transparent 1px),
    linear-gradient(90deg, #f1f5f9 1px, transparent 1px);
  background-size: auto, auto, 24px 24px, 24px 24px;
  background-position: center, center, -1px -1px, -1px -1px;
  background-color: #fff;
}
.card-soft { border-radius: 1rem; border:1px solid #e5e7eb; background:#fff; box-shadow:0 8px 20px rgba(2,6,23,.04); }
.kpi .label { font-size:.85rem; color:#64748b; }
.kpi .value { font-weight:800; font-size:1.45rem; color:#0f172a; }
.progress-wrap { background:#eef2f7; border-radius:9999px; overflow:hidden; height:10px; }
.progress-bar { height:100%; border-radius:9999px; background-image:linear-gradient(90deg,#4f46e5,#7c3aed); }
.list-mini { list-style:none; padding:0; margin:0; }
.list-mini li { display:flex; justify-content:space-between; padding:.4rem 0; border-bottom:1px dashed #eef2f7; }
.list-mini li:last-child { border-bottom:0; }
.table-wrap { overflow-x:auto; }
.table thead th { position: sticky; top: 0; background: #fff; z-index: 1; }
.quick-actions .btn { display:flex; align-items:center; gap:.5rem; }
.hero-card {
  background-image: linear-gradient(135deg,#4f46e5 0%, #d946ef 50%, #7c3aed 100%);
  color: #fff; border-radius: 1rem; padding: 16px 18px;
}
.hero-card .title { font-size: 1.125rem; font-weight: 700; }
.hero-card .subtitle { font-size: .875rem; opacity: .95; }
CSS);

// Chart.js
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js', ['position'=>\yii\web\View::POS_END]);

$this->registerJs(<<<'JS'
(function(){
  var el = document.getElementById('trendChart');
  if (!el) return;

  var ctx = el.getContext('2d');
  var gradient = ctx.createLinearGradient(0,0,0,240);
  gradient.addColorStop(0,'rgba(99,102,241,0.35)');
  gradient.addColorStop(1,'rgba(99,102,241,0.02)');

  var labels = Array.isArray(window.__chartLabels) ? window.__chartLabels : [];
  var data   = Array.isArray(window.__chartData)   ? window.__chartData   : [];
  var year   = window.__chartYear || new Date().getFullYear();

  new Chart(ctx, {
    type: 'line',
    data: {
      labels: labels,
      datasets: [{
        label: 'Ventas ' + year,
        data: data,
        tension: .35,
        borderColor: '#4f46e5',
        backgroundColor: gradient,
        borderWidth: 2,
        pointRadius: 3,
        pointBackgroundColor: '#4f46e5'
      }]
    },
    options: {
      plugins: { legend: { display: false } },
      scales: {
        y: { ticks: { callback: function(v){ return '$' + Number(v).toLocaleString('es-MX'); } } },
        x: { grid: { display:false } }
      }
    }
  });
})();
JS, \yii\web\View::POS_END);
?>

<div class="site-bg min-vh-100">
  <div class="container py-4">

    <!-- Encabezado -->
    <div class="hero-card d-flex align-items-center justify-content-between mb-3">
      <div>
        <div class="title"><?= Html::encode($this->title) ?></div>
        <div class="subtitle">Resumen operativo y de ventas</div>
      </div>
      <div class="text-end">
        <div class="small">Fecha</div>
        <div class="fw-semibold"><?= Html::encode(date('Y-m-d')) ?></div>
      </div>
    </div>

    <!-- Acciones rápidas (sin marketing) -->
    <div class="row g-3 quick-actions mb-3">
      <div class="col-12 col-sm-6 col-lg-3">
        <a href="<?= Url::to(['ventas/create']) ?>" class="btn btn-primary w-100">
          <i class="bi bi-plus-lg"></i> Nueva venta
        </a>
      </div>
      <div class="col-12 col-sm-6 col-lg-3">
        <a href="<?= Url::to(['ventas/index']) ?>" class="btn btn-light border w-100">
          <i class="bi bi-table"></i> Ver ventas
        </a>
      </div>
      <div class="col-12 col-sm-6 col-lg-3">
        <a href="<?= Url::to(['diseno/index']) ?>" class="btn btn-light border w-100">
          <i class="bi bi-brush"></i> Diseño
        </a>
      </div>
      <div class="col-12 col-sm-6 col-lg-3">
        <a href="<?= Url::to(['logistica/index']) ?>" class="btn btn-light border w-100">
          <i class="bi bi-truck"></i> Logística
        </a>
      </div>
    </div>

    <!-- KPIs principales -->
    <div class="row g-3 mb-3">
      <div class="col-12 col-lg-6">
        <div class="card-soft p-4 kpi h-100">
          <div class="label mb-1">Ventas del mes</div>
          <div class="value mb-2"><?= $money($kpis['ventasMes'] ?? 0) ?></div>
          <?php $meta = $kpis['metas']['dinero'] ?? ['valor'=>0,'avance'=>0]; $pct = isset($meta['avance'])? round($meta['avance']*100):0; ?>
          <div class="d-flex justify-content-between small text-muted mb-1">
            <span>Meta: <?= $money($meta['valor'] ?? 0) ?></span>
            <span><?= $pct ?>%</span>
          </div>
          <div class="progress-wrap"><div class="progress-bar" style="width: <?= min(100,$pct) ?>%"></div></div>
        </div>
      </div>
      <div class="col-6 col-lg-2">
        <div class="card-soft p-4 kpi h-100">
          <div class="label mb-1">Hoy</div>
          <div class="value"><?= $money($kpis['ventasHoy'] ?? 0) ?></div>
        </div>
      </div>
      <div class="col-6 col-lg-2">
        <div class="card-soft p-4 kpi h-100">
          <div class="label mb-1">Ticket promedio</div>
          <div class="value"><?= $money($kpis['ticketPromedio'] ?? 0) ?></div>
        </div>
      </div>
      <div class="col-6 col-lg-2">
        <div class="card-soft p-4 kpi h-100">
          <div class="label mb-1">Unidades (mes)</div>
          <div class="value"><?= $int($kpis['unidadesMes'] ?? 0) ?></div>
          <?php $metaU = $kpis['metas']['unidades'] ?? ['valor'=>0,'avance'=>0]; $pctU = isset($metaU['avance'])? round($metaU['avance']*100):0; ?>
          <div class="progress-wrap mt-2"><div class="progress-bar" style="width: <?= min(100,$pctU) ?>%"></div></div>
        </div>
      </div>
    </div>

    <!-- Tendencia + Lateral (Top producto y Pipeline) -->
    <div class="row g-3 mb-4">
      <div class="col-12 col-xl-8">
        <div class="card-soft p-3 h-100">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="fw-semibold">Tendencia anual (<?= $year ?>)</div>
            <div class="text-muted small">Suma de ventas por mes</div>
          </div>
          <canvas id="trendChart" height="120"></canvas>
        </div>
      </div>
      <div class="col-12 col-xl-4">
        <div class="card-soft p-3 mb-3">
          <div class="fw-semibold mb-2">Top producto (mes)</div>
          <ul class="list-mini">
            <li><span>Nombre</span><span class="fw-semibold"><?= Html::encode($kpis['topProducto']['nombre'] ?? '—') ?></span></li>
            <li><span>Unidades</span><span class="fw-semibold"><?= $int($kpis['topProducto']['pzs'] ?? 0) ?></span></li>
            <li><span>Monto</span><span class="fw-semibold"><?= $money($kpis['topProducto']['monto'] ?? 0) ?></span></li>
          </ul>
        </div>
        <div class="card-soft p-3">
          <div class="fw-semibold mb-2">Pipeline</div>
          <?php $p = $kpis['pipeline'] ?? ['disenoPend'=>0,'disenoListo'=>0,'envioPend'=>0,'envioEnviado'=>0]; ?>
          <ul class="list-mini">
            <li><span>Diseño · Pendiente</span><span class="fw-semibold"><?= $int($p['disenoPend']) ?></span></li>
            <li><span>Diseño · Listo</span><span class="fw-semibold"><?= $int($p['disenoListo']) ?></span></li>
            <li><span>Envío · Pendiente</span><span class="fw-semibold"><?= $int($p['envioPend']) ?></span></li>
            <li><span>Envío · Enviado</span><span class="fw-semibold"><?= $int($p['envioEnviado']) ?></span></li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Últimas ventas -->
    <div class="card-soft p-3">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="fw-semibold">Últimas ventas</div>
        <a class="small" href="<?= Url::to(['ventas/index']) ?>">Ver todo</a>
      </div>
      <div class="table-wrap">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th>ID</th>
              <th>Fecha</th>
              <th>Producto</th>
              <th class="text-end">Pzs</th>
              <th class="text-end">Monto</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($ultimasVentas)): ?>
              <tr><td colspan="6" class="text-muted">Sin ventas recientes</td></tr>
            <?php else: foreach ($ultimasVentas as $v): ?>
              <tr>
                <td>#<?= (int)$v['id'] ?></td>
                <td><?= Html::encode(date('Y-m-d H:i', strtotime($v['fecha']))) ?></td>
                <td><?= Html::encode($v['producto']) ?></td>
                <td class="text-end"><?= $int($v['pzs']) ?></td>
                <td class="text-end"><?= $money($v['monto']) ?></td>
                <td class="text-end">
                  <a class="btn btn-sm btn-light border" href="<?= Url::to(['ventas/view','id'=>(int)$v['id']]) ?>">Ver</a>
                </td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>