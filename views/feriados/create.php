<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Feriados $model */

$this->title = 'Create Feriados';
$this->params['breadcrumbs'][] = ['label' => 'Feriados', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="feriados-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
