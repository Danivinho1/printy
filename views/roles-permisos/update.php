<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\RolesPermisos $model */

$this->title = 'Update Roles Permisos: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Roles Permisos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="roles-permisos-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
