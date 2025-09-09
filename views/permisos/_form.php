<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Permisos $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="permisos-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'modulo')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'accion')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'nombre')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'descripcion')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'activo')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
