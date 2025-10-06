<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var app\models\LoginForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;


$this->params['breadcrumbs'][] = $this->title;



// Registrar Font Awesome para iconos

?>

<div class="site-login">
<div class="login-container">

        <!-- Imagen en lugar del icono -->
        <div class="login-icon">
    <img src="<?= Yii::$app->request->baseUrl ?>/images/login2.png" alt="Login2">
</div>



        
        <h1 class="login-title"><?= Html::encode($this->title) ?></h1>
        <p class="login-subtitle">Bienvenido de vuelta</p>

        <?php $form = ActiveForm::begin([
            'id' => 'login-form',
            'fieldConfig' => [
                'template' => "<div class=\"form-floating\">{input}{label}</div>\n{error}",
                'inputOptions' => ['class' => 'form-control'],
                'errorOptions' => ['class' => 'invalid-feedback'],
            ],
        ]); ?>

        <?= $form->field($model, 'username')->textInput([
            'autofocus' => true,
            'placeholder' => 'Usuario',
            'id' => 'username-input'
        ])->label('Usuario') ?>

        <?= $form->field($model, 'password')->passwordInput([
            'placeholder' => 'Contraseña',
            'id' => 'password-input'
        ])->label('Contraseña') ?>

        <div class="custom-checkbox-container">
            <?= $form->field($model, 'rememberMe')->checkbox([
                'template' => "<div class=\"custom-checkbox\">{input} {label}</div>\n{error}",
                'labelOptions' => ['class' => ''],
                'uncheck' => null,
            ])->label('Recordarme') ?>
        </div>

        <div class="form-group">
            <?= Html::submitButton('<i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión', [
                'class' => 'btn btn-login', 
                'name' => 'login-button'
            ]) ?>
        </div>

        <?php ActiveForm::end(); ?>

        </div>
    </div>
</div>
