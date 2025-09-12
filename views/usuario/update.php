<?php
use yii\helpers\Html;

/* @var $model app\models\Usuario */
/* @var $roles array */

$this->title = 'Actualizar Usuario: ' . $model->username;
?>
<div class="usuario-update">
    <h1><?= Html::encode($this->title) ?></h1>
    <?= $this->render('_form', compact('model', 'roles')) ?>
</div>