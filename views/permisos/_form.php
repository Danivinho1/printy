<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $model app\models\Permiso */
$this->title = $model->isNewRecord ? 'Crear permiso' : 'Editar permiso';
?>
<h1><?= Html::encode($this->title) ?></h1>

<?php $form = ActiveForm::begin(); ?>
<?= $form->field($model, 'modulo')->textInput(['maxlength' => true])->hint('Ej: ventas') ?>
<?= $form->field($model, 'accion')->textInput(['maxlength' => true])->hint('Ej: crear, ver, editar') ?>
<?= $form->field($model, 'nombre')->textInput(['maxlength' => true])->hint('Etiqueta legible') ?>
<?= $form->field($model, 'descripcion')->textInput(['maxlength' => true]) ?>
<?= $form->field($model, 'activo')->checkbox() ?>

<div class="form-group">
    <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
    <?= Html::a('Volver', ['index'], ['class' => 'btn btn-default']) ?>
</div>
<?php ActiveForm::end(); ?>