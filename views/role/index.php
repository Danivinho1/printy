<?php
use app\models\Role;
use yii\helpers\Html;
use yii\helpers\Url;
use app\widgets\CustomGridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel app\models\RoleSearch|null */

$this->title = 'Roles';
$this->params['breadcrumbs'][] = $this->title;

// Conteos
$totalRegistros = (int) Role::find()->count();
$activosCount   = (int) Role::find()->where(['activo' => 1])->count();
$inactivosCount = (int) Role::find()->where(['activo' => 0])->count();

$formName = isset($searchModel) ? $searchModel->formName() : 'RoleSearch';
$filtroActivo = Yii::$app->request->get($formName)['activo'] ?? null;
?>

<div class="role-index">

  <h1><?= Html::encode($this->title) ?></h1>

  <div class="filtros-diseño mb-4">
    <div class="d-flex align-items-center gap-2 mb-3">

      <a href="<?= Url::to(['role/index']) ?>"
         class="btn-filtro <?= ($filtroActivo === null || $filtroActivo === '') ? 'active' : '' ?>">
        Todos (<?= $totalRegistros ?>)
      </a>

      <a href="<?= Url::to(['role/index', $formName => ['activo' => 1]]) ?>"
         class="btn-filtro <?= ($filtroActivo !== null && (string)$filtroActivo === '1') ? 'active' : '' ?>">
        Activos (<?= $activosCount ?>)
      </a>

      <a href="<?= Url::to(['role/index', $formName => ['activo' => 0]]) ?>"
         class="btn-filtro <?= ($filtroActivo !== null && (string)$filtroActivo === '0') ? 'active' : '' ?>">
        Inactivos (<?= $inactivosCount ?>)
      </a>

      <div class="ms-auto">
        <?= Html::a('<i class="fas fa-plus me-1"></i> Nuevo rol', ['create'], ['class' => 'btn-filtro']) ?>
      </div>
    </div>
  </div>

  <style>
  .filtros-diseño {
      background-color: #f8f9fa;
      padding: 15px;
      border-radius: 8px;
      border: 1px solid #e9ecef;
  }
  .btn-filtro {
      background-color: #ffffff;
      border: 1px solid #dee2e6;
      color: #6c757d;
      padding: 8px 16px;
      border-radius: 6px;
      text-decoration: none;
      font-size: 14px;
      font-weight: 500;
      transition: all 0.2s ease;
      white-space: nowrap;
      display: inline-flex;
      align-items: center;
      cursor: pointer;
  }
  .btn-filtro:hover {
      background-color: #e9ecef;
      border-color: #adb5bd;
      color: #495057;
      text-decoration: none;
  }
  .btn-filtro.active {
      background-color: #0d6efd;
      border-color: #0d6efd;
      color: #ffffff;
  }
  </style>

  <?= CustomGridView::widget([
      'dataProvider' => $dataProvider,
      'filterModel'  => $searchModel ?? null,
      'columns' => [
          ['class' => 'yii\grid\SerialColumn'],

          [
              'attribute' => 'id',
              'contentOptions' => ['style' => 'color:#6b7280;']
          ],
          'nombre',
          'descripcion',
          [
              'attribute' => 'es_admin',
              'label' => 'Admin',
              'format' => 'raw',
              'value' => function($model){
                  return $model->es_admin
                      ? '<span class="badge bg-success">Sí</span>'
                      : '<span class="badge bg-danger">No</span>';
              },
              'filter' => [1 => 'Sí', 0 => 'No'],
              'contentOptions' => ['style' => 'text-align:center']
          ],
          [
              'attribute' => 'activo',
              'format' => 'raw',
              'value' => function($model){
                  return $model->activo
                      ? '<span class="badge bg-success">Sí</span>'
                      : '<span class="badge bg-danger">No</span>';
              },
              'filter' => [1 => 'Sí', 0 => 'No'],
              'contentOptions' => ['style' => 'text-align:center']
          ],
          [
              'attribute' => 'updated_at',
              'label' => 'Actualizado',
          ],

          [
              'class' => 'yii\grid\ActionColumn',
              'template' => '{view} {update} {delete}',
              'buttons' => [
                  'view' => fn($url) => Html::a('Ver', $url, ['class' => 'btn btn-sm btn-light me-1']),
                  'update' => fn($url) => Html::a('Editar', $url, ['class' => 'btn btn-sm btn-primary me-1']),
                  'delete' => fn($url) => Html::a('Eliminar', $url, [
                      'class' => 'btn btn-sm btn-danger',
                      'data' => ['confirm' => '¿Eliminar este rol?', 'method' => 'post'],
                  ]),
              ],
              'header' => 'Acciones',
              'headerOptions' => ['style' => 'min-width:180px']
          ],
      ],
  ]); ?>

</div>