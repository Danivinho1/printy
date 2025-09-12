<?php

use app\models\Produccion;
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
            [
                'attribute' => 'nombre_letrero',
                'label' => 'Nombre Letrero',
                'value' => function ($model) {
                    return $model->nombre_letrero;
                },
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
                    $checked = $model->diseno_impresion == 1;
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

            'dias_restantes',

            // Editable Empaquetado
            [
                'attribute' => 'empaquetado_id',
                'format' => 'raw',
                'label' => 'Estatus',
                'value' => function($model) use ($optionsEmpaquetadoJson) {
                    $nombre = $model->empaquetado? $model->empaquetado->nombre : 'Pendiente';
                    $color = strtolower($nombre) === 'listo para empaquetar' ? 'green' : 'red';
                    return "<div class='editable-empaquetado' 
                                data-record-id='{$model->id}' 
                                data-field-name='empaquetado_id'
                                data-options='{$optionsEmpaquetadoJson}'
                                style='cursor:pointer; display:inline-block;'>
                                <span class='badge' style='background-color:{$color}; color:white;'>{$nombre}</span>
                            </div>";
                },
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
                var color = newText.toLowerCase() === 'listo para empaquetar' ? 'green' : 'red';
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


