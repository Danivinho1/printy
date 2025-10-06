<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $model app\models\Usuario */
/* @var $roles array */
?>
<div class="usuario-form">
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'email')->input('email') ?>
    <?= $form->field($model, 'nombre_completo')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'role_id')->dropDownList($roles, ['prompt' => 'Selecciona un rol']) ?>
    <?= $form->field($model, 'activo')->checkbox() ?>
    <?= $form->field($model, 'new_password')->passwordInput()->hint('Dejar vacío para no cambiar') ?>

    <div class="form-group">
        <?= Html::submitButton('Guardar', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>