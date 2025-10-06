<?php
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $model app\models\Usuario */

$this->title = $model->username;
?>
<div class="usuario-view">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Actualizar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Eliminar', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => '¿Eliminar este usuario?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'username',
            'email:email',
            'nombre_completo',
            [
                'label' => 'Rol',
                'value' => $model->role ? $model->role->nombre : '-',
            ],
            [
                'attribute' => 'activo',
                'value' => $model->activo ? 'Sí' : 'No',
            ],
            'created_at',
            'updated_at',
        ],
    ]) ?>
</div>