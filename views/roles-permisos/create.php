<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\RolePermiso $model */

$this->title = 'Create Roles Permisos';
$this->params['breadcrumbs'][] = ['label' => 'Roles Permisos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="roles-permisos-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
