<?php

use app\models\Logistica;
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

?>

<div class="logistica-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= CustomGridView::widget([
        'dataProvider' => $dataProvider,
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
                'attribute' => 'chapetones',
                'label' => 'Chapetones',
                'format' => 'raw',
                'value' => function ($model) {
                    if (!$model->venta) return '<span class="badge bg-secondary">Ninguno</span>';

                    $extrasNombres = $model->venta->getExtrasNombres();
                    if ($extrasNombres === 'Ninguno') return '<span class="badge bg-secondary">Ninguno</span>';

                    $extrasArray = explode(', ', $extrasNombres);
                    $chapetones = [];
                    foreach ($extrasArray as $extra) {
                        if (stripos($extra, 'chapetones') !== false) {
                            $chapetones[] = $extra;
                        }
                    }

                    if (empty($chapetones)) return '<span class="badge bg-secondary">Ninguno</span>';

                    $badges = '';
                    foreach ($chapetones as $chapeton) {
                        $badges .= '<span class="badge bg-info text-dark me-1">' . Html::encode($chapeton) . '</span>';
                    }

                    return $badges;
                },
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

            [
            'attribute' => 'estatus_pago',
            'format' => 'raw',
            'value' => function($model) {
                $restante = $model->total - $model->anticipo;
                $estatusText = $restante > 0 ? 'Por liquidar' : 'Liquidado';
                $estatusColor = $restante > 0 ? '#ffc107' : '#28a745';
                return Html::tag('span', $estatusText, [
                    'id' => 'estatus-pago-' . $model->id,
                    'class' => 'badge',
                    'style' => 'background-color:' . $estatusColor . '; color:#fff;'
                ]);
            }
        ],

        ],
    ]); ?>

</div>

<?php
$this->registerJs("
$(document).ready(function() {
    let editingCell = null;

    // Función para actualizar Restante en la misma tabla
    function actualizarRestante(recordId, total, anticipo){
        let restante = Math.max(0, total - anticipo);
        let restanteHtml = '<span class=\"badge\" style=\"color:red; font-weight:bold;\">$' + restante.toFixed(2) + '</span>';

        // Actualiza la celda de restante en Logística
        $('.editable-field[data-field-name=\"restante\"][data-record-id=\"'+recordId+'\"]')
            .each(function(){
                $(this).html(restanteHtml);
                $(this).data('current-value', restante);
            });

        // Opcional: actualizar también en Ventas vía AJAX
        $.post('" . Url::to(['ventas/actualizar-restante']) . "', 
            {id: recordId, _csrf: yii.getCsrfToken()}, 
            function(res){
                if(res.success){
                    // Si quieres reflejarlo en otras vistas o tablas de Ventas
                    console.log('Restante sincronizado en Ventas:', res.restante);
                }
            }, 'json'
        );
    }

    // Evento principal para editar campos
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
        let inputElement;

        // Campos tipo número, texto o select simple
        let inputType = fieldType === 'number' ? 'number' : 'text';
        let step = fieldType === 'number' ? 'step=\"0.01\"' : '';
        inputElement = '<input type=\"' + inputType + '\" class=\"inline-input form-control\" value=\"' + (currentValue || '') + '\" ' + step + '>';
        createEditContainer(inputElement);

        function createEditContainer(inputEl){
            let editContainer = '<div class=\"edit-container\" style=\"min-width: 200px;\">'+
                                inputEl+
                                '<div class=\"save-cancel-buttons mt-2 d-flex gap-2\">'+
                                '<button type=\"button\" class=\"btn btn-success btn-sm save-btn\" data-record-id=\"'+recordId+'\" data-field-name=\"'+fieldName+'\">Guardar</button>'+
                                '<button type=\"button\" class=\"btn btn-secondary btn-sm cancel-btn\">Cancelar</button>'+
                                '</div></div>';
            cell.html(editContainer);
            let focusElement = cell.find('.inline-input');
            if(focusElement.length>0) setTimeout(()=>focusElement.focus(),100);
            bindEditEvents(cell, recordId, fieldName, originalContent);
        }
    });

    function bindEditEvents(cell, recordId, fieldName, originalContent){
        // Guardar cambios
        cell.off('click.save').on('click.save', '.save-btn', function(e){
            e.preventDefault(); e.stopPropagation();
            let saveBtn = $(this);
            let newValue = parseFloat(cell.find('.inline-input').val()) || 0;

            $.ajax({
                url: '" . Url::to(['update-field']) . "',
                type: 'POST',
                dataType: 'json',
                data: {id: recordId, field: fieldName, value: newValue, _csrf: yii.getCsrfToken()},
                beforeSend: ()=>saveBtn.prop('disabled',true).html('<i class=\"fas fa-spinner fa-spin me-1\"></i>Guardando...'),
                success: function(response){
                    if(response && response.success){
                        // Actualiza la celda propia
                        cell.data('current-value', newValue);
                        cell.html(response.newContent || newValue);
                        cell.removeClass('editing'); editingCell = null;

                        // Actualiza restante automáticamente si se editó total o anticipo
                        if(fieldName==='total' || fieldName==='anticipo'){
                            let total = fieldName==='total' ? newValue : parseFloat($('.editable-field[data-field-name=\"total\"][data-record-id=\"'+recordId+'\"]').data('current-value')) || 0;
                            let anticipo = fieldName==='anticipo' ? newValue : parseFloat($('.editable-field[data-field-name=\"anticipo\"][data-record-id=\"'+recordId+'\"]').data('current-value')) || 0;
                            actualizarRestante(recordId, total, anticipo);
                        }
                    } else {
                        cell.html(originalContent); cell.removeClass('editing'); editingCell = null;
                        alert(response.message || 'Error al actualizar');
                    }
                },
                error: function(xhr,status,error){
                    console.error('Error AJAX:',xhr.responseText,status,error);
                    cell.html(originalContent); cell.removeClass('editing'); editingCell = null;
                    alert('Error de conexión');
                },
                complete: ()=>saveBtn.prop('disabled',false).html('Guardar')
            });
        });

        // Cancelar edición
        cell.off('click.cancel').on('click.cancel', '.cancel-btn', function(e){
            e.preventDefault(); e.stopPropagation();
            cell.html(originalContent); cell.removeClass('editing'); editingCell = null;
        });

        // Enter y Escape
        cell.off('keydown.edit').on('keydown.edit', '.inline-input', function(e){
            if(e.keyCode===13) cell.find('.save-btn').click();
            else if(e.keyCode===27) cell.find('.cancel-btn').click();
        });
    }
});
");
?>

