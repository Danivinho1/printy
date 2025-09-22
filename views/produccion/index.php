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

// En tu controlador o al inicio de la vista
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
                  if ($model->venta && $model->venta->fecha_entrega) {
                      $hoy = new \DateTime();
                      $fechaEntrega = new \DateTime($model->venta->fecha_entrega);
          
                      $diasHabiles = 0;
                      $fechaIter = clone $hoy;
          
                      // Contar solo lunes a viernes
                      while ($fechaIter <= $fechaEntrega) {
                          $diaSemana = (int)$fechaIter->format('N'); // 1=lunes, 7=domingo
                          if ($diaSemana < 6) { 
                              $diasHabiles++;
                          }
                          $fechaIter->modify('+1 day');
                      }
          
                      // Determinar color del badge
                      if ($diasHabiles > 5) {
                          $color = 'bg-success';
                      } elseif ($diasHabiles >= 1) {
                          $color = 'bg-warning text-dark';
                      } else {
                          $color = 'bg-danger';
                      }
          
                      return '<span class="badge '.$color.'">'.$diasHabiles.' día'.($diasHabiles == 1 ? '' : 's').'</span>';
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


