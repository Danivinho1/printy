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
                'label' => 'Extras',
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
