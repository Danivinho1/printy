<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Produccion $model */

$this->title = 'Update Produccion: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Produccions', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="produccion-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
