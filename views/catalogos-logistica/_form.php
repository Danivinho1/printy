<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Logistica $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="logistica-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'venta_id')->textInput() ?>

    <?= $form->field($model, 'tipo_letrero')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'nombre_letrero')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'chapetones')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'num_chapetones')->textInput() ?>

    <?= $form->field($model, 'telefono')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'total')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'anticipo')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'restante')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'estatus_pago_id')->textInput() ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
