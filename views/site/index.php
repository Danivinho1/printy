<?php


/** @var yii\web\View $this */

use yii\helpers\Html;
use yii\helpers\Url;

// Configurar el layout para usar el modo dashboard
$this->params['is_dashboard'] = true;

$this->title = 'Sistema Empresarial - ' . Yii::$app->name;

// Configurar pestañas del dashboard que redirigen a secciones reales
$departments = [
    ['id' => 'admin', 'name' => 'Dashboard', 'url' => ['/site/index'], 'color' => 'purple'],
    ['id' => 'ventas', 'name' => 'Ventas', 'url' => ['/ventas/index'], 'color' => 'green'],
    ['id' => 'diseno', 'name' => 'Diseño', 'url' => ['/diseno/index'], 'color' => 'blue'],
    ['id' => 'produccion', 'name' => 'Producción', 'url' => ['/produccion/index'], 'color' => 'orange'],
    ['id' => 'logistica', 'name' => 'Logística', 'url' => ['/logistica/index'], 'color' => 'red'],
    ['id' => 'analytics', 'name' => 'Analytics', 'url' => ['/reportes/index'], 'color' => 'indigo']
];

// Configurar las pestañas del dashboard
ob_start();
$icons = [
    'admin' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>',
    'ventas' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17M17 13v4a2 2 0 01-2 2H9a2 2 0 01-2-2v-4m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"></path>',
    'diseno' => '<circle cx="13.5" cy="6.5" r=".5"></circle><circle cx="17.5" cy="10.5" r=".5"></circle><circle cx="8.5" cy="7.5" r=".5"></circle><circle cx="6.5" cy="12.5" r=".5"></circle><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"></path>',
    'produccion' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m2 8 2 2-2 2 2 2-2 2"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m22 8-2 2 2 2-2 2 2 2"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 8v10c0 .55.45 1 1 1h6c.55 0 1-.45 1-1V8"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 8V6a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>',
    'logistica' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 18H9"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"></path><circle cx="17" cy="18" r="2"></circle><circle cx="7" cy="18" r="2"></circle>',
    'analytics' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3v18h18"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-5 5-4-4-3 3"></path>'
];

$currentRoute = Yii::$app->requestedRoute;
foreach ($departments as $dept):
    $isActive = ($dept['id'] === 'admin' && $currentRoute === 'site/index') || 
                strpos($currentRoute, trim($dept['url'][0], '/')) !== false;
?>
    <?= Html::a(
        '<svg class="department-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">' . 
        $icons[$dept['id']] . 
        '</svg>' .
        '<span>' . Html::encode($dept['name']) . '</span>',
        $dept['url'],
        [
            'class' => 'department-tab ' . ($isActive ? 'active' : ''),
            'encode' => false
        ]
    ) ?>
<?php endforeach;
$this->params['dashboard_tabs'] = ob_get_clean();

// Configurar controles del navbar
ob_start();
$months = [
    date('Y-m') => date('F Y'),
    date('Y-m', strtotime('-1 month')) => date('F Y', strtotime('-1 month')),
    date('Y-m', strtotime('-2 months')) => date('F Y', strtotime('-2 months')),
    date('Y-m', strtotime('-3 months')) => date('F Y', strtotime('-3 months')),
    date('Y-m', strtotime('-4 months')) => date('F Y', strtotime('-4 months')),
    date('Y-m', strtotime('-5 months')) => date('F Y', strtotime('-5 months'))
];
?>
<div style="display: flex; align-items: center; gap: 0.5rem;">
    <svg style="width: 1rem; height: 1rem; color: #6b7280;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
    </svg>
    <?= Html::dropDownList('month', date('Y-m'), $months, [
        'class' => 'search-input',
        'style' => 'width: auto; padding-left: 0.75rem; margin: 0;'
    ]) ?>
</div>
<?php
$this->params['dashboard_controls'] = ob_get_clean();
?>

<!-- KPIs Rápidos -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h6>Unidades Vendidas</h6>
                    <p class="display-6"><?= $totalUnidades ?></p>
                    <span class="badge bg-info"><?= $porcentajeUnidades ?>%</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h6>Ingresos Totales</h6>
                    <p class="display-6">$<?= number_format($totalDinero, 2) ?></p>
                    <span class="badge bg-success"><?= $porcentajeDinero ?>%</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h6>Anticipos</h6>
                    <p class="display-6">$<?= number_format($totalAnticipo, 2) ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h6>Restante</h6>
                    <p class="display-6">$<?= number_format($totalRestante, 2) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Progreso hacia metas -->
    <div class="row mb-4">
        <?= $this->render('_progresoVentas', [
            'porcentajeUnidades' => $porcentajeUnidades,
            'porcentajeDinero'   => $porcentajeDinero,
            'totalUnidades'      => $totalUnidades,
            'metaUnidades'       => $metaUnidades,
            'totalDinero'        => $totalDinero,
            'metaDinero'         => $metaDinero,
        ]) ?>
    </div>

    <!-- Top Productos -->
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="mb-3">Top Productos Vendidos</h6>
                    <ul class="list-group">
                        <?php foreach ($productosVendidos as $producto): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?= Html::encode($producto['nombre']) ?>
                                <span class="badge bg-primary rounded-pill">
                                    <?= $producto['total_unidades'] ?> u.
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>