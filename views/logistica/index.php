<?php

use app\models\LogisticaSearch;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use app\widgets\CustomGridView;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Logistica';
$this->params['breadcrumbs'][] = $this->title;

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

// Opciones de envío
$optionsEnvioArray = [];
foreach (\app\models\Catalogos::find()->where(['tipo'=>'envio'])->all() as $opcion) {
    $optionsEnvioArray[] = ['id' => $opcion->id, 'nombre' => $opcion->nombre];
}
$optionsEnvioJson = htmlspecialchars(json_encode($optionsEnvioArray), ENT_QUOTES, 'UTF-8');

// Opciones de pago
$optionsPagoArray = [];
foreach (\app\models\Catalogos::find()->where(['tipo'=>'estatus_pago'])->all() as $opcion) {
    $optionsPagoArray[] = ['id' => $opcion->id, 'nombre' => $opcion->nombre];
}
$optionsPagoJson = htmlspecialchars(json_encode($optionsPagoArray), ENT_QUOTES, 'UTF-8');

?>


<?php 
$filtroPago = $searchModel->filtro_pago; 
$filtroEnvio = $searchModel->filtro_envio; 
$filtroFecha = $searchModel->filtro_fecha;  
$conteos = LogisticaSearch::getFiltrosConteos(); 
?>

<div class="logistica-index">
    <h1><?= Html::encode($this->title) ?></h1>
     <!-- Métricas Principales -->
    <div class="row mb-4">
        <!-- Total Anticipos -->
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Anticipos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                $<?= number_format($metrics['total_anticipos'], 2) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Total Restantes -->
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Total Pendiente
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                $<?= number_format($metrics['total_restantes'], 2) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Liquidado -->
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Liquidado
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                $<?= number_format($metrics['total_liquidado'], 2) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Paquetes a Tiempo -->
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Enviados a Tiempo
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $metrics['enviados_a_tiempo'] ?>
                            </div>
                            <div class="text-xs text-muted">
                                <?= number_format($metrics['porcentaje_a_tiempo'], 1) ?>%
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Paquetes con Retraso -->
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Con Retraso
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $metrics['enviados_con_retraso'] ?>
                            </div>
                            <div class="text-xs text-muted">
                                <?= number_format($metrics['porcentaje_retraso'], 1) ?>%
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tu contenido existente de la tabla GridView va aquí -->
    <?php // ... resto de tu código existente ... ?>

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

