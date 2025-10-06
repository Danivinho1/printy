<?php
use yii\helpers\Html;

/* @var $model app\models\Usuario */
/* @var $roles array */

$this->title = 'Crear Usuario';
?>
<div class="usuario-create">
    <h1><?= Html::encode($this->title) ?></h1>
    <?= $this->render('_form', compact('model', 'roles')) ?>
</div>