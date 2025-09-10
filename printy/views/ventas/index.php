<?php

use app\models\Ventas;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use app\widgets\CustomGridView;
use app\models\VentasExtras;
use app\models\VentasAdicionales;
use yii\widgets\ActiveForm;

// Crear modelo para el modal
$modeloNuevo = new Ventas();
if ($modeloNuevo->load(Yii::$app->request->post()) && $modeloNuevo->save()) {
    Yii::$app->session->setFlash('success', 'Venta guardada correctamente.');
    return $this->refresh();
}

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Ventas';
$this->params['breadcrumbs'][] = $this->title;

$this->registerJs("
$(document).ready(function() {
    let editingCell = null;

    // Función para actualizar Restante
    function actualizarRestante(recordId){
        $.post('" . Url::to(['ventas/actualizar-restante']) . "', 
            {id: recordId, _csrf: yii.getCsrfToken()}, 
            function(res){
                if(res.success){
                    $('.editable-field[data-field-name=\"restante\"][data-record-id=\"'+recordId+'\"]')
                        .each(function(){
                            $(this).html(res.restanteHtml);
                            $(this).data('current-value', res.restante);
                        });
                }
            }, 'json'
        );
    }

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
                let selectedValues = [];
                if (currentValue !== null && currentValue !== undefined && currentValue !== '' && currentValue !== '0') {
                    if (Array.isArray(currentValue)) {
                        selectedValues = currentValue.map(val => val.toString());
                    } else {
                        let stringValue = currentValue.toString().trim();
                        if (stringValue !== '' && stringValue !== '0') {
                            selectedValues = stringValue.split(',').map(val => val.trim()).filter(val => val !== '' && val !== '0');
                        }
                    }
                }

                inputElement = '<div class=\"multiselect-container border border-primary rounded p-3\" style=\"max-height: 250px; overflow-y: auto; background-color: #f8f9fa; min-width: 300px;\">';
                inputElement += '<div class=\"mb-2\"><strong>Selecciona una o múltiples opciones:</strong></div>';
                inputElement += '<div class=\"mb-2\"><small class=\"text-muted\">Puedes marcar/desmarcar las opciones que desees</small></div>';
                if (options.length === 0) {
                    inputElement += '<div class=\"text-muted\">No hay opciones disponibles</div>';
                } else {
                    options.forEach(function(option) {
                        let isChecked = selectedValues.includes(option.value.toString());
                        let checkboxId = 'multiselect_' + catalogType + '_' + option.value + '_' + Math.random().toString(36).substr(2, 9);
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
            let focusElement = cell.find('.inline-input, .inline-select');
            if (focusElement.length > 0) {
                setTimeout(function() {
                    focusElement.focus();
                    if (focusElement.is('input[type=\"text\"]')) focusElement.select();
                }, 100);
            }
            bindEditEvents(cell, recordId, fieldName, originalContent);
        }
    });

    function bindEditEvents(cell, recordId, fieldName, originalContent) {
        cell.off('click.save').on('click.save', '.save-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            let saveBtn = $(this);
            let newValue;

            if (cell.find('.multiselect-checkbox').length > 0) {
                let checkedValues = [];
                cell.find('.multiselect-checkbox:checked').each(function() { checkedValues.push($(this).val()); });
                newValue = checkedValues;
            } else {
                let inputField = cell.find('.inline-input, .inline-select');
                newValue = inputField.val();
                if (fieldName.includes('precio') || fieldName === 'unidades' || fieldName === 'anticipo' || fieldName === 'restante' || fieldName === 'total') {
                    newValue = parseFloat(newValue) || 0;
                }
            }

            let ajaxUrl = (fieldName === 'adicionales' || fieldName === 'extras') ? 
                '" . Url::to(['update-many-to-many']) . "' : '" . Url::to(['update-field']) . "';

            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {id: recordId, field: fieldName, value: newValue, _csrf: yii.getCsrfToken()},
                beforeSend: function() { saveBtn.prop('disabled', true).html('<i class=\"fas fa-spinner fa-spin me-1\"></i>Guardando...'); },
                success: function(response) {
                    if (response && response.success) {
                        let newCurrentValue = Array.isArray(newValue) ? newValue.join(',') : newValue;
                        cell.data('current-value', newCurrentValue);
                        let displayContent = response.newContent || response.display || newValue;
                        if (Array.isArray(newValue) && newValue.length === 0) displayContent = '<span class=\"badge bg-light text-dark\">Ninguno</span>';
                        cell.html(displayContent);
                        cell.removeClass('editing');
                        editingCell = null;

                        // 🚀 Actualizar Restante automáticamente si se edita Total o Anticipo
                        if(fieldName === 'total' || fieldName === 'anticipo'){
                            actualizarRestante(recordId);
                        }
                    } else {
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
                    showNotification('Error de conexión: '+error, 'error');
                },
                complete: function() { saveBtn.prop('disabled', false).html('<i class=\"fas fa-save me-1\"></i>Guardar'); }
            });
        });

        // Cancelar
        cell.off('click.cancel').on('click.cancel', '.cancel-btn', function(e){
            e.preventDefault(); e.stopPropagation();
            cell.html(originalContent); cell.removeClass('editing'); editingCell = null;
        });

        // Prevenir clicks que cierren edición
        cell.off('click.prevent').on('click.prevent', '.multiselect-checkbox, .form-check-label, .multiselect-container', function(e){ e.stopPropagation(); });

        // Enter y Escape
        cell.off('keydown.edit').on('keydown.edit', '.inline-input', function(e){
            if(e.keyCode===13) cell.find('.save-btn').click();
            else if(e.keyCode===27) cell.find('.cancel-btn').click();
        });
    }

    // Cargar opciones select
    function loadSelectOptions(fieldName, currentValue, callback){
        $.get('" . Url::to(['get-select-options']) . "', {field: fieldName}, function(response){
            callback(response.options || []);
        }, 'json');
    }

    // Cargar opciones multiselect
    function loadMultiSelectOptions(catalogType, currentValue, callback){
        $.get('" . Url::to(['get-multiselect-options']) . "', {type: catalogType}, function(response){
            callback(response.options || []);
        }, 'json');
    }

    // Notificaciones
    function showNotification(message, type){
        let alertClass = type==='success'?'alert-success':'alert-danger';
        let icon = type==='success'?'fa-check-circle':'fa-exclamation-triangle';
        let notification = '<div class=\"alert '+alertClass+' alert-dismissible fade show notification-toast\" role=\"alert\" style=\"position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;\">'+
            '<i class=\"fas '+icon+' me-2\"></i>'+message+
            '<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button></div>';
        $('body').append(notification);
        setTimeout(function(){ $('.notification-toast').fadeOut(function(){ $(this).remove(); }); }, 3000);
    }

    // Cerrar edición si se hace click fuera
    $(document).on('click', function(e){
        if(editingCell!==null && !$(e.target).closest('.edit-container').length){
            let cancelBtn = editingCell.find('.cancel-btn');
            if(cancelBtn.length>0) cancelBtn.click();
        }
    });
});
");
?>


