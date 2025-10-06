<?php

use app\models\ProduccionSearch;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use app\widgets\CustomGridView;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Produccion';
$this->params['breadcrumbs'][] = $this->title;

// Función para generar colores únicos
function generarColorUnico($texto) {
    $textoLower = strtolower(trim($texto));
    if ($textoLower === 'urgente') {
        return ['bg' => '#dc3545', 'text' => '#ffffff'];
    }
    $coloresSuaves = [
        ['bg' => '#e3f2fd', 'text' => '#1565c0'], ['bg' => '#e8f5e8', 'text' => '#2e7d32'],
        ['bg' => '#fff3e0', 'text' => '#ef6c00'], ['bg' => '#f3e5f5', 'text' => '#7b1fa2'],
        ['bg' => '#e0f2f1', 'text' => '#00695c'], ['bg' => '#fce4ec', 'text' => '#c2185b'],
        ['bg' => '#f5f5f5', 'text' => '#424242'], ['bg' => '#e1f5fe', 'text' => '#0277bd'],
        ['bg' => '#fff8e1', 'text' => '#f57f17'], ['bg' => '#f9fbe7', 'text' => '#689f38'],
        ['bg' => '#fef7ff', 'text' => '#8e24aa'], ['bg' => '#e8eaf6', 'text' => '#3f51b5']
    ];
    $hash = crc32($texto);
    $indice = abs($hash) % count($coloresSuaves);
    return $coloresSuaves[$indice];
}

function getColorAvance($avance) {
    if ($avance <= 25) return '#dc3545';
    if ($avance <= 50) return '#ffc107';
    if ($avance <= 75) return '#17a2b8';
    return '#28a745';
}

// Obtener opciones de envío como array de objetos
$optionsEmpaquetadoArray = [];
foreach (\app\models\Catalogos::find()->where(['tipo'=>'empaquetado'])->all() as $opcion) {
    $optionsEmpaquetadoArray[] = ['id' => $opcion->id, 'nombre' => $opcion->nombre];
}
$optionsEmpaquetadoJson = htmlspecialchars(json_encode($optionsEmpaquetadoArray), ENT_QUOTES, 'UTF-8');

?>


<div class="produccion-index">

    <h1><?= Html::encode($this->title) ?></h1>
     <?php


$filtroEstatus = null;
if (isset($_GET['ProduccionSearch']['estatus'])) {
    $filtroEstatus = $_GET['ProduccionSearch']['estatus'];
}

// Obtener conteos usando el ProduccionSearch
$conteos = ProduccionSearch::getFiltrosConteos();
$totalRegistros = $conteos['total'];
$urgentesCount = $conteos['urgentes'];
$pendientesCount = $conteos['pendientes'];
$listosCount = $conteos['listos'];
$porVencerCount = $conteos['por_vencer'];


$db = Yii::$app->db;

// Total de letreros
$totalLetrerosCard = $db->createCommand('SELECT COUNT(*) FROM produccion')->queryScalar();

