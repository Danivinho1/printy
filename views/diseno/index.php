<?php

use app\models\Diseno;
use app\models\DisenoSearch;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use app\widgets\CustomGridView;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Diseño';
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

?>

<div class="diseno-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php

// Obtener filtros actuales de la URL
$filtroEstatus = Yii::$app->request->get('estatus');

// Obtener conteos usando el DisenoSearch
$conteos = DisenoSearch::getFiltrosConteos();
$totalRegistros = $conteos['total'];
$urgentesCount = $conteos['urgentes'];
$pendientesCount = $conteos['pendientes'];
$listosCount = $conteos['listos'];

// Función para mantener otros filtros en las URLs
function buildFilterUrl($newFilters = []) {
    $currentParams = Yii::$app->request->queryParams;
    
    // Remover parámetros de paginación para reset
    unset($currentParams['page']);
    
    $params = array_merge($currentParams, $newFilters);
    
    // Remover parámetros vacíos o null
    foreach ($params as $key => $value) {
        if ($value === null || $value === '' || $value === 'todos') {
            unset($params[$key]);
        }
    }
    
    return Url::current($params);
}
?>

<div class="filtros-diseño mb-4 d-flex justify-content-between align-items-center">
    <!-- Filtros a la izquierda -->
    <div class="d-flex align-items-center gap-2">
        <!-- Botón Todos -->
        <a href="<?= Url::to(['diseno/index']) ?>" 
           class="btn-filtro <?= !$filtroEstatus ? 'active' : '' ?>">
            Todos (<?= $totalRegistros ?>)
        </a>

        <!-- Filtro Urgentes -->
        <a href="<?= Url::to(['diseno/index', 'estatus' => 'urgente']) ?>" 
           class="btn-filtro btn-urgente <?= $filtroEstatus == 'urgente' ? 'active' : '' ?>">
            Urgentes (<?= $urgentesCount ?>)
        </a>

        <!-- Filtro Pendientes -->
        <a href="<?= Url::to(['diseno/index', 'estatus' => 'pendiente']) ?>" 
           class="btn-filtro btn-pendiente <?= $filtroEstatus == 'pendiente' ? 'active' : '' ?>">
            Pendientes (<?= $pendientesCount ?>)
        </a>

        <!-- Filtro Listos -->
        <a href="<?= Url::to(['diseno/index', 'estatus' => 'listo']) ?>" 
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
            'telefono',

            [
                'attribute' => 'entrega_id',
                'format' => 'raw',
                'value' => function($model) {
                    if (!$model->entrega) return '<span class="badge bg-secondary">No definido</span>';
                    $colores = generarColorUnico($model->entrega->nombre);
                    return '<span class="badge" style="background-color: '.$colores['bg'].'; color: '.$colores['text'].';">'.$model->entrega->nombre.'</span>';
                },
                'label' => 'Entrega'
            ],

            [
              'attribute' => 'fecha_confirmacion',
              'format' => 'raw',
              'label' => 'Fecha Confirmación',
              'value' => function($model) {
                  if (empty($model->fecha_confirmacion)) {
                      return '<span class="badge bg-warning text-dark fecha-confirmacion-badge" data-id="'.$model->id.'">Pendiente</span>';
                  } else {
                      return '<span class="fecha-confirmacion-text" data-id="'.$model->id.'">'
                          . Yii::$app->formatter->asDate($model->fecha_confirmacion, 'php:d/m/Y')
                          . '</span>';
                  }
                },
            ],


            // Responsable
            [
                'attribute' => 'responsable_id',
                'format' => 'raw',
                'value' => function($model) {
                    $nombre = $model->responsable->nombre ?? 'Sin asignar';
                    $colores = generarColorUnico($nombre);
                    return "<div class='editable-field select-responsable' data-field-type='select' data-field-name='responsable_id' data-record-id='{$model->id}' data-current-value='{$model->responsable_id}' title='Click para editar'>
                        <span class='badge' style='background-color:{$colores['bg']};color:{$colores['text']}'>{$nombre}</span>
                    </div>";
                },
                'label' => 'Responsable'
            ],

            // Extras
            [
                'attribute' => 'extras',
                'format' => 'raw',
                'value' => function($model) {
                    $extras = $model->venta ? $model->venta->extras : [];
                    $badges = '';
                    $ids = [];
                    foreach ($extras as $extra) {
                        $ids[] = $extra->id;
                        $colores = generarColorUnico($extra->nombre);
                        $badges .= '<span class="badge me-1 mb-1" style="background-color:'.$colores['bg'].';color:'.$colores['text'].';">'.$extra->nombre.'</span>';
                    }
                    if (empty($badges)) $badges = '<span class="badge bg-light text-dark">Ninguno</span>';
                    $idsStr = implode(',', $ids);
                    return "<div class='editable-field multiselect-extra' data-field-type='multiselect' data-field-name='extras' data-record-id='{$model->id}' data-current-value='{$idsStr}' data-catalog-type='extra' title='Click para editar'>{$badges}</div>";
                },
                'label' => 'Extras'
            ],

            // Adicionales
            [
                'attribute' => 'adicionales',
                'format' => 'raw',
                'value' => function($model) {
                    $adicionales = $model->venta ? $model->venta->adicionales : [];
                    $badges = '';
                    $ids = [];
                    foreach ($adicionales as $adicional) {
                        $ids[] = $adicional->id;
                        $colores = generarColorUnico($adicional->nombre);
                        $badges .= '<span class="badge me-1 mb-1" style="background-color:'.$colores['bg'].';color:'.$colores['text'].';">'.$adicional->nombre.'</span>';
                    }
                    if (empty($badges)) $badges = '<span class="badge bg-light text-dark">Ninguno</span>';
                    $idsStr = implode(',', $ids);
                    return "<div class='editable-field multiselect-adicional' data-field-type='multiselect' data-field-name='adicionales' data-record-id='{$model->id}' data-current-value='{$idsStr}' data-catalog-type='adicional' title='Click para editar'>{$badges}</div>";
                },
                'label' => 'Adicionales'
            ],

            [
                'attribute' => 'extra_precio',
                'format' => 'raw',
                'value' => function($model) {
                    $valor = $model->extra_precio ? $model->extra_precio : 0;
                    $mostrar = '$' . number_format($valor, 2);
                    return "<div class=\"editable-field\" data-field-type=\"number\" data-field-name=\"extra_precio\" data-record-id=\"{$model->id}\" data-current-value=\"{$valor}\" title=\"Click para editar\">{$mostrar}</div>";
                },
                'label' => 'Precio Extra'
            ],

            // Check-circles
            [
                'attribute' => 'contacto_cliente_id',
                'format' => 'raw',
                'value' => function($model){
                    $completed = $model->contacto_cliente_id == 1;
                    $color = $completed ? '#4CAF50' : '#f44336';
                    $icon = $completed ? '✓' : '✗';
                    return Html::tag('div', $icon, [
                        'class'=>'check-circle',
                        'data-id'=>$model->id,
                        'data-field'=>'contacto_cliente_id',
                        'title'=>'Contacto',
                        'style'=>"width:25px;height:25px;border-radius:50%;background-color:$color;cursor:pointer;color:white;display:flex;align-items:center;justify-content:center;font-weight:bold;margin:auto;"
                    ]);
                }
            ],
            [
                'attribute' => 'vectorizado_id',
                'format' => 'raw',
                'value' => function($model){
                    $completed = $model->vectorizado_id == 1;
                    $color = $completed ? '#FF9800' : '#f44336';
                    $icon = $completed ? '✓' : '✗';
                    return Html::tag('div', $icon, [
                        'class'=>'check-circle',
                        'data-id'=>$model->id,
                        'data-field'=>'vectorizado_id',
                        'title'=>'Vectorizado',
                        'style'=>"width:25px;height:25px;border-radius:50%;background-color:$color;cursor:pointer;color:white;display:flex;align-items:center;justify-content:center;font-weight:bold;margin:auto;"
                    ]);
                }
            ],

            // Barra de progreso
            [
                'attribute' => 'avance',
                'format' => 'raw',
                'value' => function($model){
                    $color = getColorAvance($model->avance);
                    return Html::tag('div',
                        Html::tag('div', $model->avance.'%', [
                            'class'=>'progress-bar',
                            'role'=>'progressbar',
                            'style'=>"width:{$model->avance}%;height:30px;line-height:30px;background-color:{$color};text-align:center;color:#000;font-weight:bold;border-radius:5px;transition: width 0.5s, background-color 0.5s;",
                            'aria-valuenow'=>$model->avance,
                            'aria-valuemin'=>0,
                            'aria-valuemax'=>100,
                            'data-id'=>$model->id
                        ]),
                        ['class'=>'progress','style'=>'height:30px;border-radius:5px;']
                    );
                }
            ],

            [
               'attribute' => 'estatus_id',
               'format' => 'raw',
               'value' => function($model) {
                   $nombre = $model->estatus->nombre ?? 'Pendiente';
                   switch ($nombre) {
                       case 'Pendiente':
                           $colores = ['bg' => '#dc3545', 'text' => '#fff'];
                           break;
                       case 'Listo':
                           $colores = ['bg' => '#28a745', 'text' => '#fff'];
                           break;
                       default:
                           $colores = generarColorUnico($nombre);
                           break;
                   }
                  $badge = Html::tag('span', $nombre, [
                      'class' => 'badge',
                      'style' => "background-color:{$colores['bg']};color:{$colores['text']}"
                  ]);
                  return "<div class='estatus-badge' data-record-id='{$model->id}'>{$badge}</div>";
              },
              'label' => 'Estatus',
            ],



        ],
    ]); ?>

    

