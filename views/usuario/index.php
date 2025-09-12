<?php
use yii\grid\GridView;
use yii\helpers\Html;

/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Usuarios';
?>
<div class="usuario-index">
    <h1><?= Html::encode($this->title) ?></h1>
    <p><?= Html::a('Crear Usuario', ['create'], ['class' => 'btn btn-success']) ?></p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            'username',
            'email:email',
            [
                'label' => 'Rol',
                'value' => fn($model) => $model->role ? $model->role->nombre : '-',
            ],
            [
                'attribute' => 'activo',
                'value' => fn($model) => $model->activo ? 'Sí' : 'No',
            ],
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]) ?>
</div>