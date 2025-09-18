<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

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
                        <?= Html::a('<i class="fas fa-download me-2"></i>Exportar Dashboard', ['export-dashboard'], [
                            'class' => 'btn btn-outline-info btn-sm me-2',
                            'data-bs-toggle' => 'tooltip',
                            'title' => 'Exportar reporte completo del dashboard con todas las secciones'
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
            <span class="badge badge-soft-warning text-base px-3 py-1" id="porcentaje-unidades"><?= $porcentajeUnidades ?>%</span>
        </div>
    </div>

    <!-- Contenido KPI -->
    <div class="kpi-content mt-2">
        <div class="editable-value" onclick="editarValor('unidades')">
            <h3 class="kpi-value text-2xl font-extrabold text-gray-800" id="valor-unidades">
                <?= DashboardHelper::formatearNumero($metaUnidades) ?>
            </h3>
            <input type="number" class="edit-input d-none" id="input-unidades" value="<?= $metaUnidades ?>" min="0" step="1">
            <div class="edit-hint">Haz clic para editar</div>
        </div>
        <div class="progress-wrapper mt-2">
            <div class="progress progress-sm h-2 rounded-full overflow-hidden">
                <div class="progress-bar bg-warning" id="progress-unidades" style="width: <?= min($porcentajeUnidades, 100) ?>%"></div>
            </div>
            <small class="progress-text text-gray-600" id="texto-progress-unidades"><?= $porcentajeUnidades ?>% completado</small>
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
            <span class="badge badge-soft-success text-base px-3 py-1" id="porcentaje-dinero"><?= $porcentajeDinero ?>%</span>
        </div>
    </div>

    <!-- Contenido KPI -->
    <div class="kpi-content mt-2">
        <div class="editable-value" onclick="editarValor('dinero')">
            <h3 class="kpi-value text-2xl font-extrabold text-gray-800" id="valor-dinero">
                <?= DashboardHelper::formatearMoneda($metaDinero) ?>
            </h3>
            <input type="number" class="edit-input d-none" id="input-dinero" value="<?= $metaDinero ?>" min="0" step="0.01">
            <div class="edit-hint">Haz clic para editar</div>
        </div>
        <div class="progress-wrapper mt-2">
            <div class="progress progress-sm h-2 rounded-full overflow-hidden">
                <div class="progress-bar bg-success" id="progress-dinero" style="width: <?= min($porcentajeDinero, 100) ?>%"></div>
            </div>
            <small class="progress-text text-gray-600" id="texto-progress-dinero"><?= $porcentajeDinero ?>% completado</small>
        </div>
    </div>
</div>

<style>
.editable-value {
    cursor: pointer;
    position: relative;
    padding: 4px 8px;
    border-radius: 4px;
    transition: all 0.2s ease;
}

.editable-value:hover {
    background-color: #f9fafb;
    box-shadow: 0 0 0 1px #d1d5db;
}

.edit-input {
    border: 2px solid #3b82f6;
    border-radius: 4px;
    padding: 4px 8px;
    font-size: 1.5rem;
    font-weight: 800;
    background-color: white;
    outline: none;
    width: 100%;
}

.edit-input:focus {
    border-color: #1d4ed8;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.edit-hint {
    font-size: 0.75rem;
    color: #6b7280;
    margin-top: 4px;
    opacity: 0;
    transition: opacity 0.2s ease;
}

.editable-value:hover .edit-hint {
    opacity: 1;
}
</style>
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
                                        <div class="progress-enhanced">
                                            <div class="progress-bar bg-info" 
                                                 style="width: <?= (int)min($porcentajeUnidades, 100) ?>%;"></div>
                                        </div>

                                        <small>
                                            <?= DashboardHelper::formatearNumero($totalUnidades) ?> de <?= DashboardHelper::formatearNumero($metaUnidades) ?> unidades
                                        </small>
                                    </div>

                                    <div class="progress-item-expanded">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="progress-label">Ingresos Totales</span>
                                            <div class="progress-badges">
                                                <span class="badge bg-success"><?= (int)$porcentajeDinero ?>%</span>
                                            </div>
                                        </div>

                                        <div class="progress-enhanced">
                                        <div class="progress-bar bg-success" 
                                          style="width: <?= (int)min($porcentajeDinero, 100) ?>%;"></div>
                                    </div>
                                    <small>
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


        <!-- Performance Team - Todos los Asesores -->
<div class="col-xl-6 col-lg-6 mb-4">
    <div class="card card-pro">
        <div class="card-header card-header-pro">
            <h6 class="card-title mb-0">
                <i class="fas fa-users text-primary me-2"></i>
                Todos los Asesores de Ventas
            </h6>
            <small class="text-muted">Rendimiento completo del equipo</small>
        </div>
        <div class="card-body">
            <?php if (empty($conteoAsesores)): ?>
                <div class="empty-state-pro">
                    <i class="fas fa-users fa-2x text-muted mb-2"></i>
                    <p class="text-muted mb-0">No hay datos de asesores</p>
                </div>
            <?php else: ?>
                <!-- Resumen del equipo -->
                <div class="team-summary mb-3 p-3" style="background-color: #f8f9fa; border-radius: 8px;">
                    <div class="row text-center">
                        <div class="col-4">
                            <h6 class="mb-1 text-primary"><?= count($conteoAsesores) ?></h6>
                            <small class="text-muted">Asesores Activos</small>
                        </div>
                        <div class="col-4">
                            <h6 class="mb-1 text-success"><?= array_sum(array_column($conteoAsesores, 'cantidad')) ?></h6>
                            <small class="text-muted">Ventas Totales</small>
                        </div>
                        <div class="col-4">
                            <h6 class="mb-1 text-info"><?= DashboardHelper::formatearMoneda(array_sum(array_column($conteoAsesores, 'total_ventas'))) ?></h6>
                            <small class="text-muted">Total Equipo</small>
                        </div>
                    </div>
                </div>

                <!-- Lista completa con scroll -->
                <div class="ranking-list-pro" style="max-height: 400px; overflow-y: auto;">
                    <?php 
                    $totalAsesores = count($conteoAsesores);
                    $position = 1;
                    foreach ($conteoAsesores as $asesor): 
                        $colores = DashboardHelper::generarColorUnico($asesor['nombre']);
                        $promedioVenta = $asesor['total_ventas'] / $asesor['cantidad'];
                        
                        // Calcular badge de rendimiento
                        $maxVentas = max(array_column($conteoAsesores, 'total_ventas'));
                        $rendimientoPercent = ($asesor['total_ventas'] / $maxVentas) * 100;
                        
                        $badgeClass = 'bg-secondary';
                        $badgeText = 'Regular';
                        if ($rendimientoPercent >= 80) {
                            $badgeClass = 'bg-success';
                            $badgeText = 'Excelente';
                        } elseif ($rendimientoPercent >= 60) {
                            $badgeClass = 'bg-warning';
                            $badgeText = 'Bueno';
                        } elseif ($rendimientoPercent >= 40) {
                            $badgeClass = 'bg-info';
                            $badgeText = 'Promedio';
                        }
                    ?>
                        <div class="ranking-item-pro" style="border-left: 4px solid <?= $colores['bg'] ?>;">
                            <div class="ranking-position">
                                <span class="position-number" style="background-color: <?= $colores['bg'] ?>; color: <?= $colores['text'] ?>;">
                                    <?= $position ?>
                                </span>
                            </div>
                            <div class="ranking-details flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="ranking-title mb-1"><?= Html::encode($asesor['nombre']) ?></h6>
                                        <p class="ranking-subtitle text-muted mb-1">
                                            <?= $asesor['cantidad'] ?> ventas realizadas
                                        </p>
                                    </div>
                                    <span class="badge <?= $badgeClass ?> badge-sm"><?= $badgeText ?></span>
                                </div>
                                
                                <!-- Barra de progreso de rendimiento -->
                                <div class="progress mt-2" style="height: 4px;">
                                    <div class="progress-bar" 
                                         style="width: <?= $rendimientoPercent ?>%; background-color: <?= $colores['bg'] ?>;">
                                    </div>
                                </div>
                                <small class="text-muted"><?= round($rendimientoPercent, 1) ?>% de las ventas</small>
                            </div>
                            <div class="ranking-value text-end">
                                <span class="value-amount"><?= DashboardHelper::formatearMoneda($asesor['total_ventas']) ?></span>
                                <small class="text-muted d-block">
                                    Prom: <?= DashboardHelper::formatearMoneda($promedioVenta) ?>
                                </small>
                            </div>
                        </div>
                    <?php 
                        $position++;
                    endforeach; ?>
                </div>
                
                <!-- Footer con estadísticas adicionales -->
                <div class="mt-3 pt-3 border-top">
                    <div class="row text-center">
                        <div class="col-6">
                            <small class="text-muted">Promedio por asesor:</small>
                            <p class="mb-0 fw-bold"><?= DashboardHelper::formatearMoneda(array_sum(array_column($conteoAsesores, 'total_ventas')) / count($conteoAsesores)) ?></p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Ventas por asesor:</small>
                            <p class="mb-0 fw-bold"><?= round(array_sum(array_column($conteoAsesores, 'cantidad')) / count($conteoAsesores), 1) ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Estilos adicionales para el widget completo */
.ranking-list-pro {
    /* Scroll personalizado */
    scrollbar-width: thin;
    scrollbar-color: #cbd5e0 #f7fafc;
}

.ranking-list-pro::-webkit-scrollbar {
    width: 6px;
}

.ranking-list-pro::-webkit-scrollbar-track {
    background: #f7fafc;
    border-radius: 3px;
}

.ranking-list-pro::-webkit-scrollbar-thumb {
    background: #cbd5e0;
    border-radius: 3px;
}

.ranking-list-pro::-webkit-scrollbar-thumb:hover {
    background: #a0aec0;
}

.ranking-item-pro {
    transition: all 0.2s ease;
    margin-bottom: 12px;
}

.ranking-item-pro:hover {
    transform: translateX(2px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.badge-sm {
    font-size: 0.7rem;
    padding: 0.25rem 0.5rem;
}

.team-summary {
    border: 1px solid #e2e8f0;
}

.progress {
    background-color: #e2e8f0;
}
</style>

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


<script>
// Variables globales - valores iniciales desde PHP
const datosActuales = {
    unidades: <?= $totalUnidades ?>,
    dinero: <?= $totalDinero ?>
};

let editandoActualmente = null;
let guardandoEnProceso = false;

function formatearNumero(numero) {
    return new Intl.NumberFormat('es-ES').format(numero);
}

function formatearMoneda(numero) {
    return new Intl.NumberFormat('es-ES', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2
    }).format(numero);
}

function calcularPorcentaje(actual, meta) {
    return Math.min(Math.round((actual / meta) * 100), 100);
}

function actualizarKPI(tipo, nuevoValor = null) {
    const actual = datosActuales[tipo];
    const inputElement = document.getElementById(`input-${tipo}`);
    
    // Usar nuevo valor si se proporciona, sino usar el del input
    const meta = nuevoValor || parseFloat(inputElement.value);
    
    if (meta <= 0) return;
    
    const porcentaje = calcularPorcentaje(actual, meta);
    
    // Actualizar valor mostrado
    const valorElement = document.getElementById(`valor-${tipo}`);
    if (tipo === 'unidades') {
        valorElement.textContent = formatearNumero(meta);
    } else {
        valorElement.textContent = formatearMoneda(meta);
    }
    
    // Actualizar porcentaje
    document.getElementById(`porcentaje-${tipo}`).textContent = `${porcentaje}%`;
    
    // Actualizar barra de progreso
    const progressBar = document.getElementById(`progress-${tipo}`);
    progressBar.style.width = `${porcentaje}%`;
    
    // Actualizar texto de progreso
    document.getElementById(`texto-progress-${tipo}`).textContent = `${porcentaje}% completado`;
    
    // Actualizar el input con el nuevo valor
    inputElement.value = meta;
}

function editarValor(tipo) {
    if (editandoActualmente && editandoActualmente !== tipo) {
        cancelarEdicion(editandoActualmente);
    }
    
    editandoActualmente = tipo;
    
    const valorElement = document.getElementById(`valor-${tipo}`);
    const inputElement = document.getElementById(`input-${tipo}`);
    
    // Ocultar valor y mostrar input
    valorElement.classList.add('d-none');
    inputElement.classList.remove('d-none');
    
    // Enfocar el input
    inputElement.focus();
    inputElement.select();
    
    // Limpiar eventos anteriores
    inputElement.onblur = null;
    inputElement.onkeydown = null;
    
    // Variable para controlar si ya se guardó
    let yaGuardado = false;
    
    // Manejar eventos
    inputElement.onblur = () => {
        if (!yaGuardado && !guardandoEnProceso) {
            setTimeout(() => {
                if (!yaGuardado && editandoActualmente === tipo && !guardandoEnProceso) {
                    yaGuardado = true;
                    guardarValor(tipo);
                }
            }, 100);
        }
    };
    
    inputElement.onkeydown = (e) => {
        if (e.key === 'Enter' && !yaGuardado && !guardandoEnProceso) {
            e.preventDefault();
            yaGuardado = true;
            guardarValor(tipo);
        } else if (e.key === 'Escape') {
            e.preventDefault();
            yaGuardado = true; // Prevenir blur
            cancelarEdicion(tipo);
        }
    };
}

function guardarValor(tipo) {
    // Evitar múltiples llamadas simultáneas
    if (guardandoEnProceso) {
        return;
    }
    
    const valorElement = document.getElementById(`valor-${tipo}`);
    const inputElement = document.getElementById(`input-${tipo}`);
    
    const nuevoValor = parseFloat(inputElement.value);
    
    if (nuevoValor > 0) {
        guardandoEnProceso = true;
        
        // Mostrar indicador de carga
        mostrarCargando(tipo, true);
        
        // Guardar en la base de datos
        guardarEnBaseDatos(tipo, nuevoValor)
            .then(response => {
                if (response.success) {
                    // Actualizar KPI con el nuevo valor
                    actualizarKPI(tipo, nuevoValor);
                    
                    // Mostrar mensaje de éxito
                    mostrarMensaje(response.message || `Meta de ${tipo} actualizada correctamente`, 'success');
                } else {
                    // Restaurar valor anterior en caso de error
                    inputElement.value = tipo === 'unidades' ? <?= $metaUnidades ?> : <?= $metaDinero ?>;
                    mostrarMensaje(response.message || 'Error al actualizar la meta', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Restaurar valor anterior
                inputElement.value = tipo === 'unidades' ? <?= $metaUnidades ?> : <?= $metaDinero ?>;
                mostrarMensaje('Error de conexión. Intente nuevamente.', 'error');
            })
            .finally(() => {
                mostrarCargando(tipo, false);
                guardandoEnProceso = false;
            });
    } else {
        mostrarMensaje('El valor debe ser mayor a 0', 'error');
    }
    
    // Volver al estado normal
    inputElement.classList.add('d-none');
    valorElement.classList.remove('d-none');
    editandoActualmente = null;
}

function cancelarEdicion(tipo) {
    const valorElement = document.getElementById(`valor-${tipo}`);
    const inputElement = document.getElementById(`input-${tipo}`);
    
    // Restaurar valor original
    const metaOriginal = tipo === 'unidades' ? <?= $metaUnidades ?> : <?= $metaDinero ?>;
    inputElement.value = metaOriginal;
    
    // Volver al estado normal
    inputElement.classList.add('d-none');
    valorElement.classList.remove('d-none');
    editandoActualmente = null;
}

function guardarEnBaseDatos(tipo, valor) {
    // Determinar la URL según el tipo
    const urls = {
        'unidades': '<?= yii\helpers\Url::to(["actualizar-meta-unidades"]) ?>',
        'dinero': '<?= yii\helpers\Url::to(["actualizar-meta-dinero"]) ?>'
    };
    
    // Determinar el parámetro según el tipo
    const params = {
        'unidades': { 'meta_unidades': valor },
        'dinero': { 'meta_dinero': valor }
    };
    
    // Agregar CSRF token para Yii2
    const formData = new FormData();
    formData.append('_csrf', $('meta[name="csrf-token"]').attr('content'));
    
    // Agregar parámetros específicos
    Object.keys(params[tipo]).forEach(key => {
        formData.append(key, params[tipo][key]);
    });
    
    return fetch(urls[tipo], {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error de red');
        }
        return response.json();
    });
}

function mostrarCargando(tipo, mostrar) {
    const valorElement = document.getElementById(`valor-${tipo}`);
    
    if (mostrar) {
        valorElement.style.opacity = '0.5';
        valorElement.style.pointerEvents = 'none';
    } else {
        valorElement.style.opacity = '1';
        valorElement.style.pointerEvents = 'auto';
    }
}

function mostrarMensaje(mensaje, tipo) {
    // Usar el sistema de notificaciones de Yii2 si está disponible
    if (typeof krajeeDialog !== 'undefined') {
        if (tipo === 'success') {
            krajeeDialog.alert(mensaje, {type: krajeeDialog.TYPE_SUCCESS});
        } else {
            krajeeDialog.alert(mensaje, {type: krajeeDialog.TYPE_DANGER});
        }
        return;
    }
    
    // Si usas Toastr
    if (typeof toastr !== 'undefined') {
        if (tipo === 'success') {
            toastr.success(mensaje);
        } else {
            toastr.error(mensaje);
        }
        return;
    }
    
    // Si usas Bootstrap toasts
    if (typeof bootstrap !== 'undefined') {
        const toastContainer = document.getElementById('toast-container') || createToastContainer();
        
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white ${tipo === 'success' ? 'bg-success' : 'bg-danger'} border-0`;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${mensaje}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        
        toastContainer.appendChild(toast);
        const toastInstance = new bootstrap.Toast(toast);
        toastInstance.show();
        return;
    }
    
    // Fallback: alert simple
    alert(mensaje);
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    container.style.zIndex = '9999';
    document.body.appendChild(container);
    return container;
}

// Función para sincronizar datos desde el servidor (opcional)
function sincronizarMetas() {
    fetch('<?= yii\helpers\Url::to(["obtener-metas"]) ?>', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Actualizar datos globales
            datosActuales.unidades = data.data.total_unidades;
            datosActuales.dinero = data.data.total_dinero;
            
            // Actualizar KPIs
            actualizarKPI('unidades', data.data.meta_unidades);
            actualizarKPI('dinero', data.data.meta_dinero);
        }
    })
    .catch(error => {
        console.error('Error al sincronizar metas:', error);
    });
}

// Manejar clics fuera de los inputs para cancelar edición
document.addEventListener('click', function(e) {
    // Solo actuar si hay algo editándose y el clic no es dentro del área editable
    if (editandoActualmente && !e.target.closest('.editable-value') && !e.target.closest('.edit-input')) {
        // No llamar guardarValor aquí, el blur ya lo maneja
        // Solo cancelar la edición si se hace clic muy lejos
        setTimeout(() => {
            if (editandoActualmente && !guardandoEnProceso) {
                cancelarEdicion(editandoActualmente);
            }
        }, 150);
    }
});

// Inicializar cuando el documento esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Si quieres sincronizar automáticamente cada cierto tiempo
    // setInterval(sincronizarMetas, 60000); // Cada minuto
});
</script>