<?php
function generarColorUnico($texto) {
    $textoLower = strtolower(trim($texto));
    
    // Mantener el estilo especial para "urgente"
    if ($textoLower === 'urgente') {
        return ['bg' => '#dc3545', 'text' => '#ffffff']; // Rojo brillante
    }
    
    // Paleta de colores suaves similar a tu imagen
    $coloresSuaves = [
        ['bg' => '#e3f2fd', 'text' => '#1565c0'], // Azul suave
        ['bg' => '#e8f5e8', 'text' => '#2e7d32'], // Verde suave
        ['bg' => '#fff3e0', 'text' => '#ef6c00'], // Naranja suave
        ['bg' => '#f3e5f5', 'text' => '#7b1fa2'], // Morado suave
        ['bg' => '#e0f2f1', 'text' => '#00695c'], // Verde agua suave
        ['bg' => '#fce4ec', 'text' => '#c2185b'], // Rosa suave
        ['bg' => '#f5f5f5', 'text' => '#424242'], // Gris suave
        ['bg' => '#e1f5fe', 'text' => '#0277bd'], // Cian suave
        ['bg' => '#fff8e1', 'text' => '#f57f17'], // Amarillo suave
        ['bg' => '#f9fbe7', 'text' => '#689f38'], // Verde lima suave
        ['bg' => '#fef7ff', 'text' => '#8e24aa'], // Lavanda suave
        ['bg' => '#e8eaf6', 'text' => '#3f51b5'], // Índigo suave
    ];
    
    // Generar índice basado en el texto para consistencia
    $hash = crc32($texto);
    $indice = abs($hash) % count($coloresSuaves);
    
    return $coloresSuaves[$indice];
}
?>