// Letreros producidos (con estatus "Listo")
$letrerosProducidos = $db->createCommand('
    SELECT COUNT(*) 
    FROM produccion p
    LEFT JOIN catalogos c ON p.empaquetado_id = c.id
    WHERE c.nombre = "Listo"
')->queryScalar();

// Productos por tipo de letrero
$productosPorTipo = $db->createCommand('
    SELECT 
        tl.nombre,
        COUNT(p.id) as total
    FROM produccion p
    LEFT JOIN catalogos tl ON p.tipo_letrero_id = tl.id
    WHERE tl.tipo = "tipo_letrero"
    GROUP BY tl.id, tl.nombre
    ORDER BY total DESC
    LIMIT 5
')->queryAll();

// Producidos a tiempo (listos y entregados antes o en la fecha límite)
$producidosATiempo = $db->createCommand('
    SELECT COUNT(*) 
    FROM produccion p
    LEFT JOIN ventas v ON p.venta_id = v.id
    LEFT JOIN catalogos c ON p.empaquetado_id = c.id
    WHERE c.nombre = "Listo" 
    AND v.fecha_entrega IS NOT NULL 
    AND v.fecha_entrega >= CURDATE()
')->queryScalar();

// Producidos con retrasos (listos pero que se entregaron después de la fecha límite)
$conRetraso = $db->createCommand('
    SELECT COUNT(*) 
    FROM produccion p
    LEFT JOIN ventas v ON p.venta_id = v.id
    LEFT JOIN catalogos c ON p.empaquetado_id = c.id
    WHERE c.nombre = "Listo" 
    AND v.fecha_entrega IS NOT NULL 
    AND v.fecha_entrega < CURDATE()
')->queryScalar();

// Crear array de estadísticas
$estadisticas = [
    'total_letreros' => (int)$totalLetrerosCard,
    'letreros_producidos' => (int)$letrerosProducidos,
    'productos_por_tipo' => $productosPorTipo ?: [],
    'producidos_a_tiempo' => (int)$producidosATiempo,
    'con_retraso' => (int)$conRetraso
];

// Calcular porcentajes
$porcentajeProducidos = $estadisticas['total_letreros'] > 0 ? 
    round(($estadisticas['letreros_producidos'] / $estadisticas['total_letreros']) * 100, 1) : 0;
$porcentajeATiempo = $estadisticas['total_letreros'] > 0 ? 
    round(($estadisticas['producidos_a_tiempo'] / $estadisticas['total_letreros']) * 100, 1) : 0;
$porcentajeRetraso = $estadisticas['total_letreros'] > 0 ? 
    round(($estadisticas['con_retraso'] / $estadisticas['total_letreros']) * 100, 1) : 0;
?>

<!-- Métricas Principales -->
<div class="row mb-4">
    <!-- Total de Letreros -->
    <div class="col-xl col-lg col-md-6 col-sm-12 mb-3">
        <div class="card border-left-primary shadow h-100 py-1">
            <div class="card-body" style="padding: 0.8rem;">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Letreros
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800" style="font-size: 1.1rem;">
                            <?= number_format($estadisticas['total_letreros']) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clipboard-list fa-lg text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Letreros Producidos -->
    <div class="col-xl col-lg col-md-6 col-sm-12 mb-3">
        <div class="card border-left-success shadow h-100 py-1">
            <div class="card-body" style="padding: 0.8rem;">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Producidos
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800" style="font-size: 1.1rem;">
                            <?= number_format($estadisticas['letreros_producidos']) ?>
                        </div>
                        <div class="text-xs text-muted">
                            <?= $porcentajeProducidos ?>%
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-lg text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Producidos a Tiempo -->
    <div class="col-xl col-lg col-md-6 col-sm-12 mb-3">
        <div class="card border-left-success shadow h-100 py-1">
            <div class="card-body" style="padding: 0.8rem;">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            A Tiempo
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800" style="font-size: 1.1rem;">
                            <?= number_format($estadisticas['producidos_a_tiempo']) ?>
                        </div>
                        <div class="text-xs text-muted">
                            <?= $porcentajeATiempo ?>%
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-lg text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Con Retraso -->
    <div class="col-xl col-lg col-md-6 col-sm-12 mb-3">
        <div class="card border-left-danger shadow h-100 py-1">
            <div class="card-body" style="padding: 0.8rem;">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            Producidos con Retrasos
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800" style="font-size: 1.1rem;">
                            <?= number_format($estadisticas['con_retraso']) ?>
                        </div>
                        <div class="text-xs text-muted">
                            <?= $porcentajeRetraso ?>%
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-lg text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Productos por Tipo -->
    <?php if (!empty($estadisticas['productos_por_tipo'])): ?>
    <div class="col-xl col-lg col-md-6 col-sm-12 mb-3">
        <div class="card border-left-info shadow h-100 py-1">
            <div class="card-body" style="padding: 0.8rem;">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Por Tipo
                        </div>
                        <div style="font-size: 0.7rem;">
                        <?php 
                        // Mostrar los 2 tipos más producidos para ahorrar espacio
                        $tiposMostrar = array_slice($estadisticas['productos_por_tipo'], 0, 2);
                        foreach ($tiposMostrar as $index => $tipo): 
                            $colores = generarColorUnico($tipo['nombre']);
                        ?>
                        <div class="d-flex justify-content-between align-items-center <?= $index < 1 ? 'mb-1' : '' ?>">
                            <span class="badge" style="background-color: <?= $colores['bg'] ?>; color: <?= $colores['text'] ?>; font-size: 0.6rem; padding: 0.2rem 0.4rem;">
                                <?= Html::encode(strlen($tipo['nombre']) > 8 ? substr($tipo['nombre'], 0, 8) . '...' : $tipo['nombre']) ?>
                            </span>
                            <span class="font-weight-bold text-gray-800" style="font-size: 0.7rem;">
                                <?= number_format($tipo['total']) ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                        
                        <?php if (count($estadisticas['productos_por_tipo']) > 2): ?>
                        <div class="text-center mt-1">
                            <small class="text-muted" style="font-size: 0.6rem;">
                                +<?= count($estadisticas['productos_por_tipo']) - 2 ?> más
                            </small>
                        </div>
                        <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-pie fa-lg text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
// CSS para las tarjetas de métricas
$this->registerCss("
/* Estilos para las tarjetas de métricas */
.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-danger {
    border-left: 0.25rem solid #e74a3b !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-left-secondary {
    border-left: 0.25rem solid #858796 !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.card {
    position: relative;
    display: flex;
    flex-direction: column;
    min-width: 0;
    word-wrap: break-word;
    background-color: #fff;
    background-clip: border-box;
    border: 1px solid #e3e6f0;
    border-radius: 0.35rem;
}

.shadow {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
}

.h-100 {
    height: 100% !important;
}

.py-2 {
    padding-top: 0.5rem !important;
    padding-bottom: 0.5rem !important;
}

.card-body {
    flex: 1 1 auto;
    min-height: 1px;
    padding: 1.25rem;
}

.row.no-gutters {
    margin-right: 0;
    margin-left: 0;
}

.row.no-gutters > .col,
.row.no-gutters > [class*=\"col-\"] {
    padding-right: 0;
    padding-left: 0;
}

.text-xs {
    font-size: 0.7rem;
}

.font-weight-bold {
    font-weight: 700 !important;
}

.text-uppercase {
    text-transform: uppercase !important;
}

.mb-1 {
    margin-bottom: 0.25rem !important;
}

.h5, .h6 {
    margin-bottom: 0.5rem;
    font-weight: 500;
    line-height: 1.2;
}

.h5 {
    font-size: 1.25rem;
}

.h6 {
    font-size: 1rem;
}

.mb-0 {
    margin-bottom: 0 !important;
}

.text-gray-800 {
    color: #5a5c69 !important;
}

.text-gray-300 {
    color: #dddfeb !important;
}

.text-success {
    color: #1cc88a !important;
}

.text-primary {
    color: #4e73df !important;
}

.text-danger {
    color: #e74a3b !important;
}

.text-info {
    color: #36b9cc !important;
}

.text-secondary {
    color: #858796 !important;
}

.text-warning {
    color: #f6c23e !important;
}

.text-muted {
    color: #858796 !important;
}

.col-auto {
    flex: 0 0 auto;
    width: auto;
    max-width: 100%;
}

.mr-2 {
    margin-right: 0.5rem !important;
}

.fa-2x {
    font-size: 2em;
}

/* Responsividad */
@media (max-width: 768px) {
    .col-xl-2.col-lg-3 {
        margin-bottom: 1rem;
    }
    
    .h5 {
        font-size: 1.1rem;
    }
    
    .h6 {
        font-size: 0.9rem;
    }
    
    .fa-2x {
        font-size: 1.5em;
    }
}
");
?>

<?php
// Función para mantener otros filtros en las URLs
function buildFilterUrl($newFilters = []) {
    $currentParams = Yii::$app->request->queryParams;

    // Remover parámetros de paginación para reset
    unset($currentParams['page']);

    $params = array_merge($currentParams, $newFilters);

    // Remover parámetros vacíos o null
    foreach ($params as $key => $value) {
        if (is_array($value)) {
            foreach ($value as $subKey => $subValue) {
                if ($subValue === null || $subValue === '' || $subValue === 'todos') {
                    unset($params[$key][$subKey]);
                }
            }
            if (empty($params[$key])) {
                unset($params[$key]);
            }
        } else {
            if ($value === null || $value === '' || $value === 'todos') {
                unset($params[$key]);
            }
        }
    }

    return Url::current($params);
}
?>

<div class="filtros-produccion mb-4 d-flex justify-content-between align-items-center">
    <!-- Filtros a la izquierda -->
    <div class="d-flex align-items-center gap-2">
        <!-- Botón Todos -->
        <a href="<?= Url::to(['produccion/index']) ?>" 
           class="btn-filtro <?= !$filtroEstatus ? 'active' : '' ?>">
            Todos (<?= $totalRegistros ?>)
        </a>

        <!-- Filtro Urgentes -->
        <a href="<?= Url::to(['produccion/index', 'ProduccionSearch[estatus]' => 'urgente']) ?>" 
           class="btn-filtro btn-danger <?= $filtroEstatus == 'urgente' ? 'active' : '' ?>">
             Urgentes (<?= $urgentesCount ?>)
        </a>

        <!-- Filtro Por vencer -->
        <a href="<?= Url::to(['produccion/index', 'ProduccionSearch[estatus]' => 'por_vencer']) ?>" 
           class="btn-filtro btn-warning <?= $filtroEstatus == 'por_vencer' ? 'active' : '' ?>">
            🔥 Por vencer (<?= $porVencerCount ?>)
        </a>

        <!-- Filtro Pendientes -->
        <a href="<?= Url::to(['produccion/index', 'ProduccionSearch[estatus]' => 'pendiente']) ?>" 
           class="btn-filtro btn-pendiente <?= $filtroEstatus == 'pendiente' ? 'active' : '' ?>">
            Pendientes (<?= $pendientesCount ?>)
        </a>

        <!-- Filtro Listos -->
        <a href="<?= Url::to(['produccion/index', 'ProduccionSearch[estatus]' => 'listo']) ?>" 
           class="btn-filtro btn-listo <?= $filtroEstatus == 'listo' ? 'active' : '' ?>">
            Listos (<?= $listosCount ?>)
        </a>
    </div>

    <!-- Botón Exportar a la derecha -->
    <div>
        <?= Html::a('<i class="fas fa-file-excel me-2"></i>Exportar a Excel', 
            ['export-excel'], 
            [
                'class' => 'btn btn-light border btn-sm',
                'title' => 'Descargar todos los datos en Excel',
                'data-bs-toggle' => 'tooltip',
                'data-bs-placement' => 'top'
            ]) 
        ?>
    </div>
</div>
    

    <?= CustomGridView::widget([
        'dataProvider' => $dataProvider,
        'summary' => false,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'tipo_letrero_id',
                'format' => 'raw',
                'value' => function($model) {
                    if (!$model->tipoLetrero) return '<span class="badge bg-secondary">No definido</span>';
                    $colores = generarColorUnico($model->tipoLetrero->nombre);
                    return '<span class="badge" style="background-color: '.$colores['bg'].'; color: '.$colores['text'].';">'.$model->tipoLetrero->nombre.'</span>';
                },
                'label' => 'Tipo Letrero'
            ],
            [
                'attribute' => 'nombre_letrero',
                'label' => 'Nombre Letrero',
                'value' => function ($model) {
                    return $model->nombre_letrero;
                },
            ],
            [
                'format' => 'raw',
                'value' => function($model) {
                    $entregaNombre = $model->venta->entrega->nombre ?? null;
                    
                    if (!$entregaNombre) {
                        return '<span class="badge bg-secondary">No definido</span>';
                   }

                    $colores = generarColorUnico($entregaNombre);
                    return '<span class="badge" style="background-color: ' . $colores['bg'] . '; color: ' . $colores['text'] . ';">' . 
                           $entregaNombre . '</span>';
                },
                'label' => 'Entrega'
            ],

            [
                'attribute' => 'disenador_id',
                'format' => 'raw',
                'value' => function($model) {
                    $nombre = $model->disenador->nombre ?? 'Sin asignar';
                    $colores = generarColorUnico($nombre);
                    return "<span class='badge' style='background-color:{$colores['bg']};color:{$colores['text']}'>{$nombre}</span>";
                },
                'label' => 'Diseñador'
            ],

            'unidades',

            [
                'attribute' => 'diseno_impresion',
                'format' => 'raw',
                'label' => 'Diseño Impresión',
                'value' => function($model){
                    $checked = ($model->diseno_impresion == 1 || $model->no_impresion == 1);
                    return Html::tag('span', $checked ? '✔' : '✖', [
                        'class' => 'toggle-icon ' . ($checked ? 'checked' : 'unchecked'),
                        'data-id' => $model->id,
                        'data-field' => 'diseno_impresion',
                        'style' => 'cursor:pointer',
                    ]);
                }
            ],

            [
                'attribute' => 'corte_listo',
                'format' => 'raw',
                'label' => 'Corte',
                'value' => function($model){
                    $checked = $model->corte_listo == 1;
                    return Html::tag('span', $checked ? '✔' : '✖', [
                        'class' => 'toggle-icon ' . ($checked ? 'checked' : 'unchecked'),
                        'data-id' => $model->id,
                        'data-field' => 'corte_listo',
                        'style' => 'cursor:pointer',
                    ]);
                }
            ],

            [
                'attribute' => 'fabricacion_listo',
                'format' => 'raw',
                'label' => 'Fabricación',
                'value' => function($model){
                    $checked = $model->fabricacion_listo == 1;
                    return Html::tag('span', $checked ? '✔' : '✖', [
                        'class' => 'toggle-icon ' . ($checked ? 'checked' : 'unchecked'),
                        'data-id' => $model->id,
                        'data-field' => 'fabricacion_listo',
                        'style' => 'cursor:pointer',
                    ]);
                }
            ],

            [
                'attribute' => 'fecha_entrega',
                'label' => 'Fecha de Entrega',
                'format' => 'raw',
                'value' => function($model) {
                    if ($model->venta && $model->venta->fecha_entrega) {
                        return Yii::$app->formatter->asDate($model->venta->fecha_entrega, 'php:d/m/Y');
                    } else {
                        return '<span class="badge bg-secondary">No definida</span>';
                    }
                },
            ],


            [
            'attribute' => 'dias_restantes',
            'label' => 'Días Restantes',
            'format' => 'raw',
            'value' => function($model) {

        // ⚠️ Si el empaquetado está listo
        $nombreEmpaquetado = $model['empaquetado']['nombre'] ?? null;
        if (strtolower($nombreEmpaquetado) === 'listo') {
            $fechaEntrega = isset($model['venta']['fecha_entrega']) ? new \DateTime($model['venta']['fecha_entrega']) : null;
            $fechaEntrega?->setTime(0,0,0);
            $hoy = new \DateTime();
            $hoy->setTime(0,0,0);

            if ($fechaEntrega && $fechaEntrega >= $hoy) {
                return '<span class="badge bg-success">Fabricado a tiempo</span>';
            } elseif ($fechaEntrega && $fechaEntrega < $hoy) {
                return '<span class="badge bg-danger">Fabricado con retraso</span>';
            } else {
                return '<span class="badge bg-success">Listo</span>';
            }
        }

        // Si no está listo, contamos días restantes o de retraso
        if ($model->venta && $model->venta['fecha_entrega']) {

            $hoy = new \DateTime();
            $hoy->setTime(0,0,0);

            $fechaEntrega = new \DateTime($model['venta']['fecha_entrega']);
            $fechaEntrega->setTime(0,0,0);

            $diasHabiles = 0;
            $tipo = '';

            if ($fechaEntrega < $hoy) {
                $fechaIter = clone $fechaEntrega;
                while ($fechaIter < $hoy) {
                    $diaSemana = (int)$fechaIter->format('N');
                    if ($diaSemana < 6) { 
                        $diasHabiles++;
                    }
                    $fechaIter->modify('+1 day');
                }
                $tipo = 'de retraso';
            } else {
                $fechaIter = clone $hoy;
                while ($fechaIter <= $fechaEntrega) {
                    $diaSemana = (int)$fechaIter->format('N');
                    if ($diaSemana < 6) {
                        $diasHabiles++;
                    }
                    $fechaIter->modify('+1 day');
                }
                $tipo = 'restantes';
            }

            // 🎨 Color del badge
            if ($tipo === 'de retraso') {
                $color = 'bg-danger';
            } elseif ($diasHabiles > 5) {
                $color = 'bg-success';
            } elseif ($diasHabiles >= 1) {
                $color = 'bg-warning text-dark';
            } else {
                $color = 'bg-danger';
            }

            return '<span class="badge '.$color.'">'
                .$diasHabiles.' día'.($diasHabiles == 1 ? '' : 's').' '.$tipo.
                '</span>';

        } else {
            return '<span class="badge bg-secondary">No definida</span>';
        }
    },
],


                      // Editable Empaquetado
                      [
              'attribute' => 'empaquetado_id',
              'format' => 'raw',
              'label' => 'Estatus',
              'value' => function($model) {
                            $nombre = $model->empaquetado ? $model->empaquetado->nombre : 'Pendiente';
                  $color = strtolower($nombre) === 'listo' ? 'green' : 'red';
                  return "<span class='badge' style='background-color:{$color}; color:white;'>{$nombre}</span>";
              }
          ],

        ],
    ]); ?>

</div>

<?php
$this->registerCss("
.toggle-icon {
    font-weight: bold;
    color: #fff;
    border-radius: 4px;
    width: 28px;
    height: 28px;
    line-height: 28px;
    text-align: center;
    display: inline-block;
    font-size: 16px;
    transition: all 0.3s ease;
}
.toggle-icon.checked { background-color: #28a745; }
.toggle-icon.unchecked { background-color: #dc3545; }

<style>
.filtros-diseño {
    background-color: #f8f9fa;
    padding: 8px 12px; /* más compacto */
    border-radius: 6px;
    border: 1px solid #e9ecef;
}

.btn-filtro {
    background-color: #ffffff;
    border: 1px solid #dee2e6;
    color: #6c757d;
    padding: 4px 10px; /* reducido */
    border-radius: 4px; /* más discreto */
    text-decoration: none;
    font-size: 12px; /* más pequeño */
    font-weight: 500;
    transition: all 0.2s ease;
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    cursor: pointer;
}

.btn-filtro:hover {
    background-color: #e9ecef;
    border-color: #adb5bd;
    color: #495057;
    text-decoration: none;
}

.btn-filtro.active {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: #ffffff;
}

.btn-urgente.active {
    background-color: #dc3545;
    border-color: #dc3545;
    color: #ffffff;
}

.btn-urgente.active:hover {
    background-color: #c82333;
    border-color: #bd2130;
}

.btn-pendiente.active {
    background-color: #ffc107;
    border-color: #ffc107;
    color: #000000;
}

.btn-pendiente.active:hover {
    background-color: #e0a800;
    border-color: #d39e00;
}

.btn-listo.active {
    background-color: #28a745;
    border-color: #28a745;
    color: #ffffff;
}

.btn-listo.active:hover {
    background-color: #218838;
    border-color: #1e7e34;
}

/* Responsive */
@media (max-width: 768px) {
    .filtros-diseño .d-flex {
        flex-wrap: wrap;
    }
    
    .btn-filtro {
        margin-bottom: 5px;
        font-size: 11px;
        padding: 3px 8px;
    }
}
</style>
");


$this->registerJs(<<<JS
$(document).on('click', '.editable-empaquetado', function(e) {
    e.stopPropagation();
    var div = $(this);
    var recordId = div.data('record-id');
    var fieldName = div.data('field-name');

    var options = div.data('options');
    if (typeof options === 'string') {
        options = JSON.parse(options);
    }

    if (div.find('select').length) return;

    var select = $('<select class="form-select form-select-sm"></select>');
    $.each(options, function(index, option) {
        var selected = (div.find('span').text().trim() === option.nombre) ? 'selected' : '';
        select.append('<option value="'+option.id+'" '+selected+'>'+option.nombre+'</option>');
    });

    div.html(select);
    select.focus();

    select.on('change', function() {
        var newValue = $(this).val();
        var newText = $(this).find('option:selected').text();

        $.post('index.php?r=produccion/update-empaquetado', {
            id: recordId,
            field: fieldName,
            value: newValue
        }, function(response) {
            if(response === 'ok') {
                var color = newText.toLowerCase() === 'listo' ? 'green' : 'red';
                div.html('<span class="badge" style="background-color:'+color+'; color:white;">'+newText+'</span>');
            } else {
                alert('Error al actualizar');
            }
        });
    });
});
JS
);
?>


