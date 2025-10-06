<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Produccion $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Produccions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="produccion-view">

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
            'venta_id',
            'diseno_id',
            'tipo_letrero',
            'nombre_letrero',
            'disenador_id',
            'unidades',
            'chapetones',
            'diseno_impresion',
            'corte_id',
            'fabricacion_id',
            'fecha_confirmacion',
            'dias_restantes',
            'estatus_pago_id',
            'envio_id',
            'created_at',
            'updated_at',
        ],
    ]) ?>

</div>