<div class="ventas-index">

    <!-- Mensajes flash -->
    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?= Yii::$app->session->getFlash('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <h1 class="mb-3"><?= Html::encode($this->title) ?></h1>

    <!-- Botón debajo del título, alineado a la izquierda -->
    <div class="mb-4">
        <button type="button" 
                class="btn btn-success btn-lg" 
                data-bs-toggle="modal" 
                data-bs-target="#modalNuevaVenta">
            <i class="fas fa-plus me-2"></i>
            Nueva Venta
        </button>
    </div>

   <!-- Modal para Nueva Venta -->
<div class="modal fade" id="modalNuevaVenta" tabindex="-1" aria-labelledby="modalNuevaVentaLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modalNuevaVentaLabel">
                    <i class="fas fa-shopping-cart me-2"></i>
                    Nueva Venta
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body">
                <?= $this->render('_form', ['model' => $modeloNuevo]) ?>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-cancelar me-2" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    Cancelar
                </button>
                <button type="submit" class="btn btn-printy btn-lg" form="ventasForm">
                    <i class="fas fa-save me-1"></i>
                    Guardar Venta
                </button>
            </div>
        </div>
    </div>
</div>



    <?= CustomGridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            
            // Tipo Letrero - EDITABLE
            [
                'attribute' => 'tipo_letrero_id',
                'format' => 'raw',
                'value' => function($model) {
                    if (!$model->tipoLetrero) {
                        $badge = '<span class="badge bg-secondary">No definido</span>';
                    } else {
                        $colores = generarColorUnico($model->tipoLetrero->nombre);
                        $badge = '<span class="badge" style="background-color: ' . $colores['bg'] . '; color: ' . $colores['text'] . ';">' . 
                               $model->tipoLetrero->nombre . '</span>';
                    }
                    
                    return "<div class=\"editable-field\" data-field-type=\"select\" data-field-name=\"tipo_letrero_id\" data-record-id=\"{$model->id}\" data-current-value=\"{$model->tipo_letrero_id}\" title=\"Click para editar\">{$badge}</div>";
                },
                'label' => 'Tipo Letrero'
            ],

            // Nombre del letrero - EDITABLE
            [
                'attribute' => 'nombre_letrero',
                'format' => 'raw',
                'value' => function($model) {
                    $nombre = Html::encode($model->nombre_letrero);
                    return "<div class=\"editable-field\" data-field-type=\"text\" data-field-name=\"nombre_letrero\" data-record-id=\"{$model->id}\" data-current-value=\"{$nombre}\" title=\"Click para editar\">{$nombre}</div>";
                },
                'label' => 'Nombre Letrero'
            ],

            // Entrega - EDITABLE
            [
                'attribute' => 'entrega_id',
                'format' => 'raw',
                'value' => function($model) {
                    if (!$model->entrega) {
                        $badge = '<span class="badge bg-secondary">No definido</span>';
                    } else {
                        $colores = generarColorUnico($model->entrega->nombre);
                        $badge = '<span class="badge" style="background-color: ' . $colores['bg'] . '; color: ' . $colores['text'] . ';">' . 
                               $model->entrega->nombre . '</span>';
                    }
                    
                    return "<div class=\"editable-field\" data-field-type=\"select\" data-field-name=\"entrega_id\" data-record-id=\"{$model->id}\" data-current-value=\"{$model->entrega_id}\" title=\"Click para editar\">{$badge}</div>";
                },
                'label' => 'Entrega'
            ],

            // Adicionales - EDITABLE (many-to-many con multi-select)
            [
                'attribute' => 'adicionales',
                'format' => 'raw',
                'value' => function($model) {
                    $adicionales = $model->adicionales;
                    $adicionalesIds = [];
                    $badges = '';
                    
                    if (!empty($adicionales)) {
                        foreach ($adicionales as $adicional) {
                            $adicionalesIds[] = $adicional->id;
                            $colores = generarColorUnico($adicional->nombre);
                            $badges .= '<span class="badge me-1 mb-1" style="background-color: ' . $colores['bg'] . '; color: ' . $colores['text'] . ';">' . 
                                      $adicional->nombre . '</span>';
                        }
                    }
                    
                    if (empty($badges)) {
                        $badges = '<span class="badge bg-light text-dark">Ninguno</span>';
                    }
                    
                    $adicionalesIdsStr = implode(',', $adicionalesIds);
                    return "<div class=\"editable-field\" data-field-type=\"multiselect\" data-field-name=\"adicionales\" data-record-id=\"{$model->id}\" data-current-value=\"{$adicionalesIdsStr}\" data-catalog-type=\"adicional\" title=\"Click para editar\">{$badges}</div>";
                },
                'label' => 'Adicionales'
            ],

            // Extras - EDITABLE (many-to-many con multi-select)
            [
                'attribute' => 'extras',
                'format' => 'raw',
                'value' => function($model) {
                    $extras = $model->extras;
                    $extrasIds = [];
                    $badges = '';
                    
                    if (!empty($extras)) {
                        foreach ($extras as $extra) {
                            $extrasIds[] = $extra->id;
                            $colores = generarColorUnico($extra->nombre);
                            $badges .= '<span class="badge me-1 mb-1" style="background-color: ' . $colores['bg'] . '; color: ' . $colores['text'] . ';">' . 
                                      $extra->nombre . '</span>';
                        }
                    }
                    
                    if (empty($badges)) {
                        $badges = '<span class="badge bg-light text-dark">Ninguno</span>';
                    }
                    
                    $extrasIdsStr = implode(',', $extrasIds);
                    return "<div class=\"editable-field\" data-field-type=\"multiselect\" data-field-name=\"extras\" data-record-id=\"{$model->id}\" data-current-value=\"{$extrasIdsStr}\" data-catalog-type=\"extra\" title=\"Click para editar\">{$badges}</div>";
                },
                'label' => 'Extras'
            ],

            // Medio - EDITABLE
            [
                'attribute' => 'medio_id',
                'format' => 'raw',
                'value' => function($model) {
                    if (!$model->medio) {
                        $badge = '<span class="badge bg-secondary">No definido</span>';
                    } else {
                        $colores = generarColorUnico($model->medio->nombre);
                        $badge = '<span class="badge" style="background-color: ' . $colores['bg'] . '; color: ' . $colores['text'] . ';">' . 
                               $model->medio->nombre . '</span>';
                    }
                    
                    return "<div class=\"editable-field\" data-field-type=\"select\" data-field-name=\"medio_id\" data-record-id=\"{$model->id}\" data-current-value=\"{$model->medio_id}\" title=\"Click para editar\">{$badge}</div>";
                },
                'label' => 'Medio'
            ],

            // Teléfono - EDITABLE
            [
                'attribute' => 'telefono',
                'format' => 'raw',
                'value' => function($model) {
                    $telefono = Html::encode($model->telefono);
                    return "<div class=\"editable-field\" data-field-type=\"text\" data-field-name=\"telefono\" data-record-id=\"{$model->id}\" data-current-value=\"{$telefono}\" title=\"Click para editar\">{$telefono}</div>";
                },
                'label' => 'Teléfono'
            ],

            // Campaña - EDITABLE
            [
                'attribute' => 'campaña_id',
                'format' => 'raw',
                'value' => function($model) {
                    if (!$model->campaña) {
                        $badge = '<span class="badge bg-secondary">No definido</span>';
                    } else {
                        $colores = generarColorUnico($model->campaña->nombre);
                        $badge = '<span class="badge" style="background-color: ' . $colores['bg'] . '; color: ' . $colores['text'] . ';">' . 
                               $model->campaña->nombre . '</span>';
                    }
                    
                    return "<div class=\"editable-field\" data-field-type=\"select\" data-field-name=\"campaña_id\" data-record-id=\"{$model->id}\" data-current-value=\"{$model->campaña_id}\" title=\"Click para editar\">{$badge}</div>";
                },
                'label' => 'Campaña'
            ],

            // Asesor - EDITABLE
            [
                'attribute' => 'asesor_id',
                'format' => 'raw',
                'value' => function($model) {
                    if (!$model->asesor) {
                        $badge = '<span class="badge bg-secondary">No definido</span>';
                    } else {
                        $colores = generarColorUnico($model->asesor->nombre);
                        $badge = '<span class="badge" style="background-color: ' . $colores['bg'] . '; color: ' . $colores['text'] . ';">' . 
                               $model->asesor->nombre . '</span>';
                    }
                    
                    return "<div class=\"editable-field\" data-field-type=\"select\" data-field-name=\"asesor_id\" data-record-id=\"{$model->id}\" data-current-value=\"{$model->asesor_id}\" title=\"Click para editar\">{$badge}</div>";
                },
                'label' => 'Asesor'
            ],

            // Unidades - EDITABLE
            [
                'attribute' => 'unidades',
                'format' => 'raw',
                'value' => function($model) {
                    return "<div class=\"editable-field\" data-field-type=\"number\" data-field-name=\"unidades\" data-record-id=\"{$model->id}\" data-current-value=\"{$model->unidades}\" title=\"Click para editar\">{$model->unidades}</div>";
                },
                'label' => 'Unidades'
            ],

            // Precio Extra - EDITABLE
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

            // Precio Total - EDITABLE
            [
                'attribute' => 'precio_total',
                'format' => 'raw',
                'value' => function($model) {
                    $valor = $model->precio_total ? $model->precio_total : 0;
                    $mostrar = '$' . number_format($valor, 2);
                    return "<div class=\"editable-field\" style=\"color:green; font-weight:bold;\" data-field-type=\"number\" data-field-name=\"precio_total\" data-record-id=\"{$model->id}\" data-current-value=\"{$valor}\" title=\"Click para editar\">{$mostrar}</div>";
                },
                'label' => 'Precio Total'
            ],

            // Anticipo
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

            // Restante
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

            // Fecha de compra - EDITABLE
            [
                'attribute' => 'fecha_compra',
                'format' => 'raw',
                'value' => function($model) {
                    $fechaMostrar = $model->fecha_compra ? Yii::$app->formatter->asDate($model->fecha_compra, 'php:d/m/Y') : '';
                    $fechaValue = $model->fecha_compra ? Yii::$app->formatter->asDate($model->fecha_compra, 'php:Y-m-d') : '';
                    return "<div class=\"editable-field\" data-field-type=\"date\" data-field-name=\"fecha_compra\" data-record-id=\"{$model->id}\" data-current-value=\"{$fechaValue}\" title=\"Click para editar\">{$fechaMostrar}</div>";
                },
                'label' => 'Fecha de Compra'
            ],

            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Ventas $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                }
            ],

        ],
    ]); 

    // CSS para el modal
$this->registerCss("
    .modal-xl {
        max-width: 95%;
        width: 1400px;
    }
    .modal-content {
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }
    .modal-header {
        border-radius: 15px 15px 0 0;
    }
");

// JavaScript para el modal
$this->registerJs("
    $('#modalNuevaVenta').on('hidden.bs.modal', function () {
        $('#ventasForm')[0].reset();
        if (typeof calcularRestante === 'function') {
            calcularRestante();
        }
    });
    
    $('#modalNuevaVenta').on('shown.bs.modal', function () {
        $('#tipo_letrero_id').focus();
    });
    
    $('#modalNuevaVenta').on('click', 'button[type=submit]', function(e) {
        e.preventDefault();
        var btn = $(this);
        btn.prop('disabled', true).html('<i class=\"fas fa-spinner fa-spin me-1\"></i>Guardando...');
        $('#ventasForm').submit();
    });
");
    ?>

    

</div>


