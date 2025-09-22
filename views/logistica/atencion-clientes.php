<?php

use yii\helpers\Html;
use app\widgets\CustomGridView;

$this->title = 'Atención a Clientes';
$this->params['breadcrumbs'][] = $this->title;

// Generar colores como en producción
function generarColorUnico($texto) {
    $textoLower = strtolower(trim($texto));
    if ($textoLower === 'urgente') return ['bg'=>'#dc3545','text'=>'#fff'];

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

<div class="atencion-clientes-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <!-- Barra de encabezados de sección -->
    <div class="section-headers-bar">
        <div class="section-header basic-info-header">
            <span>INFORMACIÓN BÁSICA</span>
        </div>
        <div class="section-header design-header">
            <span>DISEÑO</span>
        </div>
        <div class="section-header production-header">
            <span>PRODUCCIÓN</span>
        </div>
    </div>
    <!-- Tabla con encabezados simples -->
    <?= CustomGridView::widget([
        'dataProvider' => $dataProvider,
        'summary' => false,
        'tableOptions' => ['class' => 'table table-striped table-bordered custom-table'],
        'headerRowOptions' => ['class' => 'table-header'],
        'columns' => [
            ['class'=>'yii\grid\SerialColumn'],

            // Información básica
            [
                'attribute'=>'tipo_letrero',
                'format'=>'raw',
                'label' => 'Tipo Letrero',
                'headerOptions' => ['class' => 'basic-info-col'],
                'value'=>function($model){
                    $colores = generarColorUnico($model['tipo_letrero']);
                    return '<span class="badge tipo-badge" style="background-color:'.$colores['bg'].'; color:'.$colores['text'].'">'.$model['tipo_letrero'].'</span>';
                }
            ],
            [
                'attribute' => 'nombre_letrero',
                'label' => 'Nombre Letrero',
                'headerOptions' => ['class' => 'basic-info-col'],
            ],
            [
                'attribute' => 'telefono',
                'label' => 'Teléfono',
                'headerOptions' => ['class' => 'basic-info-col'],
            ],

            // DISEÑO
            [
                'attribute'=>'contacto_cliente',
                'format'=>'raw',
                'label' => 'Contacto',
                'headerOptions' => ['class' => 'design-col'],
                'value'=>function($model){
                    $completed = $model['contacto_cliente']==1;
                    $color = $completed ? '#4CAF50' : '#f44336';
                    $icon = $completed ? '✓' : '✗';
                    return Html::tag('div',$icon,[
                        'class' => 'status-circle',
                        'style'=>"background-color:$color;"
                    ]);
                }
            ],
            [
                'attribute'=>'vectorizado',
                'format'=>'raw',
                'label' => 'Vectorizado',
                'headerOptions' => ['class' => 'design-col'],
                'value'=>function($model){
                    $completed = $model['vectorizado']==1;
                    $color = $completed ? '#FF9800' : '#f44336';
                    $icon = $completed ? '✓' : '✗';
                    return Html::tag('div',$icon,[
                        'class' => 'status-circle',
                        'style'=>"background-color:$color;"
                    ]);
                }
            ],
            [
                'attribute'=>'estatus',
                'label' => 'Estatus Diseño',
                'headerOptions' => ['class' => 'design-col'],
                'format'=>'raw',
                'value'=>function($model){
                    $colores = match($model['estatus']){
                        'Pendiente'=>['bg'=>'#dc3545','text'=>'#fff'],
                        'Listo'=>['bg'=>'#28a745','text'=>'#fff'],
                        default=>generarColorUnico($model['estatus'])
                    };
                    return Html::tag('span',$model['estatus'],['class'=>'badge status-badge','style'=>"background-color:{$colores['bg']};color:{$colores['text']}"]);
                }
            ],
            [
                'attribute' => 'fecha_confirmacion_diseno',
                'format' => 'raw',
                'label' => 'Fecha Confirmación',
                'headerOptions' => ['class' => 'design-col'],
                'value' => function($model) {
                    if (empty($model['fecha_confirmacion_diseno'])) {
                        return '<span class="badge bg-warning text-dark fecha-badge">Pendiente</span>';
                    } else {
                        return '<span class="badge bg-info fecha-badge">' . Yii::$app->formatter->asDate($model['fecha_confirmacion_diseno'], 'php:d/m/Y') . '</span>';
                    }
                },
            ],

            // PRODUCCIÓN
            [
                'attribute'=>'diseno_impresion',
                'label' => 'Impresión',
                'headerOptions' => ['class' => 'production-col'],
                'format'=>'raw',
                'value'=>function($model){
                    $checked = $model['diseno_impresion']==1;
                    $color = $checked ? '#28a745' : '#dc3545';
                    $icon = $checked ? '✔' : '✖';
                    return Html::tag('div',$icon,[
                        'class' => 'status-square',
                        'style'=>"background-color:$color;"
                    ]);
                }
            ],
            [
                'attribute'=>'corte_listo',
                'label' => 'Corte',
                'headerOptions' => ['class' => 'production-col'],
                'format'=>'raw',
                'value'=>function($model){
                    $checked = $model['corte_listo']==1;
                    $color = $checked ? '#28a745' : '#dc3545';
                    $icon = $checked ? '✔' : '✖';
                    return Html::tag('div',$icon,[
                        'class' => 'status-square',
                        'style'=>"background-color:$color;"
                    ]);
                }
            ],
            [
                'attribute'=>'fabricacion_listo',
                'label' => 'Fabricación',
                'headerOptions' => ['class' => 'production-col'],
                'format'=>'raw',
                'value'=>function($model){
                    $checked = $model['fabricacion_listo']==1;
                    $color = $checked ? '#28a745' : '#dc3545';
                    $icon = $checked ? '✔' : '✖';
                    return Html::tag('div',$icon,[
                        'class' => 'status-square',
                        'style'=>"background-color:$color;"
                    ]);
                }
            ],
            [
                'attribute'=>'empaquetado_nombre',
                'format'=>'raw',
                'label'=>'Estatus Produccion',
                'headerOptions' => ['class' => 'production-col'],
                'value'=>function($model){
                    $nombre = $model['empaquetado_nombre'] ?? 'Pendiente';
                    $color = strtolower($nombre)=='listo para empaquetar'?'#28a745':'#dc3545';
                    return "<span class='badge status-badge' style='background-color:{$color}; color:white;'>{$nombre}</span>";
                }
            ],

            [
                'attribute' => 'restante',
                'format' => 'raw',
                'label' => 'Restante',
                'value' => function($model) {
                    $valor = $model['restante'] ?? 0;
                    $mostrar = '$' . number_format($valor, 2);
                    return "<div style=\"color:red; font-weight:bold;\">{$mostrar}</div>";
                },
            ],
            [
                'attribute' => 'estatus_pago_id',
                'format' => 'raw',
                'label' => 'Estatus Pago',
                'value' => function($model) use ($optionsPagoJson) {
                    $nombre = $model['pago_nombre'] ?? 'Pendiente';
                    $color = strtolower($nombre) === 'liquidado' ? '#28a745' : '#ffc107'; // Verde si está liquidado, amarillo si no
        
                    return "<div class='editable-pago' 
                                data-record-id='{$model['id']}' 
                                data-field-name='estatus_pago_id'
                                data-options='{$optionsPagoJson}'
                                style='cursor:pointer; display:inline-block;'>
                                <span class='badge' style='background-color:{$color}; color:white;'>{$nombre}</span>
                            </div>";
                },
            ],
            [
                'attribute' => 'estatus_envio_id',
                'format' => 'raw',
                'label' => 'Estatus Envío',
                'value' => function($model) use ($optionsEnvioJson) {
                    // $model es un array, acceder directamente
                    $nombre = $model['envio_nombre'] ?? 'Pendiente';
                    $color = strtolower($nombre) === 'enviado' ? 'green' : 'red';
                    
                    return "<div class='editable-envio' 
                                data-record-id='{$model['id']}' 
                                data-field-name='estatus_envio_id'
                                data-options='{$optionsEnvioJson}'
                                style='cursor:pointer; display:inline-block;'>
                                <span class='badge' style='background-color:{$color}; color:white;'>{$nombre}</span>
                            </div>";
                },
            ],
        ],
    ]); ?>
</div>

<?php
// Estilos CSS
$this->registerCss("
/* Barra de encabezados de sección - PEGADA A LA TABLA */
.section-headers-bar {
    display: flex;
    margin-bottom: 0;
    border-radius: 8px 8px 0 0; /* Solo esquinas superiores redondeadas */
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.section-header {
    padding: 15px 20px;
    text-align: center;
    font-weight: 700;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: white;
    border-right: 2px solid rgba(255,255,255,0.2);
}

.section-header:last-child {
    border-right: none;
}

.basic-info-header {
    flex: 0 0 27%;
    background: linear-gradient(135deg, #6c757d, #495057);
}

.design-header {
    flex: 0 0 35%;
    background: linear-gradient(135deg, #007bff, #0056b3);
}

.production-header {
    flex: 0 0 31%;
    background: linear-gradient(135deg, #fd7e14, #e55a00);
}

/* CONTENEDOR PRINCIPAL SIN ESPACIOS */
.atencion-clientes-index {
    display: flex;
    flex-direction: column;
}

/* FORZAR CONEXIÓN COMPLETA ENTRE BARRA Y TABLA */
.atencion-clientes-index .section-headers-bar {
    margin: 0 !important;
    padding: 0 !important;
    border-bottom: none !important;
    flex-shrink: 0;
}

/* Eliminar TODOS los márgenes del CustomGridView y sus elementos hijos */
.atencion-clientes-index .grid-view,
.atencion-clientes-index .grid-view > div,
.atencion-clientes-index .grid-view .table-responsive,
.atencion-clientes-index .grid-view .summary,
.atencion-clientes-index .grid-view .empty {
    margin: 0 !important;
    padding: 0 !important;
    border-top: none !important;
    flex-shrink: 0;
}

/* Tabla principal - COMPLETAMENTE PEGADA */
.custom-table {
    font-size: 0.9rem;
    border-collapse: collapse;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    border-radius: 0 0 8px 8px; /* Solo esquinas inferiores redondeadas */
    overflow: hidden;
    margin: 0 !important; /* Sin márgenes en absoluto */
    border-top: none !important; /* Sin borde superior */
    width: 100%;
}

/* Eliminar márgenes de Bootstrap en tablas */
.table {
    margin-bottom: 0 !important;
}

/* SOLUCIÓN EXTREMA: Superponer elementos si es necesario */
.atencion-clientes-index .grid-view {
    margin-top: -2px !important;
    position: relative;
    z-index: 1;
}

/* Encabezados de la tabla - sin borde superior */
.custom-table thead tr:first-child th {
    border-top: none !important;
}

.production-col:last-child {
    border-right: none !important;
}

/* Celdas de datos */
.custom-table td {
    text-align: center;
    vertical-align: middle;
    padding: 10px 8px;
}

/* Badges */
.tipo-badge {
    font-size: 0.8rem;
    padding: 6px 12px;
    border-radius: 16px;
    font-weight: 600;
}

.status-badge,
.fecha-badge {
    font-size: 0.75rem;
    padding: 4px 8px;
    border-radius: 12px;
    font-weight: 500;
}

/* Círculos de estado (Diseño) */
.status-circle {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    color: white;
    font-size: 0.9rem;
    margin: 0 auto;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

/* Cuadrados de estado (Producción) */
.status-square {
    width: 28px;
    height: 28px;
    border-radius: 4px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    color: white;
    font-size: 0.9rem;
    margin: 0 auto;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

/* Hover effects */
.custom-table tbody tr:hover {
    background-color: rgba(0,123,255,0.05);
    transform: scale(1.01);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: all 0.2s ease;
}

.status-circle:hover,
.status-square:hover {
    transform: scale(1.1);
    transition: transform 0.2s ease;
}

/* Responsive */
@media (max-width: 992px) {
    .section-headers-bar {
        flex-wrap: wrap;
    }
    
    .basic-info-header {
        flex: 0 0 100%;
        border-bottom: 2px solid rgba(255,255,255,0.2);
        border-right: none;
    }
    
    .design-header {
        flex: 0 0 50%;
    }
    
    .production-header {
        flex: 0 0 50%;
    }
}

@media (max-width: 768px) {
    .section-headers-bar {
        flex-direction: column;
    }
    
    .section-header {
        flex: none;
        border-right: none;
        border-bottom: 2px solid rgba(255,255,255,0.2);
        padding: 12px 15px;
        font-size: 0.8rem;
    }
    
    .section-header:last-child {
        border-bottom: none;
    }
    
    .custom-table th,
    .custom-table td {
        padding: 6px 4px;
        font-size: 0.75rem;
    }
    
    .status-circle,
    .status-square {
        width: 20px;
        height: 20px;
        font-size: 0.7rem;
    }
}

/* Animaciones */
.status-circle,
.status-square,
.badge {
    transition: all 0.2s ease;
}

.custom-table tbody tr {
    transition: all 0.2s ease;
}
");


$this->registerJs(<<<'JS'
// --- JS Completo para select editable --

    $(document).on('click', '.editable-envio', function(e){
        e.stopPropagation();
        var div = $(this);
        var recordId = div.data('record-id');
        var fieldName = div.data('field-name');
        var options = div.data('options');
        if (typeof options === 'string') options = JSON.parse(options);
        if (div.find('select').length) return;
        var select = $('<select class="form-select form-select-sm"></select>');
        $.each(options, function(index, option){
            var selected = (div.find('span').text().trim() === option.nombre) ? 'selected' : '';
            select.append('<option value="'+option.id+'" '+selected+'>'+option.nombre+'</option>');
        });
        div.html(select);
        select.focus();
        select.on('change', function(){
            var newValue = $(this).val();
            var newText = $(this).find('option:selected').text();
            $.post('index.php?r=logistica/update-envio', {
                id: recordId,
                field: fieldName,
                value: newValue,
                _csrf: yii.getCsrfToken()
            }, function(response){
                if(response.success){
                    var color = newText.toLowerCase() === 'enviado' ? 'green' : 'red';
                    div.html('<span class="badge" style="background-color:'+color+'; color:white;">'+newText+'</span>');
                } else {
                    console.error(response.errors || response.message);
                    alert('Error: ' + (response.message || 'No se pudo actualizar'));
                }
            }, 'json');
        });
    });

JS
);

$this->registerJs(<<<'JS'
// --- JS Completo para select editable de PAGO --

    $(document).on('click', '.editable-pago', function(e){
        e.stopPropagation();
        var div = $(this);
        var recordId = div.data('record-id');
        var fieldName = div.data('field-name');
        var options = div.data('options');
        if (typeof options === 'string') options = JSON.parse(options);
        if (div.find('select').length) return;
        
        var select = $('<select class="form-select form-select-sm"></select>');
        $.each(options, function(index, option){
            var selected = (div.find('span').text().trim() === option.nombre) ? 'selected' : '';
            select.append('<option value="'+option.id+'" '+selected+'>'+option.nombre+'</option>');
        });
        div.html(select);
        select.focus();
        
        select.on('change', function(){
            var newValue = $(this).val();
            var newText = $(this).find('option:selected').text();
            
            console.log('Texto seleccionado:', newText); // Para debug
            
            $.post('index.php?r=logistica/update-pago', {
                id: recordId,
                field: fieldName,
                value: newValue,
                _csrf: yii.getCsrfToken()
            }, function(response){
                if(response.success){
                    // Ajusta estas comparaciones según los nombres reales en tu catálogo
                    var color = '#ffc107'; // Amarillo por defecto
                    var textLower = newText.toLowerCase();
                    
                    if (textLower === 'pagado' || textLower === 'liquidado' || textLower === 'completo') {
                        color = '#28a745'; // Verde
                    }
                    
                    var newDiv = '<div class="editable-pago" ' +
                                'data-record-id="'+recordId+'" ' +
                                'data-field-name="'+fieldName+'" ' +
                                'data-options=\''+JSON.stringify(options)+'\' ' +
                                'style="cursor:pointer; display:inline-block;">' +
                                '<span class="badge" style="background-color:'+color+'; color:white;">'+newText+'</span>' +
                                '</div>';
                    div.parent().html(newDiv);
                } else {
                    console.error(response.errors || response.message);
                    alert('Error: ' + (response.message || 'No se pudo actualizar'));
                }
            }, 'json');
        });
    });

JS
);