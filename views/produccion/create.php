<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Produccion $model */

$this->title = 'Create Produccion';
$this->params['breadcrumbs'][] = ['label' => 'Produccions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="produccion-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
