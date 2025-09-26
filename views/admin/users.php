<?php
use yii\grid\GridView;
use yii\helpers\Html;

/* @var $dataProvider yii\data\ActiveDataProvider */
$this->title = 'Usuarios';
?>
<div class="user-index">
    <p>
        <?= Html::a('Crear usuario', ['create-user'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            'username',
            'email',
            'nombre_completo',
            [
                'attribute' => 'role_id',
                'label' => 'Rol',
                'value' => fn($model) => $model->role->nombre ?? '-',
            ],
            [
                'attribute' => 'activo',
                'value' => fn($model) => $model->activo ? 'Sí' : 'No',
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{assign} {update} {delete}',
                'buttons' => [
                    'assign' => fn($url, $model) => Html::a('Asignar rol', ['assign-role', 'id' => $model->id], ['class' => 'btn btn-sm btn-secondary']),
                    'update' => fn($url, $model) => Html::a('Editar', ['update-user', 'id' => $model->id], ['class' => 'btn btn-sm btn-primary']),
                    'delete' => fn($url, $model) => Html::a('Desactivar', ['delete-user', 'id' => $model->id], [
                        'class' => 'btn btn-sm btn-danger',
                        'data' => ['confirm' => '¿Desactivar este usuario?', 'method' => 'post'],
                    ]),
                ],
            ],
        ],
    ]) ?>
</div>