.h5 {
    font-size: 1.25rem;
    margin-bottom: 0.5rem;
    font-weight: 500;
    line-height: 1.2;
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
    .col-xl-3.col-md-6 {
        margin-bottom: 1rem;
    }
    
    .h5 {
        font-size: 1.1rem;
    }
    
    .fa-2x {
        font-size: 1.5em;
    }
}
");
?>

    <div class="filtros-logistica mb-4 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <!-- Todos -->
            <a href="<?= Url::to(['logistica/index']) ?>"
                class="btn-filtro <?= !$filtroPago && !$filtroEnvio && !$filtroFecha ? 'active' : '' ?>">
                Todos (<?= $conteos['total'] ?>)
            </a>

            <!-- Liquidados -->
            <a href="<?= Url::to(['logistica/index', 'LogisticaSearch[filtro_pago]' => 'Liquidado']) ?>"
               class="btn-filtro btn-success <?= $filtroPago == 'Liquidado' ? 'active' : '' ?>">
                Liquidados (<?= $conteos['liquidados'] ?>)
            </a>

            <!-- Por Liquidar -->
            <a href="<?= Url::to(['logistica/index', 'LogisticaSearch[filtro_pago]' => 'Por Liquidar']) ?>"
               class="btn-filtro btn-warning <?= $filtroPago == 'Por Liquidar' ? 'active' : '' ?>">
                Por Liquidar (<?= $conteos['por_liquidar'] ?>)
            </a>

            <!-- Enviados -->
            <a href="<?= Url::to(['logistica/index', 'LogisticaSearch[filtro_envio]' => 'Enviado']) ?>"
               class="btn-filtro btn-info <?= $filtroEnvio == 'Enviado' ? 'active' : '' ?>">
                Enviados (<?= $conteos['enviados'] ?>)
            </a>

            <!-- Pendientes de Envío -->
            <a href="<?= Url::to(['logistica/index', 'LogisticaSearch[filtro_envio]' => 'Pendiente']) ?>"
               class="btn-filtro btn-secondary <?= $filtroEnvio == 'Pendiente' ? 'active' : '' ?>">
                Pend. Envío (<?= $conteos['envios_pendientes'] ?>)
            </a>

            <!-- Urgentes -->
            <a href="<?= Url::to(['logistica/index', 'LogisticaSearch[filtro_fecha]' => 'urgente']) ?>"
               class="btn-filtro btn-urgente <?= $filtroFecha == 'urgente' ? 'active' : '' ?>">
                Urgentes 🔥 (<?= $conteos['urgentes'] ?>)
            </a>

            <!-- Retrasados -->
            <a href="<?= Url::to(['logistica/index', 'LogisticaSearch[filtro_fecha]' => 'retrasado']) ?>"
               class="btn-filtro btn-retrasado <?= $filtroFecha == 'retrasado' ? 'active' : '' ?>">
                Retrasados (<?= $conteos['retrasados'] ?>)
            </a>
        </div>
        
        <div>
            <?= Html::a('<i class="fas fa-file-excel me-2"></i>Exportar a Excel',
                ['export-excel'], ['class'=>'btn btn-light border btn-sm']) ?>
        </div>
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
            'nombre_letrero',
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
            'attribute' => 'extras',
            'format' => 'raw',
            'value' => function($model) {
                $extras = $model->venta ? $model->venta->extras : [];
                $badges = '';
                foreach ($extras as $extra) {
                    $colores = generarColorUnico($extra->nombre);
                    $badges .= '<span class="badge me-1 mb-1" style="background-color:'.$colores['bg'].';color:'.$colores['text'].';">'.$extra->nombre.'</span>';
                }
                if (empty($badges)) $badges = '<span class="badge bg-light text-dark">Ninguno</span>';
        
                return $badges; // Solo mostrar, sin div editable
            },
            'label' => 'Extras'
            ],

            'telefono',
            [
              'attribute' => 'total',
              'format' => 'raw',
              'value' => function($model) {
                  $valor = $model->total ?? 0;
                  $mostrar = '$' . number_format($valor, 2);
                  return "<div class=\"editable-field\" style=\"color:green; font-weight:bold;\" data-field-type=\"number\" data-field-name=\"total\" data-record-id=\"{$model->id}\" data-current-value=\"{$valor}\" title=\"Click para editar\">{$mostrar}</div>";
              },
              'label' => 'Total'
            ],
            [
                'attribute' => 'anticipo',
                'format' => 'raw',
                'value' => function($model) {
                    $valor = $model->anticipo ?? 0;
                    $mostrar = '$' . number_format($valor, 2);
                    return "<div class=\"editable-field\" style=\"color:orange; font-weight:bold;\" data-field-type=\"number\" data-field-name=\"anticipo\" data-record-id=\"{$model->id}\" data-current-value=\"{$valor}\" title=\"Click para editar\">{$mostrar}</div>";
                },
                'label' => 'Anticipo'
            ],
            [
                'attribute' => 'restante',
                'format' => 'raw',
                'value' => function($model) {
                    $valor = $model->restante ?? 0;
                    $mostrar = '$' . number_format($valor, 2);
                    return "<div class=\"editable-field\" style=\"color:red; font-weight:bold;\" data-field-type=\"number\" data-field-name=\"restante\" data-record-id=\"{$model->id}\" data-current-value=\"{$valor}\" title=\"Click para editar\">{$mostrar}</div>";
                },
                'label' => 'Restante'
            ],
            [
                'attribute' => 'estatus_pago_id',
                'format' => 'raw',
                            'label' => 'Estatus Pago',
                'value' => function($model) {
                    $nombre = $model->estatusPago->nombre ?? 'Pendiente'; // <- usando la relación
                    $color = strtolower($nombre) === 'liquidado' ? '#28a745' : '#ffc107';
            
                    return "<span class='badge' style='background-color:{$color}; color:white;'>{$nombre}</span>";
                },
            ],

            [
                 'attribute' => 'estatus_envio_id',
                 'format' => 'raw',
                 'label' => 'Estatus Envío',
                 'value' => function($model) {
                     $nombre = $model->envio->nombre ?? 'Pendiente';
                     $color = strtolower($nombre) === 'enviado' ? 'green' : 'red';
             
                     return "<span class='badge' style='background-color:{$color}; color:white;'>{$nombre}</span>";
                 },
            ],

        ],
    ]); ?>
