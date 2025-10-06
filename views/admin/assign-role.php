<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $user app\models\User */
/* @var $roles array */
$this->title = "Asignar rol a: {$user->username}";
?>
<h1><?= Html::encode($this->title) ?></h1>

<?php $form = ActiveForm::begin(); ?>

<?= Html::dropDownList('role_id', $user->role_id, $roles, ['class' => 'form-control', 'prompt' => 'Selecciona un rol']) ?>
<br>
<div class="form-group">
    <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
    <?= Html::a('Volver', ['users'], ['class' => 'btn btn-default']) ?>
</div>

<?php ActiveForm::end(); ?>