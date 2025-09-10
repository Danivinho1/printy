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
        ['class' => 'yii\grid\ActionColumn'],
    ],
]); ?>