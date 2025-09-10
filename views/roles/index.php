<?php
use yii\grid\GridView;
use yii\helpers\Html;

/* @var $dataProvider yii\data\ActiveDataProvider */
$this->title = 'Roles';
?>
<p>
    <?= Html::a('Crear rol', ['create'], ['class' => 'btn btn-success']) ?>
</p>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        'id',
        'nombre',
        'descripcion',
        [
            'attribute' => 'es_admin',
            'value' => fn($m) => $m->es_admin ? 'Sí' : 'No',
        ],
        [
            'attribute' => 'activo',
            'value' => fn($m) => $m->activo ? 'Sí' : 'No',
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{assign} {update} {delete}',
            'buttons' => [
                'assign' => fn($url, $model) => Html::a('Permisos', ['assign', 'id' => $model->id], ['class' => 'btn btn-sm btn-secondary']),
                'update' => fn($url, $model) => Html::a('Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-sm btn-primary']),
                'delete' => fn($url, $model) => Html::a('Desactivar', ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-sm btn-danger',
                    'data' => ['confirm' => '¿Desactivar este rol?', 'method' => 'post'],
                ]),
            ],
        ],
    ],
]); ?>