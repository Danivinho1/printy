<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $model app\models\forms\UserForm */
/* @var $roles array */
/* @var $title string */
$this->title = $title;
?>
<h1><?= Html::encode($this->title) ?></h1>

<div class="user-form">
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'email')->textInput(['type' => 'email']) ?>
    <?= $form->field($model, 'nombre_completo')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'role_id')->dropDownList($roles, ['prompt' => 'Selecciona un rol']) ?>
    <?= $form->field($model, 'password')->passwordInput()->hint('Deja vacío al editar si no deseas cambiarla') ?>

    <div class="form-group">
        <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
        <?= Html::a('Volver', ['users'], ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>