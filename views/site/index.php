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

<div class="space-y-6">
    <!-- Tarjetas KPI -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="kpi-card kpi-card-green">
            <div class="kpi-content">
                <div class="kpi-text">
                    <p class="kpi-label">Ventas Total</p>
                    <p class="kpi-value">$2,450,000</p>
                    <p class="kpi-trend">
                        <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                        +15% vs mes anterior
                    </p>
                </div>
                <svg class="kpi-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                </svg>
            </div>
        </div>

        <div class="kpi-card kpi-card-blue">
            <div class="kpi-content">
                <div class="kpi-text">
                    <p class="kpi-label">Proyectos Activos</p>
                    <p class="kpi-value">23</p>
                    <p class="kpi-trend">
                        <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        8 en revisión
                    </p>
                </div>
                <svg class="kpi-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4z"></path>
                </svg>
            </div>
        </div>

        <div class="kpi-card kpi-card-orange">
            <div class="kpi-content">
                <div class="kpi-text">
                    <p class="kpi-label">Producción</p>
                    <p class="kpi-value">1,245</p>
                    <p class="kpi-trend">
                        <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        unidades este mes
                    </p>
                </div>
                <svg class="kpi-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
            </div>
        </div>

        <div class="kpi-card kpi-card-red">
            <div class="kpi-content">
                <div class="kpi-text">
                    <p class="kpi-label">Entregas</p>
                    <p class="kpi-value">96%</p>
                    <p class="kpi-trend">
                        <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        a tiempo
                    </p>
                </div>
                <svg class="kpi-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM21 17a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
        </div>
    </div>


        <!-- Actividad Reciente -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <svg style="width: 1.25rem; height: 1.25rem; color: #6b7280;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4 12h8m0 0v8m0-8L8 8"></path>
                    </svg>
                    Actividad Reciente
                </h3>
            </div>
            <div class="card-body">
                <div class="space-y-3">
                    <div class="notification notification-success">
                        <div class="flex items-start space-x-3">
                            <svg style="width: 1rem; height: 1rem; color: #10b981; margin-top: 2px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div style="flex: 1;">
                                <p class="text-sm font-medium">Nueva venta registrada - $450,000</p>
                                <p class="text-xs text-gray-500">Hace 15 min</p>
                            </div>
                        </div>
                    </div>

                    <div class="notification notification-info">
                        <div class="flex items-start space-x-3">
                            <svg style="width: 1rem; height: 1rem; color: #3b82f6; margin-top: 2px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4 12h8m0 0v8m0-8L8 8"></path>
                            </svg>
                            <div style="flex: 1;">
                                <p class="text-sm font-medium">Diseño completado para ABC Corp</p>
                                <p class="text-xs text-gray-500">Hace 1 hora</p>
                            </div>
                        </div>
                    </div>

                    <div class="notification notification-warning">
                        <div class="flex items-start space-x-3">
                            <svg style="width: 1rem; height: 1rem; color: #f59e0b; margin-top: 2px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            <div style="flex: 1;">
                                <p class="text-sm font-medium">Retraso en producción - Orden #PR-001</p>
                                <p class="text-xs text-gray-500">Hace 2 horas</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


<?php
// CSS adicional para botones
$this->registerCss("
.btn-orange { 
    background-color: #f97316; 
    color: white; 
}
.btn-orange:hover { 
    background-color: #ea580c; 
}
.btn-purple { 
    background-color: #8b5cf6; 
    color: white; 
}
.btn-purple:hover { 
    background-color: #7c3aed; 
}
");

// JavaScript para animaciones
$this->registerJs("
    document.addEventListener('DOMContentLoaded', function() {
        // Animación de entrada para elementos
        const elements = document.querySelectorAll('.kpi-card, .card');
        elements.forEach((el, index) => {
            el.style.animationDelay = (index * 0.1) + 's';
            el.classList.add('fade-in');
        });
    });
");
?>