<?php
use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Permisos';
?>
<p>
    <?= Html::a('Crear permiso', ['create'], ['class' => 'btn btn-success']) ?>
</p>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        'id',
        'modulo',
        'accion',
        'nombre',
        'descripcion',
        [
            'attribute' => 'activo',
            'value' => fn($m) => $m->activo ? 'Sí' : 'No',
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{update} {delete}', // solo Editar y Eliminar
            'visibleButtons' => [
                'view' => false, // oculta “ver” por si acaso
            ],
            // Si necesitas forzar las URLs con id:
            // 'urlCreator' => function ($action, $model) {
            //     return ["permisos/$action", 'id' => $model->id];
            // },
        ],
    ],
]); ?>