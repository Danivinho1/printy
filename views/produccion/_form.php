<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Produccion $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="produccion-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'venta_id')->textInput() ?>

    <?= $form->field($model, 'diseno_id')->textInput() ?>

    <?= $form->field($model, 'tipo_letrero_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'nombre_letrero')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'disenador_id')->textInput() ?>

    <?= $form->field($model, 'unidades')->textInput() ?>

    <?= $form->field($model, 'chapetones')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'diseno_impresion')->textInput() ?>

    <?= $form->field($model, 'corte_id')->textInput() ?>

    <?= $form->field($model, 'fabricacion_id')->textInput() ?>

    <?= $form->field($model, 'fecha_confirmacion')->textInput() ?>

    <?= $form->field($model, 'dias_restantes')->textInput() ?>

    <?= $form->field($model, 'estatus_pago_id')->textInput() ?>

    <?= $form->field($model, 'envio_id')->textInput() ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
