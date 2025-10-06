<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Ventas $model */


?>
<div class="ventas-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
