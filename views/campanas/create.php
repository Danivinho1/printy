<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Campanas $model */

$this->title = 'Create Campanas';
$this->params['breadcrumbs'][] = ['label' => 'Campanas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="campanas-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
