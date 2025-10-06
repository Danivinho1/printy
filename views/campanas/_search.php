<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\CampanasSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="campanas-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'campaña_id') ?>

    <?= $form->field($model, 'asesor_id') ?>

    <?= $form->field($model, 'inversion') ?>

    <?= $form->field($model, 'mensajes') ?>

    <?php // echo $form->field($model, 'retorno') ?>

    <?php // echo $form->field($model, 'analisis') ?>

    <?php // echo $form->field($model, 'mensaje_predeterminado') ?>

    <?php // echo $form->field($model, 'presupuesto') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
