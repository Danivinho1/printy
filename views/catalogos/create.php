<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Catalogos $model */

$this->title = 'Create Catalogos';
$this->params['breadcrumbs'][] = ['label' => 'Catalogos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="catalogos-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
