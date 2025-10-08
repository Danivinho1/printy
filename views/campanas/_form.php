<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Catalogos;

/** @var yii\web\View $this */
/** @var app\models\Campanas $model */
/** @var yii\widgets\ActiveForm $form */

// Opciones de catálogos (rápido). Si prefieres, pásalas desde el controlador.
$tipos = Catalogos::find()->select(['nombre','id'])->where(['tipo'=>'campaña'])->orderBy('nombre')->indexBy('id')->column();
$asesores = Catalogos::find()->select(['nombre','id'])->where(['tipo'=>'asesor'])->orderBy('nombre')->indexBy('id')->column();
?>

<div class="campanas-form">
    <?php $form = ActiveForm::begin([
        'id' => 'campanasForm',               // Importante: coincide con el botón del modal
        'action' => ['campanas/create'],      // Importante: ruta que procesa el guardado
        'method' => 'post',
        'options' => ['data-pjax' => 0],
    ]); ?>

    <div class="row g-3">
        <div class="col-md-6">
            <?= $form->field($model, 'nombre')->textInput(['maxlength' => true, 'placeholder' => 'Nombre de la campaña']) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'campaña_id')->dropDownList($tipos, ['prompt' => '-- Selecciona tipo --']) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'asesor_id')->dropDownList($asesores, ['prompt' => '-- Selecciona asesor --']) ?>
        </div>

        <div class="col-md-3">
            <?= $form->field($model, 'inversion')->input('number', ['step'=>'0.01', 'min'=>'0']) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'presupuesto')->input('number', ['step'=>'0.01', 'min'=>'0']) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'retorno')->input('number', ['step'=>'0.01', 'min'=>'0']) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'mensajes')->input('number', ['step'=>'1', 'min'=>'0']) ?>
        </div>

        <div class="col-md-3">
            <?= $form->field($model, 'analisis')->dropDownList([
                'Analizar' => 'Analizar',
                'Continuar' => 'Continuar',
                'Pausar' => 'Pausar',
                'Detener' => 'Detener',
                'Experimento' => 'Experimento',
            ], ['prompt' => '-- Selecciona estado --']) ?>
        </div>

        <div class="col-12">
            <?= $form->field($model, 'mensaje_predeterminado')->textarea(['rows' => 3, 'placeholder' => 'Mensaje o guion tipo para esta campaña']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>