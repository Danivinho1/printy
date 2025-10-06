<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\Permiso;

/** @var yii\web\View $this */
/** @var app\models\Role $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="role-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'nombre')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'descripcion')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'es_admin')->textInput() ?>

    <?= $form->field($model, 'activo')->textInput() ?>

    <!-- Permisos asignados al rol -->
    <?= $form->field($model, 'permisos')->checkboxList(
        ArrayHelper::map(Permiso::find()->all(), 'id', 'nombre'),
        [
            'separator' => '<br>',
            'itemOptions' => ['class' => 'mx-1 my-1'],
        ]
    )->label('Permisos asignados') ?>

    <!-- Puedes ocultar estos campos si se llenan automáticamente -->
    <?php //= $form->field($model, 'created_at')->textInput() ?>
    <?php //= $form->field($model, 'updated_at')->textInput() ?>

    <div class="form-group mt-3">
        <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>