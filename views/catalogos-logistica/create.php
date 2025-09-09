<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Logistica $model */

$this->title = 'Create Logistica';
$this->params['breadcrumbs'][] = ['label' => 'Logisticas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="logistica-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