</div>


<?php
$this->registerJs('
function getBarColor(avance){
    if(avance <= 50) return "#ff9800";
    return "#4CAF50";
}

// Inicializar data("completed") según el valor de la base
$(".check-circle").each(function(){
    var circle = $(this);
    var completed = circle.text().trim() === "✓"; 
    circle.data("completed", completed);
});

// Manejar click en los check-circles
$(".check-circle").on("click", function(){
    var circle = $(this);
    var id = circle.data("id");
    var field = circle.data("field");

    // Validar que tenemos los datos necesarios
    if (!id || !field) {
        console.error("Faltan datos: id=" + id + ", field=" + field);
        return;
    }

    // Invertir valor de completed
    var completed = !circle.data("completed");
    circle.data("completed", completed);
    circle.text(completed ? "✓" : "✗");

    // Colores de cada check
    var colorMap = {
        "contacto_cliente_id": "#4CAF50",
        "vectorizado_id": "#FF9800",

    };
    
    var newColor = completed ? colorMap[field] : "#f44336";
    circle.css("background-color", newColor);

    // Calcular avance total
    var total = 0;
    var totalSteps = 0;
    $(".check-circle[data-id=\'" + id + "\']").each(function(){
        totalSteps++;
        if($(this).data("completed")) {
            total += 50;
        }
    });

    // Evitar que el total supere 100
    if (total > 100) total = 100;

    // Actualizar barra de progreso
    var bar = $(".progress-bar[data-id=\'" + id + "\']");
    if (bar.length > 0) {
        bar.css({
            "width": total + "%", 
            "background-color": getBarColor(total)
        }).text(total + "%").attr("aria-valuenow", total);
    }

    // Actualizar estatus visual
    var statusCell = $(".estatus-badge[data-record-id=" + id + "]");
    var statusValue = (total === 100) ? "Listo" : "Pendiente";
    var badge = statusCell.find(".badge");

    if (badge.length > 0) {
    badge.text(statusValue).css({
        "background-color": (statusValue === "Listo" ? "#28a745" : "#dc3545"),
        "color": "#fff"
    });
}


    // Mostrar indicador de carga
    circle.css("opacity", "0.6");

    // Enviar cambios al servidor
    $.ajax({
        url: "' . Url::to(["diseno/update-avance"]) . '",
        type: "POST",
        data: {
            id: parseInt(id),
            field: field,
            value: completed ? 1 : null, // ← Aquí corregimos para enviar NULL
            avance: total,
            estatus_nombre: statusValue,
            _csrf: yii.getCsrfToken()
        },
        dataType: "json",
        success: function(response) {
            circle.css("opacity", "1");

            if (response && response.status === "ok") {
                console.log("✔ Actualización exitosa para registro " + id);
            } else {
                console.error("⚠️ Error del servidor:", response);
                alert("Error al guardar: " + (response.message || "Error desconocido"));

                // Revertir cambios en caso de error
                var originalCompleted = !completed;
                circle.data("completed", originalCompleted);
                circle.text(originalCompleted ? "✓" : "✗");
                circle.css("background-color", originalCompleted ? colorMap[field] : "#f44336");
            }
        },
        error: function(xhr, status, error) {
            circle.css("opacity", "1");
            console.error("❌ Error AJAX:", {
                status: status,
                error: error,
                response: xhr.responseText
            });
            alert("Error de conexión. Por favor, intenta nuevamente.");

            // Revertir cambios en caso de fallo de conexión
            var originalCompleted = !completed;
            circle.data("completed", originalCompleted);
            circle.text(originalCompleted ? "✓" : "✗");
            circle.css("background-color", originalCompleted ? colorMap[field] : "#f44336");
        }
    });
});
');
?>


<?php
$this->registerJs(<<<JS
// --------------------------
// Badge "Pendiente" → input date
// --------------------------
$(document).on('click', '.fecha-confirmacion-badge, .fecha-confirmacion-text', function() {
    let span = $(this);
    let id = span.data('id');
    let currentText = span.text().trim();

    // convertir fecha dd/mm/yyyy a yyyy-mm-dd
    let value = '';
    if (currentText !== 'Pendiente') {
        let parts = currentText.split('/');
        value = parts[2]+'-'+parts[1]+'-'+parts[0];
    }

    // crear input date
    let input = $('<input>', {
        type: 'date',
        class: 'form-control fecha-confirmacion',
        'data-id': id,
        value: value
    });

    span.replaceWith(input);
    input.trigger('focus');
});

// Guardar al cambiar fecha
$(document).on('change', '.fecha-confirmacion', function() {
    let input = $(this);
    let id = input.data('id');
    let fecha = input.val();

    $.ajax({
        url: 'index.php?r=diseno/update-fecha',
        type: 'POST',
        data: {
            id: id,
            fecha_confirmacion: fecha,
            _csrf: yii.getCsrfToken()
        },
        success: function(res) {
            if (res.success) {
                let span;
                if (fecha) {
                    span = $('<span>', {
                        class: 'fecha-confirmacion-text',
                        'data-id': id,
                        text: res.fecha
                    });
                } else {
                    span = $('<span>', {
                        class: 'badge bg-warning text-dark fecha-confirmacion-badge',
                        'data-id': id,
                        text: 'Pendiente'
                    });
                }
                input.replaceWith(span);
            } else {
                alert('⚠️ Error: ' + res.message);
            }
        },
        error: function(xhr) {
            alert('❌ Error AJAX: ' + xhr.responseText);
        }
    });
});

// Si el usuario hace blur sin seleccionar fecha, vuelve a badge si estaba pendiente
$(document).on('blur', '.fecha-confirmacion', function() {
    let input = $(this);
    if (!input.val()) {
        let id = input.data('id');
        let span = $('<span>', {
            class: 'badge bg-warning text-dark fecha-confirmacion-badge',
            'data-id': id,
            text: 'Pendiente'
        });
        input.replaceWith(span);
    }
});
JS);
?>
<?php
$this->registerJs("
$(document).ready(function() {
    let editingCell = null;

    // Event listener principal para campos editables
    $(document).off('click.editable').on('click.editable', '.editable-field', function(e) {
        e.preventDefault();
        e.stopPropagation();

        if (editingCell !== null) return;

        let cell = $(this);
        let fieldType = cell.data('field-type');
        let fieldName = cell.data('field-name');
        let recordId = cell.data('record-id');
        let currentValue = cell.data('current-value');
        editingCell = cell;
        cell.addClass('editing');
        let originalContent = cell.html();
        let inputElement;

        console.log('Celda clickeada:', cell[0], 'fieldType:', fieldType, 'fieldName:', fieldName, 'recordId:', recordId, 'currentValue:', currentValue);

        if (fieldType === 'select') {
            loadSelectOptions(fieldName, currentValue, function(options) {
                inputElement = '<select class=\"inline-select form-control\"><option value=\"\">-- Seleccionar --</option>';
                options.forEach(function(option) {
                    let selected = option.value == currentValue ? 'selected' : '';
                    inputElement += '<option value=\"' + option.value + '\" ' + selected + '>' + option.text + '</option>';
                });
                inputElement += '</select>';
                createEditContainer(inputElement);
            });
            return;
        }

        if (fieldType === 'multiselect') {
            let catalogType = cell.data('catalog-type');
            loadMultiSelectOptions(catalogType, currentValue, function(options) {
                // Manejo mejorado de valores seleccionados
                let selectedValues = [];
                
                console.log('currentValue recibido:', currentValue, 'tipo:', typeof currentValue);
                
                if (currentValue !== null && currentValue !== undefined && currentValue !== '' && currentValue !== '0') {
                    if (Array.isArray(currentValue)) {
                        selectedValues = currentValue.map(val => val.toString());
                    } else {
                        // Convertir a string y dividir por comas, eliminando espacios
                        let stringValue = currentValue.toString().trim();
                        if (stringValue !== '' && stringValue !== '0') {
                            selectedValues = stringValue.split(',').map(val => val.trim()).filter(val => val !== '' && val !== '0');
                        }
                    }
                }
                
                console.log('selectedValues procesados:', selectedValues);

                inputElement = '<div class=\"multiselect-container border border-primary rounded p-3\" style=\"max-height: 250px; overflow-y: auto; background-color: #f8f9fa; min-width: 300px;\">';
                inputElement += '<div class=\"mb-2\"><strong>Selecciona una o múltiples opciones:</strong></div>';
                inputElement += '<div class=\"mb-2\"><small class=\"text-muted\">Puedes marcar/desmarcar las opciones que desees</small></div>';
                
                if (options.length === 0) {
                    inputElement += '<div class=\"text-muted\">No hay opciones disponibles</div>';
                } else {
                    options.forEach(function(option) {
                        let isChecked = selectedValues.includes(option.value.toString());
                        let checkboxId = 'multiselect_' + catalogType + '_' + option.value + '_' + Math.random().toString(36).substr(2, 9);
                        
                        console.log('Opción:', option.value, 'texto:', option.text, 'isChecked:', isChecked);
                        
                        inputElement += '<div class=\"form-check mb-2\">';
                        inputElement += '<input class=\"form-check-input multiselect-checkbox\" type=\"checkbox\" value=\"' + option.value + '\" id=\"' + checkboxId + '\"' + (isChecked ? ' checked' : '') + '>';
                        inputElement += '<label class=\"form-check-label ms-2\" for=\"' + checkboxId + '\" style=\"cursor: pointer; font-weight: 500; user-select: none;\">' + option.text + '</label>';
                        inputElement += '</div>';
                    });
                }
                
                inputElement += '</div>';
                createEditContainer(inputElement);
            });
            return;
        }

        // Campos tipo texto, número, fecha
        let inputType = fieldType === 'number' ? 'number' : 'text';
        let step = fieldType === 'number' ? 'step=\"0.01\"' : '';
        inputElement = '<input type=\"' + inputType + '\" class=\"inline-input form-control\" value=\"' + (currentValue || '') + '\" ' + step + '>';
        createEditContainer(inputElement);

        function createEditContainer(inputEl) {
            let editContainer = '<div class=\"edit-container\" style=\"min-width: 200px;\">' +
                               inputEl +
                               '<div class=\"save-cancel-buttons mt-2 d-flex gap-2\">' +
                               '<button type=\"button\" class=\"btn btn-success btn-sm save-btn\" data-record-id=\"' + recordId + '\" data-field-name=\"' + fieldName + '\">' +
                               '<i class=\"fas fa-save me-1\"></i>Guardar</button>' +
                               '<button type=\"button\" class=\"btn btn-secondary btn-sm cancel-btn\">' +
                               '<i class=\"fas fa-times me-1\"></i>Cancelar</button>' +
                               '</div>' +
                               '</div>';
            
            cell.html(editContainer);
            
            // Focus en el elemento apropiado
            let focusElement = cell.find('.inline-input, .inline-select');
            if (focusElement.length > 0) {
                setTimeout(function() {
                    focusElement.focus();
                    if (focusElement.is('input[type=\"text\"]')) {
                        focusElement.select();
                    }
                }, 100);
            }
            
            bindEditEvents(cell, recordId, fieldName, originalContent);
        }
    });

    function bindEditEvents(cell, recordId, fieldName, originalContent) {
        // Evento para guardar
        cell.off('click.save').on('click.save', '.save-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            let saveBtn = $(this);
            let newValue;

            if (cell.find('.multiselect-checkbox').length > 0) {
                // Manejo de multiselect
                let checkedValues = [];
                cell.find('.multiselect-checkbox:checked').each(function() {
                    checkedValues.push($(this).val());
                });
                newValue = checkedValues;
                console.log('Valores multiselect seleccionados:', newValue);
            } else {
                // Campos simples
                let inputField = cell.find('.inline-input, .inline-select');
                newValue = inputField.val();
                if (fieldName.includes('precio') || fieldName === 'unidades' || fieldName === 'anticipo' || fieldName === 'restante') {
                    newValue = parseFloat(newValue) || 0;
                }
            }

            console.log('Enviando actualización - ID:', recordId, 'Campo:', fieldName, 'Nuevo valor:', newValue);

            let ajaxUrl = (fieldName === 'adicionales' || fieldName === 'extras') ? 
                '" . Url::to(['update-many-to-many']) . "' : '" . Url::to(['update-field']) . "';

            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    id: recordId,
                    field: fieldName,
                    value: newValue,
                    _csrf: yii.getCsrfToken()
                },
                beforeSend: function() {
                    saveBtn.prop('disabled', true).html('<i class=\"fas fa-spinner fa-spin me-1\"></i>Guardando...');
                },
                success: function(response) {
                    console.log('Respuesta del servidor:', response);
                    if (response && response.success) {
                        // Actualizar el data-current-value para futuras ediciones
                        let newCurrentValue;
                        if (Array.isArray(newValue)) {
                            newCurrentValue = newValue.join(',');
                        } else {
                            newCurrentValue = newValue;
                        }
                        
                        cell.data('current-value', newCurrentValue);
                        
                        // Mostrar el nuevo contenido
                        let displayContent = response.newContent || response.display || newValue;
                        if (Array.isArray(newValue) && newValue.length === 0) {
                            displayContent = '<span class=\"badge bg-light text-dark\">Ninguno</span>';
                        }
                        
                        cell.html(displayContent);
                        cell.removeClass('editing');
                        editingCell = null;
                        
                        // Mostrar mensaje de éxito
                        if (response.message) {
                            showNotification(response.message, 'success');
                        }
                    } else {
                        // Error en la respuesta
                        cell.html(originalContent);
                        cell.removeClass('editing');
                        editingCell = null;
                        showNotification(response.message || 'Error al actualizar el campo', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error AJAX:', xhr.responseText, status, error);
                    cell.html(originalContent);
                    cell.removeClass('editing');
                    editingCell = null;
                    
                    let errorMsg = 'Error de conexión';
                    try {
                        let response = JSON.parse(xhr.responseText);
                        errorMsg = response.message || errorMsg;
                    } catch(e) {
                        errorMsg += ': ' + error;
                    }
                    
                    showNotification(errorMsg, 'error');
                },
                complete: function() {
                    saveBtn.prop('disabled', false).html('<i class=\"fas fa-save me-1\"></i>Guardar');
                }
            });
        });

        // Evento para cancelar
        cell.off('click.cancel').on('click.cancel', '.cancel-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            cell.html(originalContent);
            cell.removeClass('editing');
            editingCell = null;
        });

        // Prevenir que los clicks en checkboxes y labels cierren la edición
        cell.off('click.prevent').on('click.prevent', '.multiselect-checkbox, .form-check-label, .multiselect-container', function(e) {
            e.stopPropagation();
        });

        // Manejar Enter y Escape en campos de texto
        cell.off('keydown.edit').on('keydown.edit', '.inline-input', function(e) {
            if (e.keyCode === 13) { // Enter
                e.preventDefault();
                cell.find('.save-btn').click();
            } else if (e.keyCode === 27) { // Escape
                e.preventDefault();
                cell.find('.cancel-btn').click();
            }
        });
    }

    function loadSelectOptions(fieldName, currentValue, callback) {
        $.ajax({
            url: '" . Url::to(['get-select-options']) . "',
            type: 'GET',
            dataType: 'json',
            data: { field: fieldName },
            success: function(response) {
                console.log('Opciones select cargadas:', response);
                callback(response.options || []);
            },
            error: function(xhr, status, error) {
                console.error('Error cargando opciones select:', error);
                showNotification('Error cargando opciones', 'error');
                callback([]);
            }
        });
    }

    function loadMultiSelectOptions(catalogType, currentValue, callback) {
        $.ajax({
            url: '" . Url::to(['get-multiselect-options']) . "',
            type: 'GET',
            dataType: 'json',
            data: { type: catalogType },
            success: function(response) {
                console.log('Opciones multiselect cargadas:', response);
                callback(response.options || []);
            },
            error: function(xhr, status, error) {
                console.error('Error cargando opciones multiselect:', error);
                showNotification('Error cargando opciones', 'error');
                callback([]);
            }
        });
    }

    // Función para mostrar notificaciones
    function showNotification(message, type) {
        let alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        let icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
        
        let notification = '<div class=\"alert ' + alertClass + ' alert-dismissible fade show notification-toast\" role=\"alert\" style=\"position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;\">' +
            '<i class=\"fas ' + icon + ' me-2\"></i>' + message +
            '<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button>' +
            '</div>';
        
        $('body').append(notification);
        
        // Auto-hide después de 3 segundos
        setTimeout(function() {
            $('.notification-toast').fadeOut(function() {
                $(this).remove();
            });
        }, 3000);
    }

    // Cerrar edición si se hace click fuera
    $(document).on('click', function(e) {
        if (editingCell !== null && !$(e.target).closest('.edit-container').length) {
            let cancelBtn = editingCell.find('.cancel-btn');
            if (cancelBtn.length > 0) {
                cancelBtn.click();
            }
        }
    });
});
");

?>




