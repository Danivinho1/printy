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

    <div class="custom-table-container">
        <!-- Wrapper para ocultar scrollbar de encabezados -->
        <div class="headers-wrapper">
            <div class="headers-scroll-container" id="headers-container">
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
            </div>
        </div>
        
        <!-- Contenedor de la tabla con scroll -->
        <div class="table-scroll-container" id="table-container">
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

                    [
                        'attribute' => 'diseno_estatus_nombre',
                        'format' => 'raw',
                        'value' => function($model) {
                            $nombre = $model['diseno_estatus_nombre'];
                            $colores = match ($nombre) {
                                'Pendiente' => ['bg' => '#dc3545', 'text' => '#fff'],
                                'Listo'     => ['bg' => '#28a745', 'text' => '#fff'],
                                default     => generarColorUnico($nombre),
                            };
                            $badge = Html::tag('span', $nombre, [
                                'class' => 'badge',
                                'style' => "background-color:{$colores['bg']};color:{$colores['text']}"
                            ]);
                            return "<div class='estatus-badge' data-record-id='{$model['id']}'>{$badge}</div>";
                        },
                        'label' => 'Estatus Diseño',
                        'contentOptions' => ['style' => 'text-align: center;'],
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
                            $color = strtolower($nombre)=='listo'?'#28a745':'#dc3545';
                            return "<span class='badge status-badge' style='background-color:{$color}; color:white;'>{$nombre}</span>";
                        }
                    ],
                    [
    'attribute' => 'revision_calidad',
    'format' => 'raw',
    'label' => 'Empaquetado',
    'value' => function($model) {
        return isset($model['revision_calidad']) && $model['revision_calidad'] == 1
            ? '<span class="badge bg-success">Empaquetado</span>'
            : '<span class="badge bg-warning text-dark">Pendiente</span>';
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
                            $color = strtolower($nombre) === 'liquidado' ? '#28a745' : '#ffc107';
                
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
    </div>
</div>

<?php
// JavaScript para sincronizar el scroll
$this->registerJs("
// Función principal que se ejecuta hasta encontrar los elementos
function initSyncScroll() {
    try {
        const headersContainer = document.getElementById('headers-container');
        const tableContainer = document.getElementById('table-container');
        
        // Si no se encuentran, intentar de nuevo en 100ms
        if (!headersContainer || !tableContainer) {
            setTimeout(initSyncScroll, 100);
            return;
        }
        
        // Verificar que tengan scroll
        if (headersContainer.scrollWidth <= headersContainer.clientWidth) {
            setTimeout(initSyncScroll, 100);
            return;
        }
        
        // Variables para evitar bucle infinito
        let isHeaderScrolling = false;
        let isTableScrolling = false;
        
        // Handlers de scroll
        function headerScrollHandler() {
            if (isTableScrolling) return;
            isHeaderScrolling = true;
            tableContainer.scrollLeft = headersContainer.scrollLeft;
            setTimeout(() => { isHeaderScrolling = false; }, 10);
        }
        
        function tableScrollHandler() {
            if (isHeaderScrolling) return;
            isTableScrolling = true;
            headersContainer.scrollLeft = tableContainer.scrollLeft;
            setTimeout(() => { isTableScrolling = false; }, 10);
        }
        
        // Agregar listeners
        headersContainer.addEventListener('scroll', headerScrollHandler);
        tableContainer.addEventListener('scroll', tableScrollHandler);
        
    } catch (error) {
        setTimeout(initSyncScroll, 500);
    }
}

// Iniciar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSyncScroll);
} else {
    initSyncScroll();
}

// También intentar después de que se cargue todo
window.addEventListener('load', function() {
    setTimeout(initSyncScroll, 200);
});
");

// Estilos CSS actualizados
$this->registerCss("
/* Contenedor principal */
.custom-table-container {
    background: white !important;
    border-radius: 8px !important;
    overflow: visible !important; /* Cambiar de auto a visible */
    box-shadow: 0 4px 8px rgba(0,0,0,0.1) !important;
    margin-bottom: 20px !important;
    -webkit-overflow-scrolling: touch !important;
    position: relative !important;
}

/* Wrapper para ocultar la scrollbar de encabezados */
.headers-wrapper {
    position: relative;
    overflow: hidden;
    border-bottom: 2px solid #dee2e6;
}

/* Contenedor de encabezados con scroll horizontal propio */
.headers-scroll-container {
    overflow-x: scroll !important;
    overflow-y: hidden !important;
    background: white;
    width: 100%;
    min-width: 100%;
    /* Ocultar scrollbar completamente */
    scrollbar-width: none !important; /* Firefox */
    -ms-overflow-style: none !important; /* Internet Explorer 10+ */
    /* Técnica adicional para navegadores Webkit */
    -webkit-appearance: none !important;
    /* Margin negativo para ocultar la scrollbar */
    margin-bottom: -20px !important;
    padding-bottom: 20px !important;
}

/* Múltiples selectores para asegurar que se oculte en Webkit */
.headers-scroll-container::-webkit-scrollbar {
    display: none !important;
    width: 0 !important;
    height: 0 !important;
    background: transparent !important;
}

.headers-scroll-container::-webkit-scrollbar-track {
    display: none !important;
}

.headers-scroll-container::-webkit-scrollbar-thumb {
    display: none !important;
}

.headers-scroll-container::-webkit-scrollbar-corner {
    display: none !important;
}

/* Contenedor de tabla con scroll */
.table-scroll-container {
    overflow-x: auto !important;
    overflow-y: hidden !important;
    background: white;
    width: 100%;
    border-radius: 0 0 8px 8px;
}

/* Barra de encabezados */
.section-headers-bar {
    display: flex;
    min-width: 1400px; /* Mismo ancho que la tabla */
    border-radius: 8px 8px 0 0;
}

.section-header {
    padding: 8px 20px; /* Padding vertical más pequeño */
    text-align: center;
    font-weight: 500; /* Menos bold */
    font-size: 0.8rem; /* Texto más pequeño */
    text-transform: uppercase;
    letter-spacing: 0.5px; /* Menos espaciado */
    color: white;
    border-right: 1px solid rgba(255,255,255,0.2); /* Bordes más sutiles */
}

.section-header:last-child {
    border-right: none;
}

.basic-info-header {
    flex: 0 0 340px; /* Ancho fijo que coincida con las columnas de info básica */
    background: #3379c0ff; /* Color más suave */
}

.design-header {
    flex: 0 0 428px; /* Ancho fijo que coincida con las columnas de diseño */
    background: #cd6cedff; /* Color más suave */
}

.production-header {
    flex: 0 0 370px; /* Ancho fijo que coincida con las columnas de producción */
    background: #f88d1aff; /* Color más suave */
}

/* Eliminar TODOS los márgenes del CustomGridView */
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
    border-radius: 0 0 8px 8px; /* Solo esquinas inferiores redondeadas */
    overflow: hidden;
    margin: 0 !important; /* Sin márgenes en absoluto */
    border-top: none !important; /* Sin borde superior */
    width: 100%;
    min-width: 1400px; /* Mismo ancho mínimo que la barra */
}

/* Eliminar márgenes de Bootstrap en tablas */
.table {
    margin-bottom: 0 !important;
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

/* Responsive - en pantallas pequeñas mantener scroll horizontal */
@media (max-width: 992px) {
    .section-headers-bar {
        min-width: 1000px; /* Reducir un poco en tablets */
    }
    
    .custom-table {
        min-width: 1000px;
    }
}

@media (max-width: 768px) {
    .section-headers-bar {
        min-width: 800px; /* Aún más pequeño en móviles */
    }
    
    .custom-table {
        min-width: 800px;
    }
    
    .section-header {
        padding: 6px 15px;
        font-size: 0.7rem;
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

/* Scrollbar personalizada para el contenedor de la tabla */
.table-scroll-container::-webkit-scrollbar {
    height: 8px;
}

.table-scroll-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.table-scroll-container::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.table-scroll-container::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
");


    $this->registerJs(<<<'JS'
// --- JS Completo para select editable de ENVÍO --

$(document).on('click', '.editable-envio', function(e){
    e.stopPropagation();
    var div = $(this);
    var recordId = div.data('record-id');
    var fieldName = div.data('field-name');
    var options = div.data('options');
    
    // Verificar que options existe y convertir si es string
    if (typeof options === 'string') {
        try {
            options = JSON.parse(options);
        } catch(e) {
            console.error('Error parseando options:', e);
            return;
        }
    }
    
    // Si ya hay un select, no hacer nada
    if (div.find('select').length) return;
    
    // Crear el select
    var select = $('<select class="form-select form-select-sm"></select>');
    
    // Obtener el texto actual del badge
    var currentText = div.find('span').text().trim();
    
    // Agregar las opciones
    $.each(options, function(index, option){
        var selected = (currentText === option.nombre) ? 'selected' : '';
        select.append('<option value="'+option.id+'" '+selected+'>'+option.nombre+'</option>');
    });
    
    // Reemplazar el contenido con el select
    div.html(select);
    select.focus();
    
    // Manejar el cambio
    select.on('change', function(){
        var newValue = $(this).val();
        var newText = $(this).find('option:selected').text();
        
        console.log('Actualizando envío - ID:', recordId, 'Valor:', newValue, 'Texto:', newText);
        
        $.post('index.php?r=logistica/update-envio', {
            id: recordId,
            field: fieldName,
            value: newValue,
            _csrf: yii.getCsrfToken()
        }, function(response){
            console.log('Respuesta del servidor:', response); // Para debug
            
            if(response.success){
                // Usar el nombre que devuelve el servidor (más confiable)
                var finalText = response.nombre || newText;
                
                // Determinar el color basado en el texto final
                var color = finalText.toLowerCase() === 'enviado' ? 'green' : 'red';
                
                // Recrear el elemento editable completo
                var newDiv = '<div class="editable-envio" ' +
                            'data-record-id="'+recordId+'" ' +
                            'data-field-name="'+fieldName+'" ' +
                            'data-options=\''+JSON.stringify(options)+'\' ' +
                            'style="cursor:pointer; display:inline-block;">' +
                            '<span class="badge" style="background-color:'+color+'; color:white;">'+finalText+'</span>' +
                            '</div>';
                
                div.parent().html(newDiv);
                
                console.log('Actualización exitosa - Texto final:', finalText);
            } else {
                console.error('Error en respuesta:', response.errors || response.message);
                alert('Error: ' + (response.message || 'No se pudo actualizar el estado de envío'));
                
                // Restaurar el elemento original en caso de error
                var originalColor = currentText.toLowerCase() === 'enviado' ? 'green' : 'red';
                div.html('<span class="badge" style="background-color:'+originalColor+'; color:white;">'+currentText+'</span>');
            }
        }, 'json').fail(function(xhr, status, error) {
            console.error('Error AJAX:', status, error);
            console.log('Respuesta completa:', xhr.responseText); // Para debug
            alert('Error de conexión: ' + error);
            
            // Restaurar el elemento original
            var originalColor = currentText.toLowerCase() === 'enviado' ? 'green' : 'red';
            div.html('<span class="badge" style="background-color:'+originalColor+'; color:white;">'+currentText+'</span>');
        });
    });
    
    // Manejar cuando se pierde el foco sin seleccionar
    select.on('blur', function(){
        setTimeout(function(){
            if (div.find('select').length) {
                // Restaurar el badge original
                var originalColor = currentText.toLowerCase() === 'enviado' ? 'green' : 'red';
                div.html('<span class="badge" style="background-color:'+originalColor+'; color:white;">'+currentText+'</span>');
            }
        }, 200);
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