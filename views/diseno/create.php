<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Diseno $model */

$this->title = 'Create Diseno';
$this->params['breadcrumbs'][] = ['label' => 'Disenos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="diseno-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
