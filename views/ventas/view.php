<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Ventas $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Ventas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="ventas-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'tipo_letrero',
            'nombre_letrero',
            'entrega_id',
            'fecha_entrega',
            'adicionales_id',
            'extras_id',
            'medio_id',
            'telefono',
            'campaña_id',
            'asesor_id',
            'unidades',
            'extra_precio',
            'precio_total',
            'anticipo',
            'restante',
            'fecha_compra',
            'created_by',
            'created_at',
            'updated_at',
        ],
    ]) ?>

</div>