</div>

<?php
$this->registerJs(<<<'JS'
// --- JS Completo para edición en línea y select editable ---
$(document).ready(function() {
    let editingCell = null;

    function actualizarRestante(recordId, total, anticipo){
        let restante = Math.max(0, total - anticipo);
        let restanteHtml = '<span class="badge" style="color:red; font-weight:bold;">$' + restante.toFixed(2) + '</span>';
        $('.editable-field[data-field-name="restante"][data-record-id="'+recordId+'"]').each(function(){
            $(this).html(restanteHtml);
            $(this).data('current-value', restante);
        });
        $.post('index.php?r=ventas/actualizar-restante', 
            {id: recordId, _csrf: yii.getCsrfToken()}, 
            function(res){
                if(res.success){
                    console.log('Restante sincronizado en Ventas:', res.restante);
                }
            }, 'json'
        );
    }

    $(document).off('click.editable').on('click.editable', '.editable-field', function(e){
        e.preventDefault(); e.stopPropagation();
        if(editingCell!==null) return;
        let cell = $(this);
        let fieldType = cell.data('field-type');
        let fieldName = cell.data('field-name');
        let recordId = cell.data('record-id');
        let currentValue = cell.data('current-value');
        editingCell = cell;
        cell.addClass('editing');
        let originalContent = cell.html();
        let inputType = fieldType === 'number' ? 'number' : 'text';
        let step = fieldType === 'number' ? 'step="0.01"' : '';
        let inputElement = '<input type="'+inputType+'" class="inline-input form-control" value="'+(currentValue || '')+'" '+step+'>';
        let editContainer = '<div class="edit-container" style="min-width:200px;">'+
                            inputElement+
                            '<div class="save-cancel-buttons mt-2 d-flex gap-2">'+
                            '<button type="button" class="btn btn-success btn-sm save-btn" data-record-id="'+recordId+'" data-field-name="'+fieldName+'">Guardar</button>'+
                            '<button type="button" class="btn btn-secondary btn-sm cancel-btn">Cancelar</button>'+
                            '</div></div>';
        cell.html(editContainer);
        setTimeout(()=>cell.find('.inline-input').focus(),100);

        cell.off('click.save').on('click.save', '.save-btn', function(e){
            e.preventDefault(); e.stopPropagation();
            let newValue = fieldType === 'number' ? parseFloat(cell.find('.inline-input').val()) || 0 : cell.find('.inline-input').val();
            $.ajax({
                url: 'index.php?r=logistica/update-field',
                type: 'POST',
                dataType: 'json',
                data: {id: recordId, field: fieldName, value: newValue, _csrf: yii.getCsrfToken()},
                success: function(response){
                    if(response && response.success){
                        cell.data('current-value', newValue);
                        cell.html(response.newContent || newValue);
                        cell.removeClass('editing'); editingCell = null;
                        if(fieldName==='total' || fieldName==='anticipo'){
                            let total = fieldName==='total' ? newValue : parseFloat($('.editable-field[data-field-name="total"][data-record-id="'+recordId+'"]').data('current-value')) || 0;
                            let anticipo = fieldName==='anticipo' ? newValue : parseFloat($('.editable-field[data-field-name="anticipo"][data-record-id="'+recordId+'"]').data('current-value')) || 0;
                            actualizarRestante(recordId, total, anticipo);
                        }
                    } else {
                        console.error(response.errors || response.message);
                        alert('Error: ' + (response.message || 'No se pudo actualizar'));
                        cell.html(originalContent); cell.removeClass('editing'); editingCell = null;
                    }
                },
                error: function(xhr,status,error){
                    console.error('Error AJAX:',xhr.responseText,status,error);
                    alert('Error de conexión');
                    cell.html(originalContent); cell.removeClass('editing'); editingCell = null;
                }
            });
        });

        cell.off('click.cancel').on('click.cancel', '.cancel-btn', function(e){
            e.preventDefault(); e.stopPropagation();
            cell.html(originalContent); cell.removeClass('editing'); editingCell = null;
        });

        cell.off('keydown.edit').on('keydown.edit', '.inline-input', function(e){
            if(e.keyCode===13) cell.find('.save-btn').click();
            else if(e.keyCode===27) cell.find('.cancel-btn').click();
        });
    });


});
JS
);

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
