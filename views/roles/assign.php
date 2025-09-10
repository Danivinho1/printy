<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $role app\models\Role */
/* @var $allPerms app\models\Permiso[] */
/* @var $assigned int[] */

$this->title = "Permisos del rol: {$role->nombre}";
$items = [];
foreach ($allPerms as $p) {
    $items[$p->id] = "{$p->modulo}.{$p->accion} - {$p->nombre}";
}
?>
<h1><?= Html::encode($this->title) ?></h1>

<?php $form = ActiveForm::begin(); ?>

<?= Html::checkboxList('permisos', $assigned, $items, ['separator' => '<br>']) ?>
<br>
<div class="form-group">
    <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
    <?= Html::a('Volver', ['index'], ['class' => 'btn btn-default']) ?>
</div>

<?php ActiveForm::end(); ?>