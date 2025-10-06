<?php

use app\models\RolePermiso;
use app\models\RolesPermisos;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Roles Permisos';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="roles-permisos-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Roles Permisos', ['create'], ['class' => 'btn btn-success']) ?>
    </p>


    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'role_id',
            'permiso_id',
            'created_at',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, RolePermiso $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>


</div>
