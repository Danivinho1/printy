<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Catalogos $model */

$this->title = 'Update Catalogos: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Catalogos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="catalogos-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
