<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Campanas $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="campanas-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'campaña_id')->textInput() ?>

    <?= $form->field($model, 'asesor_id')->textInput() ?>

    <?= $form->field($model, 'inversion')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'mensajes')->textInput() ?>

    <?= $form->field($model, 'retorno')->textInput() ?>

    <?= $form->field($model, 'analisis')->dropDownList([ 'Analizar' => 'Analizar', 'Pausar' => 'Pausar', 'Detener' => 'Detener', 'Continuar' => 'Continuar', 'Experimento' => 'Experimento', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'mensaje_predeterminado')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'presupuesto')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
