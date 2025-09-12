<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Dashboard de Ventas';
$this->params['breadcrumbs'][] = ['label' => 'Ventas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Registrar CSS como archivo separado para mejor rendimiento
$this->registerCssFile('@web/css/dashboard-sales.css', ['depends' => [\yii\web\YiiAsset::class]]);

// Funciones auxiliares movidas a un helper class sería mejor práctica
class DashboardHelper {
    
    public static function generarColorUnico($texto) {
        $textoLower = strtolower(trim($texto));
        
        // Colores especiales para casos específicos
        $coloresEspeciales = [
            'urgente' => ['bg' => '#e53e3e', 'text' => '#ffffff'],
            'prioritario' => ['bg' => '#dd6b20', 'text' => '#ffffff'],
            'importante' => ['bg' => '#d69e2e', 'text' => '#1a202c'],
        ];
        
        if (isset($coloresEspeciales[$textoLower])) {
            return $coloresEspeciales[$textoLower];
        }
        
        // Paleta de colores discretos y profesionales
        $coloresDiscretos = [
            ['bg' => '#4299e1', 'text' => '#ffffff'], // Azul profesional
            ['bg' => '#48bb78', 'text' => '#ffffff'], // Verde corporativo
            ['bg' => '#ed8936', 'text' => '#ffffff'], // Naranja suave
            ['bg' => '#9f7aea', 'text' => '#ffffff'], // Púrpura elegante
            ['bg' => '#38b2ac', 'text' => '#ffffff'], // Teal profesional
            ['bg' => '#e53e3e', 'text' => '#ffffff'], // Rojo corporativo
            ['bg' => '#667eea', 'text' => '#ffffff'], // Índigo suave
            ['bg' => '#f56565', 'text' => '#ffffff'], // Rosa coral
            ['bg' => '#38a169', 'text' => '#ffffff'], // Verde esmeralda
            ['bg' => '#3182ce', 'text' => '#ffffff'], // Azul océano
            ['bg' => '#805ad5', 'text' => '#ffffff'], // Violeta
            ['bg' => '#319795', 'text' => '#ffffff'], // Cyan
        ];
        
        $hash = crc32($texto);
        $indice = abs($hash) % count($coloresDiscretos);
        return $coloresDiscretos[$indice];
    }

    public static function calcularPorcentaje($actual, $meta) {
        return ($meta == 0) ? 0 : round(($actual / $meta) * 100, 2);
    }

    public static function claseProgreso($porcentaje) {
        if ($porcentaje >= 100) return 'bg-success';
        if ($porcentaje >= 80) return 'bg-info';
        if ($porcentaje >= 60) return 'bg-warning';
        if ($porcentaje >= 30) return 'bg-danger';
        return 'bg-secondary';
    }

    public static function formatearMoneda($cantidad) {
        return '$' . number_format($cantidad, 2);
    }

    public static function formatearNumero($numero) {
        return number_format($numero);
    }

    public static function calcularTicketPromedio($total, $transacciones) {
        return ($transacciones > 0) ? $total / $transacciones : 0;
    }

    public static function obtenerProyeccionMensual($totalActual, $diaActual, $diasDelMes) {
        return ($diaActual > 0) ? ($totalActual / $diaActual) * $diasDelMes : 0;
    }
}

// Variables calculadas
$porcentajeUnidades = DashboardHelper::calcularPorcentaje($totalUnidades, $metaUnidades);
$porcentajeDinero = DashboardHelper::calcularPorcentaje($totalDinero, $metaDinero);
$ticketPromedio = DashboardHelper::calcularTicketPromedio($totalDinero, count($ventasDelMes ?? []));
$proyeccionMensual = DashboardHelper::obtenerProyeccionMensual($totalDinero, date('j'), date('t'));
$porcentajeAnticipo = DashboardHelper::calcularPorcentaje($totalAnticipo, $totalDinero);
$diferenciaMeta = $totalDinero - $metaDinero;
?>

<div class="dashboard-professional">
    <!-- Header del Dashboard -->
    <div class="dashboard-header-pro">
        <div class="container-fluid px-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="header-content">
                        <h1 class="dashboard-title-pro">
                            <i class="fas fa-chart-line text-primary me-3"></i>
                            Dashboard de Ventas
                        </h1>
                        <p class="dashboard-subtitle-pro">
                            <i class="fas fa-calendar-alt me-2"></i>
                            <?= date('F Y', strtotime($mesActual . '-01')) ?>
                            <span class="separator-pro">•</span>
                            <i class="fas fa-clock me-2"></i>
                            Actualizado: <?= date('d/m/Y ') ?>
                        </p>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <div class="header-actions">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones Rápidas -->
    <div class="quick-actions-pro mb-4">
        <div class="container-fluid px-4">
            <div class="row">
                <div class="col-md-8">
                    <div class="action-buttons">
                        <?= Html::a('<i class="fas fa-download me-2"></i>Exportar Datos', ['export'], [
                            'class' => 'btn btn-outline-info btn-sm me-2'
                        ]) ?>
                        <?= Html::a('<i class="fas fa-print me-2"></i>Imprimir', '#', [
                            'class' => 'btn btn-outline-secondary btn-sm me-2',
                            'onclick' => 'window.print(); return false;'
                        ]) ?>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <div class="period-selector">
                        <small class="text-muted">Período actual: <strong><?= date('M Y') ?></strong></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid px-4">

        <!-- KPIs Principales - AHORA VAN PRIMERO -->
        <div class="kpi-grid-expanded mb-5">

            <!-- Meta Unidades -->
            <div class="kpi-card-pro kpi-success">
                <div class="kpi-header flex items-center justify-between">
                    <!-- Contenedor del icono + título -->
                    <div class="flex items-center gap-3">
                        <!-- Icono de caja -->
                        <div class="kpi-icon bg-warning flex items-center justify-center rounded-full w-10 h-10 shadow-md">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path d="M3 7l9-4 9 4-9 4-9-4z" />
                                <path d="M3 7v10l9 4 9-4V7" />
                            </svg>
                        </div>
                        <!-- Texto al lado del icono -->
                        <p class="text-gray-900 font-bold text-lg m-0 leading-tight">Meta Unidades</p>
                    </div>
                    <!-- Badge de porcentaje -->
                    <div class="kpi-trend">
                        <span class="badge badge-soft-warning text-base px-3 py-1"><?= $porcentajeUnidades ?>%</span>
                    </div>
                </div>

                <!-- Contenido KPI -->
                <div class="kpi-content mt-2">
                    <h3 class="kpi-value text-2xl font-extrabold text-gray-800">
                        <?= DashboardHelper::formatearNumero($metaUnidades) ?>
                    </h3>
                    <div class="progress-wrapper mt-2">
                        <div class="progress progress-sm h-2 rounded-full overflow-hidden">
                            <div class="progress-bar bg-warning" style="width: <?= min($porcentajeUnidades, 100) ?>%"></div>
                        </div>
                        <small class="progress-text text-gray-600"><?= $porcentajeUnidades ?>% completado</small>
                    </div>
                </div>
            </div>

            <!-- Meta Mensual Dinero -->
            <div class="kpi-card-pro kpi-success">
                <div class="kpi-header flex items-center justify-between">
                    <!-- Contenedor del icono + título -->
                    <div class="flex items-center gap-3">
                        <div class="kpi-icon bg-success flex items-center justify-center rounded-full w-10 h-10 shadow-md">
                            <span class="text-white text-xl font-extrabold">$</span>
                        </div>
                        <!-- Texto al lado del icono -->
                        <p class="text-gray-900 font-bold text-lg m-0 leading-tight">Meta Dinero</p>
                    </div>
                    <!-- Badge de porcentaje -->
                    <div class="kpi-trend">
                        <span class="badge badge-soft-success text-base px-3 py-1"><?= $porcentajeDinero ?>%</span>
                    </div>
                </div>

                <!-- Contenido KPI -->
                <div class="kpi-content mt-2">
                    <h3 class="kpi-value text-2xl font-extrabold text-gray-800">
                        <?= DashboardHelper::formatearMoneda($metaDinero) ?>
                    </h3>
                    <div class="progress-wrapper mt-2">
                        <div class="progress progress-sm h-2 rounded-full overflow-hidden">
                            <div class="progress-bar bg-success" style="width: <?= min($porcentajeDinero, 100) ?>%"></div>
                        </div>
                        <small class="progress-text text-gray-600"><?= $porcentajeDinero ?>% completado</small>
                    </div>
                </div>
            </div>

        </div>

        <!-- Resumen Ejecutivo - AHORA VA DESPUÉS DE LOS KPIs -->
        <div class="row">
            <div class="col-12">
                <div class="card card-pro card-summary">
                    <div class="card-header card-header-pro">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-chart-bar text-primary me-2"></i>
                            Resumen Ejecutivo del Período
                        </h6>
                        <small class="text-muted"><?= date('F Y', strtotime($mesActual . '-01')) ?> - Actualizado <?= date('d/m/Y') ?></small>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Progreso General -->
                            <div class="col-xl-4 col-lg-6 mb-4">
                                <h6 class="section-title">Progreso hacia Objetivos</h6>
                                
                                <div class="progress-section">
                                    <div class="progress-item-expanded">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="progress-label">Unidades Vendidas</span>
                                            <div class="progress-badges">
                                                <span class="badge bg-info"><?= $porcentajeUnidades ?>%</span>
                                            </div>
                                        </div>
                                        <div class="progress progress-enhanced mb-2">
                                            <div class="progress-bar bg-info" style="width: <?= min($porcentajeUnidades, 100) ?>%"></div>
                                        </div>
                                        <small class="text-muted">
                                            <?= DashboardHelper::formatearNumero($totalUnidades) ?> de <?= DashboardHelper::formatearNumero($metaUnidades) ?> unidades
                                        </small>
                                    </div>

                                    <div class="progress-item-expanded">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="progress-label">Ingresos Totales</span>
                                            <div class="progress-badges">
                                                <span class="badge bg-success"><?= $porcentajeDinero ?>%</span>
                                            </div>
                                        </div>
                                        <div class="progress progress-enhanced mb-2">
                                            <div class="progress-bar bg-success" style="width: <?= min($porcentajeDinero, 100) ?>%"></div>
                                        </div>
                                        <small class="text-muted">
                                            <?= DashboardHelper::formatearMoneda($totalDinero) ?> de <?= DashboardHelper::formatearMoneda($metaDinero) ?>
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- Métricas Clave -->
                            <div class="col-xl-4 col-lg-6 mb-4">
                                <h6 class="section-title">Indicadores Financieros</h6>
                                
                                <div class="metrics-summary">
                                    <div class="summary-metric">
                                        <div class="metric-icon-small bg-danger">
                                            <i class="fas fa-clock"></i>
                                        </div>
                                        <div class="metric-details">
                                            <h5><?= round(($totalRestante / ($totalDinero ?: 1)) * 100) ?>%</h5>
                                            <span>Pendiente de Cobro</span>
                                        </div>
                                        <div class="metric-amount">
                                            <?= DashboardHelper::formatearMoneda($totalRestante) ?>
                                        </div>
                                    </div>

                                    <div class="summary-metric">
                                        <div class="metric-icon-small bg-warning">
                                            <i class="fas fa-percentage"></i>
                                        </div>
                                        <div class="metric-details">
                                            <h5><?= $porcentajeAnticipo ?>%</h5>
                                            <span>Anticipos del Total</span>
                                        </div>
                                        <div class="metric-amount">
                                            <?= DashboardHelper::formatearMoneda($totalAnticipo) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Análisis del Período -->
                            <div class="col-xl-4 col-lg-12 mb-4">
                                <h6 class="section-title">Análisis del Rendimiento</h6>
                                
                                <div class="performance-grid">
                                    <div class="performance-card">
                                        <div class="performance-icon bg-primary">
                                            <i class="fas fa-calendar-day"></i>
                                        </div>
                                        <div class="performance-info">
                                            <h4><?= date('j') ?> / <?= date('t') ?></h4>
                                            <span>Días del mes</span>
                                            <div class="performance-progress">
                                                <div class="mini-progress">
                                                    <div class="mini-progress-bar" style="width: <?= (date('j') / date('t')) * 100 ?>%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="performance-card">
                                        <div class="performance-icon <?= $diferenciaMeta >= 0 ? 'bg-success' : 'bg-warning' ?>">
                                            <i class="fas fa-<?= $diferenciaMeta >= 0 ? 'arrow-up' : 'arrow-down' ?>"></i>
                                        </div>
                                        <div class="performance-info">
                                            <h4 class="<?= $diferenciaMeta >= 0 ? 'text-success' : 'text-warning' ?>">
                                                <?= $diferenciaMeta >= 0 ? '+' : '' ?><?= DashboardHelper::formatearMoneda(abs($diferenciaMeta)) ?>
                                            </h4>
                                            <span>Diferencia vs Meta</span>
                                            <small class="<?= $diferenciaMeta >= 0 ? 'text-success' : 'text-warning' ?>">
                                                <?= $diferenciaMeta >= 0 ? 'Superando objetivo' : 'Por debajo del objetivo' ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
/* Estilos para las nuevas clases de Tailwind */
.flex { display: flex; }
.items-center { align-items: center; }
.justify-between { justify-content: space-between; }
.gap-3 { gap: 0.75rem; }
.rounded-full { border-radius: 9999px; }
.w-10 { width: 2.5rem; }
.h-10 { height: 2.5rem; }
.w-6 { width: 1.5rem; }
.h-6 { height: 1.5rem; }
.shadow-md { box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
.text-gray-900 { color: #111827; }
.font-bold { font-weight: 700; }
.text-lg { font-size: 1.125rem; }
.m-0 { margin: 0; }
.leading-tight { line-height: 1.25; }
.text-base { font-size: 1rem; }
.px-3 { padding-left: 0.75rem; padding-right: 0.75rem; }
.py-1 { padding-top: 0.25rem; padding-bottom: 0.25rem; }
.mt-2 { margin-top: 0.5rem; }
.text-2xl { font-size: 1.5rem; }
.font-extrabold { font-weight: 800; }
.text-gray-800 { color: #1f2937; }
.h-2 { height: 0.5rem; }
.overflow-hidden { overflow: hidden; }
.text-gray-600 { color: #4b5563; }
.text-white { color: #ffffff; }
.text-xl { font-size: 1.25rem; }

/* Badge styles */
.badge-soft-warning {
    background-color: rgba(217, 119, 6, 0.1);
    color: #d97706;
}

.badge-soft-success {
    background-color: rgba(34, 197, 94, 0.1);
    color: #22c55e;
}

/* Estilos mínimos para los iconos SVG */
.kpi-icon svg {
    width: 1.5rem;
    height: 1.5rem;
    color: white;
}
</style>
            
        </div>

        <!-- Análisis Detallado -->
        <div class="row mb-4">
            <!-- Top Productos -->
            <div class="col-xl-4 col-lg-6 mb-4">
                <div class="card card-pro">
                    <div class="card-header card-header-pro">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-trophy text-warning me-2"></i>
                            Productos Más Vendidos
                        </h6>
                        <small class="text-muted">Este mes</small>
                    </div>
                    <div class="card-body">
                        <?php if (empty($productosVendidos)): ?>
                            <div class="empty-state-pro">
                                <i class="fas fa-box-open fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-0">No hay datos suficientes</p>
                            </div>
                        <?php else: ?>
                            <div class="ranking-list-pro">
                                <?php foreach (array_slice($productosVendidos, 0, 5) as $index => $producto): ?>
                                    <?php $colores = DashboardHelper::generarColorUnico($producto['nombre']); ?>
                                    <div class="ranking-item-pro">
                                        <div class="ranking-position">
                                            <span class="position-number" style="background-color: <?= $colores['bg'] ?>; color: <?= $colores['text'] ?>;">
                                                <?= $index + 1 ?>
                                            </span>
                                        </div>
                                        <div class="ranking-details">
                                            <h6 class="ranking-title"><?= Html::encode($producto['nombre']) ?></h6>
                                            <p class="ranking-subtitle text-muted">
                                                <?= $producto['cantidad'] ?> ventas • 
                                                <?= DashboardHelper::formatearNumero($producto['total_unidades']) ?> unidades
                                            </p>
                                        </div>
                                        <div class="ranking-value text-end">
                                            <span class="value-amount"><?= DashboardHelper::formatearMoneda($producto['total_ventas']) ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Productos Premium -->
            <div class="col-xl-4 col-lg-6 mb-4">
                <div class="card card-pro">
                    <div class="card-header card-header-pro">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-gem text-info me-2"></i>
                            Mayor Valor Promedio
                        </h6>
                        <small class="text-muted">Productos premium</small>
                    </div>
                    <div class="card-body">
                        <?php if (empty($productosPotencial)): ?>
                            <div class="empty-state-pro">
                                <i class="fas fa-gem fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-0">No hay datos suficientes</p>
                            </div>
                        <?php else: ?>
                            <div class="ranking-list-pro">
                                <?php foreach (array_slice($productosPotencial, 0, 5) as $index => $producto): ?>
                                    <?php $colores = DashboardHelper::generarColorUnico($producto['nombre']); ?>
                                    <div class="ranking-item-pro">
                                        <div class="ranking-position">
                                            <span class="position-star" style="background-color: <?= $colores['bg'] ?>; color: <?= $colores['text'] ?>;">
                                                <i class="fas fa-star"></i>
                                            </span>
                                        </div>
                                        <div class="ranking-details">
                                            <h6 class="ranking-title"><?= Html::encode($producto['nombre']) ?></h6>
                                            <p class="ranking-subtitle text-muted">
                                                <?= $producto['cantidad'] ?> ventas • 
                                                Promedio: <?= DashboardHelper::formatearMoneda($producto['promedio_precio']) ?>
                                            </p>
                                        </div>
                                        <div class="ranking-value text-end">
                                            <span class="value-amount"><?= DashboardHelper::formatearMoneda($producto['total_ventas']) ?></span>
                                            <small class="text-muted d-block">Total</small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>


        <!-- Performance Team -->
        <div class="row mb-4">
            <!-- Top Asesores -->
            <div class="col-xl-6 col-lg-6 mb-4">
                <div class="card card-pro">
                    <div class="card-header card-header-pro">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-user-tie text-primary me-2"></i>
                            Top Asesores de Ventas
                        </h6>
                        <small class="text-muted">Rendimiento del equipo</small>
                    </div>
                    <div class="card-body">
                        <?php if (empty($conteoAsesores)): ?>
                            <div class="empty-state-pro">
                                <i class="fas fa-users fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-0">No hay datos de asesores</p>
                            </div>
                        <?php else: ?>
                            <div class="ranking-list-pro">
                                <?php foreach (array_slice($conteoAsesores, 0, 5, true) as $index => $asesor): ?>
                                    <?php $colores = DashboardHelper::generarColorUnico($asesor['nombre']); ?>
                                    <div class="ranking-item-pro">
                                        <div class="ranking-position">
                                            <span class="position-number" style="background-color: <?= $colores['bg'] ?>; color: <?= $colores['text'] ?>;">
                                                <?= $index + 1 ?>
                                            </span>
                                        </div>
                                        <div class="ranking-details">
                                            <h6 class="ranking-title"><?= Html::encode($asesor['nombre']) ?></h6>
                                            <p class="ranking-subtitle text-muted">
                                                <?= $asesor['cantidad'] ?> ventas realizadas
                                            </p>
                                        </div>
                                        <div class="ranking-value text-end">
                                            <span class="value-amount"><?= DashboardHelper::formatearMoneda($asesor['total_ventas']) ?></span>
                                            <small class="text-muted d-block">
                                                Prom: <?= DashboardHelper::formatearMoneda($asesor['total_ventas'] / $asesor['cantidad']) ?>
                                            </small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Canales de Venta -->
            <div class="col-xl-6 col-lg-6 mb-4">
                <div class="card card-pro">
                    <div class="card-header card-header-pro">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-bullhorn text-info me-2"></i>
                            Canales de Venta Efectivos
                        </h6>
                        <small class="text-muted">Medios de contacto</small>
                    </div>
                    <div class="card-body">
                        <?php if (empty($conteoMedios)): ?>
                            <div class="empty-state-pro">
                                <i class="fas fa-broadcast-tower fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-0">No hay datos de canales</p>
                            </div>
                        <?php else: ?>
                            <div class="ranking-list-pro">
                                <?php foreach (array_slice($conteoMedios, 0, 5, true) as $index => $medio): ?>
                                    <?php $colores = DashboardHelper::generarColorUnico($medio['nombre']); ?>
                                    <div class="ranking-item-pro">
                                        <div class="ranking-position">
                                            <span class="position-number" style="background-color: <?= $colores['bg'] ?>; color: <?= $colores['text'] ?>;">
                                                <?= $index + 1 ?>
                                            </span>
                                        </div>
                                        <div class="ranking-details">
                                            <h6 class="ranking-title"><?= Html::encode($medio['nombre']) ?></h6>
                                            <p class="ranking-subtitle text-muted">
                                                <?= $medio['cantidad'] ?> conversiones exitosas
                                            </p>
                                        </div>
                                        <div class="ranking-value text-end">
                                            <span class="value-amount"><?= DashboardHelper::formatearMoneda($medio['total_ventas']) ?></span>
                                            <small class="text-muted d-block">
                                                Prom: <?= DashboardHelper::formatearMoneda($medio['total_ventas'] / $medio['cantidad']) ?>
                                            </small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

<?php
// JavaScript mejorado y corregido
$this->registerJs("
$(document).ready(function() {
    // Configuración de tooltips
    $('[data-bs-toggle=\"tooltip\"]').tooltip();
    
    // Actualización automática cada 5 minutos
    let autoRefreshInterval = setInterval(function() {
        showRefreshNotification();
    }, 300000);
    
    // Función para mostrar notificación de actualización
    function showRefreshNotification() {
        const notification = $('<div class=\"refresh-notification-pro\">' +
            '<div class=\"notification-content\">' +
                '<i class=\"fas fa-sync-alt fa-spin me-2\"></i>' +
                '<span>Actualizando datos...</span>' +
            '</div>' +
            '</div>');
        
        $('body').append(notification);
        notification.fadeIn(300);
        
        setTimeout(function() {
            window.location.reload();
        }, 2000);
    }
    
    // Botón de actualización manual
    $('#refresh-dashboard').click(function(e) {
        e.preventDefault();
        showRefreshNotification();
    });
    
    // Animación sutil de entrada para las tarjetas
    $('.kpi-card-pro, .card-pro').each(function(index) {
        $(this).css({
            'opacity': '0',
            'transform': 'translateY(20px)'
        }).delay(index * 100).animate({
            'opacity': 1
        }, 400, function() {
            $(this).css('transform', 'translateY(0)');
        });
    });
    
    // Efectos hover sutiles
    $('.kpi-card-pro').hover(
        function() {
            $(this).css('transform', 'translateY(-3px)');
        },
        function() {
            $(this).css('transform', 'translateY(0)');
        }
    );
    
    // Contador animado para los KPIs - CORREGIDO
    $('.kpi-value').each(function() {
        const \$this = $(this);
        const text = \$this.text();
        
        if (text.match(/^\$?[\d,]+/)) {
            const finalNumber = parseFloat(text.replace(/[\$,]/g, ''));
            if (!isNaN(finalNumber) && finalNumber > 0) {
                \$this.text('0');
                \$({ counter: 0 }).animate({ counter: finalNumber }, {
                    duration: 1500,
                    step: function() {
                        const format = text.includes('\
                                        ) ? '\
                                         + Math.floor(this.counter).toLocaleString() : Math.floor(this.counter).toLocaleString();
                        \$this.text(format);
                    },
                    complete: function() {
                        \$this.text(text);
                    }
                });
            }
        }
    });
    
    // Smooth scrolling para navegación interna
    \$('a[href^=\"#\"]').on('click', function(e) {
        e.preventDefault();
        const target = \$(this.getAttribute('href'));
        if (target.length) {
            \$('html, body').animate({
                scrollTop: target.offset().top - 100
            }, 800);
        }
    });
    
    // Lazy loading para mejorar rendimiento
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        document.querySelectorAll('img[data-src]').forEach(img => imageObserver.observe(img));
    }
    
    // Log para debugging
    console.log('Dashboard profesional cargado exitosamente - ' + new Date().toLocaleString());
});
");

// CSS discreto y profesional
$this->registerCss("
/* Variables CSS para consistencia */
:root {
    --primary-color: #3182ce;
    --secondary-color: #718096;
    --success-color: #38a169;
    --info-color: #3182ce;
    --warning-color: #d69e2e;
    --danger-color: #e53e3e;
    --light-color: #f7fafc;
    --dark-color: #2d3748;
    --border-color: #e2e8f0;
    --shadow-sm: 0 1px 3px rgba(0,0,0,0.1);
    --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
    --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
    --border-radius: 8px;
    --border-radius-lg: 12px;
}

/* Container principal más amplio */
.dashboard-professional {
    background-color: #f8fafc;
    min-height: 100vh;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
}

/* Header profesional */
.dashboard-header-pro {
    background: linear-gradient(135deg, #ffffff 0%, #f7fafc 100%);
    border-bottom: 1px solid var(--border-color);
    padding: 1.5rem 0;
    margin-bottom: 1.5rem;
    box-shadow: var(--shadow-sm);
}

.dashboard-title-pro {
    color: var(--dark-color);
    font-size: 2rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.dashboard-subtitle-pro {
    color: var(--secondary-color);
    font-size: 0.95rem;
    margin: 0;
}

.separator-pro {
    margin: 0 0.75rem;
    opacity: 0.6;
}

/* Acciones rápidas */
.quick-actions-pro {
    background: white;
    border-radius: var(--border-radius);
    padding: 1rem 0;
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--border-color);
}

.action-buttons .btn {
    border-radius: 6px;
    font-weight: 500;
}

.period-selector {
    display: flex;
    align-items: center;
    height: 100%;
}

/* Grid de KPIs expandido */
.kpi-grid-expanded {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
}

/* KPI Cards profesionales */
.kpi-card-pro {
    background: white;
    border-radius: var(--border-radius-lg);
    padding: 1.5rem;
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border-color);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.kpi-card-pro::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: var(--secondary-color);
}

.kpi-card-pro.kpi-primary::before { background: var(--primary-color); }
.kpi-card-pro.kpi-success::before { background: var(--success-color); }
.kpi-card-pro.kpi-info::before { background: var(--info-color); }
.kpi-card-pro.kpi-warning::before { background: var(--warning-color); }
.kpi-card-pro.kpi-danger::before { background: var(--danger-color); }

.kpi-card-pro:hover {
    box-shadow: var(--shadow-lg);
    transform: translateY(-2px);
}

.kpi-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.kpi-icon {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
}

.kpi-trend {
    display: flex;
    align-items: center;
    font-size: 0.875rem;
}

.kpi-content .kpi-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--dark-color);
    margin-bottom: 0.25rem;
}

.kpi-content .kpi-label {
    font-size: 0.875rem;
    color: var(--secondary-color);
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.kpi-content .kpi-secondary {
    font-size: 0.875rem;
    color: var(--secondary-color);
}

/* Progress bars discretos */
.progress-wrapper {
    margin-top: 1rem;
}

.progress-sm {
    height: 6px;
}

.progress {
    background-color: #e2e8f0;
    border-radius: 3px;
    overflow: hidden;
}

.progress-text {
    color: var(--secondary-color);
    margin-top: 0.25rem;
}

/* Badges suaves */
.badge-soft-info {
    background-color: rgba(49, 130, 206, 0.1);
    color: var(--info-color);
}

.badge-soft-warning {
    background-color: rgba(214, 158, 46, 0.1);
    color: var(--warning-color);
}

/* Cards profesionales */
.card-pro {
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow-sm);
    transition: all 0.3s ease;
}

.card-pro:hover {
    box-shadow: var(--shadow-md);
}

.card-header-pro {
    background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
    border-bottom: 1px solid var(--border-color);
    border-radius: var(--border-radius-lg) var(--border-radius-lg) 0 0;
    padding: 1rem 1.5rem;
}

.card-title {
    font-weight: 600;
    color: var(--dark-color);
}

.card-summary {
    border: 2px solid var(--border-color);
}

/* Ranking lists profesionales */
.ranking-list-pro {
    max-height: 400px;
    overflow-y: auto;
}

.ranking-list-pro::-webkit-scrollbar {
    width: 4px;
}

.ranking-list-pro::-webkit-scrollbar-track {
    background: var(--light-color);
}

.ranking-list-pro::-webkit-scrollbar-thumb {
    background: var(--border-color);
    border-radius: 2px;
}

.ranking-item-pro {
    display: flex;
    align-items: center;
    padding: 1rem 0;
    border-bottom: 1px solid #f1f5f9;
    transition: all 0.2s ease;
}

.ranking-item-pro:hover {
    background-color: #f8fafc;
    border-radius: 6px;
    margin: 0 -0.5rem;
    padding-left: 1.5rem;
    padding-right: 1.5rem;
}

.ranking-item-pro:last-child {
    border-bottom: none;
}

.ranking-position {
    margin-right: 1rem;
    flex-shrink: 0;
}

.position-number,
.position-star {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    font-weight: 600;
    font-size: 0.875rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.ranking-details {
    flex: 1;
    min-width: 0;
}

.ranking-title {
    font-size: 0.95rem;
    font-weight: 600;
    color: var(--dark-color);
    margin-bottom: 0.25rem;
    line-height: 1.3;
}

.ranking-subtitle {
    font-size: 0.8rem;
    margin: 0;
    line-height: 1.2;
}

.ranking-value {
    flex-shrink: 0;
    text-align: right;
}

.value-amount {
    font-size: 0.95rem;
    font-weight: 600;
    color: var(--dark-color);
}

/* Empty states */
.empty-state-pro {
    text-align: center;
    padding: 2.5rem 1rem;
    color: var(--secondary-color);
}

/* Progress Analysis */
.progress-analysis {
    space-y: 1.5rem;
}

.analysis-item {
    margin-bottom: 1.5rem;
    padding: 1rem;
    background-color: #f8fafc;
    border-radius: var(--border-radius);
    border-left: 3px solid var(--info-color);
}

.analysis-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
}

.analysis-label {
    font-weight: 500;
    color: var(--dark-color);
    font-size: 0.9rem;
}

.analysis-value {
    font-weight: 600;
    font-size: 1.1rem;
}

.metric-cards {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-top: 1rem;
}

.metric-card {
    display: flex;
    align-items: center;
    padding: 1rem;
    background: white;
    border-radius: var(--border-radius);
    border: 1px solid var(--border-color);
}

.metric-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 0.75rem;
    color: white;
    font-size: 1.1rem;
}

.metric-info h4 {
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
    color: var(--dark-color);
}

.metric-info span {
    font-size: 0.8rem;
    color: var(--secondary-color);
    font-weight: 500;
}

/* Section titles */
.section-title {
    color: var(--dark-color);
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid var(--border-color);
}

/* Progress section mejorada */
.progress-section {
    space-y: 1.5rem;
}

.progress-item-expanded {
    margin-bottom: 1.5rem;
    padding: 1rem;
    background-color: #f8fafc;
    border-radius: var(--border-radius);
}

.progress-label {
    font-weight: 500;
    color: var(--dark-color);
    font-size: 0.9rem;
}

.progress-badges .badge {
    font-weight: 600;
}

.progress-enhanced {
    height: 8px;
    background-color: #e2e8f0;
    border-radius: 4px;
    overflow: hidden;
}

/* Metrics Summary */
.metrics-summary {
    space-y: 1rem;
}

.summary-metric {
    display: flex;
    align-items: center;
    padding: 1rem;
    background: #f8fafc;
    border-radius: var(--border-radius);
    border-left: 3px solid var(--warning-color);
    margin-bottom: 1rem;
}

.metric-icon-small {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    color: white;
    font-size: 1rem;
}

.metric-details {
    flex: 1;
}

.metric-details h5 {
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
    color: var(--dark-color);
}

.metric-details span {
    font-size: 0.875rem;
    color: var(--secondary-color);
    font-weight: 500;
}

.metric-amount {
    font-size: 0.875rem;
    color: var(--secondary-color);
    font-weight: 500;
}

/* Performance Grid */
.performance-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.performance-card {
    display: flex;
    align-items: flex-start;
    padding: 1rem;
    background: white;
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    transition: all 0.2s ease;
}

.performance-card:hover {
    box-shadow: var(--shadow-sm);
}

.performance-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 0.75rem;
    color: white;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.performance-info {
    flex: 1;
    min-width: 0;
}

.performance-info h4 {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
    color: var(--dark-color);
    line-height: 1.2;
}

.performance-info span {
    font-size: 0.875rem;
    color: var(--secondary-color);
    font-weight: 500;
    display: block;
    margin-bottom: 0.25rem;
}

.performance-info small {
    font-size: 0.75rem;
}

.performance-progress {
    margin-top: 0.5rem;
}

.mini-progress {
    width: 100%;
    height: 3px;
    background-color: #e2e8f0;
    border-radius: 2px;
    overflow: hidden;
}

.mini-progress-bar {
    height: 100%;
    background-color: var(--primary-color);
    transition: width 0.3s ease;
}

/* Notification profesional */
.refresh-notification-pro {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    background: white;
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-lg);
    display: none;
    min-width: 200px;
}

.notification-content {
    padding: 1rem 1.5rem;
    display: flex;
    align-items: center;
    color: var(--dark-color);
    font-weight: 500;
}

/* Responsive Design */
@media (max-width: 1200px) {
    .kpi-grid-expanded {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    }
    
    .performance-grid {
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    }
}

@media (max-width: 768px) {
    .container-fluid {
        padding-left: 1rem;
        padding-right: 1rem;
    }
    
    .kpi-grid-expanded {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .dashboard-title-pro {
        font-size: 1.75rem;
    }
    
    .quick-actions-pro .row {
        flex-direction: column;
    }
    
    .action-buttons {
        margin-bottom: 1rem;
        text-align: center;
    }
    
    .period-selector {
        justify-content: center;
    }
    
    .metric-cards {
        grid-template-columns: 1fr;
    }
    
    .performance-grid {
        grid-template-columns: 1fr;
    }
    
    .ranking-item-pro {
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    .ranking-value {
        width: 100%;
        text-align: left;
        margin-top: 0.5rem;
    }
}

@media (max-width: 576px) {
    .kpi-card-pro {
        padding: 1rem;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .summary-metric {
        flex-direction: column;
        text-align: center;
        gap: 0.5rem;
    }
    
    .metric-amount {
        margin-top: 0.5rem;
    }
}

/* Animaciones sutiles */
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.kpi-card-pro,
.card-pro {
    animation: slideIn 0.4s ease-out forwards;
}

/* Estados para diferentes colores de background */
.bg-primary { background-color: var(--primary-color) !important; }
.bg-secondary { background-color: var(--secondary-color) !important; }
.bg-success { background-color: var(--success-color) !important; }
.bg-info { background-color: var(--info-color) !important; }
.bg-warning { background-color: var(--warning-color) !important; }
.bg-danger { background-color: var(--danger-color) !important; }

/* Print styles */
@media print {
    .dashboard-professional {
        background: white !important;
    }
    
    .dashboard-header-pro,
    .quick-actions-pro {
        box-shadow: none !important;
        border: 1px solid #ddd !important;
    }
    
    .kpi-card-pro,
    .card-pro {
        break-inside: avoid;
        box-shadow: none !important;
        border: 1px solid #ddd !important;
    }
    
    .header-actions,
    .refresh-notification-pro {
        display: none !important;
    }
}

/* Mejoras en accesibilidad */
.btn:focus,
.card:focus {
    outline: 2px solid var(--primary-color);
    outline-offset: 2px;
}

.progress-bar {
    transition: width 0.6s ease;
}

/* Estados hover mejorados */
.kpi-card-pro:hover .kpi-icon {
    transform: scale(1.05);
}

.ranking-item-pro:hover .position-number,
.ranking-item-pro:hover .position-star {
    transform: scale(1.1);
}
");