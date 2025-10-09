<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use app\widgets\CustomGridView;

/** @var yii\web\View $this */
/** @var app\models\CampanasSearch|null $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var app\models\Campanas $modeloNuevo */

$this->title = 'Marketing';
$this->params['breadcrumbs'][] = $this->title;

/**
 * Paleta de colores suaves para badges
 */
function generarColorUnico($texto)
{
    $textoLower = strtolower(trim((string)$texto));
    if ($textoLower === 'urgente') {
        return ['bg' => '#dc3545', 'text' => '#ffffff'];
    }
    $coloresSuaves = [
        ['bg' => '#e3f2fd', 'text' => '#1565c0'],
        ['bg' => '#e8f5e8', 'text' => '#2e7d32'],
        ['bg' => '#fff3e0', 'text' => '#ef6c00'],
        ['bg' => '#f3e5f5', 'text' => '#7b1fa2'],
        ['bg' => '#e0f2f1', 'text' => '#00695c'],
        ['bg' => '#fce4ec', 'text' => '#c2185b'],
        ['bg' => '#f5f5f5', 'text' => '#424242'],
        ['bg' => '#e1f5fe', 'text' => '#0277bd'],
        ['bg' => '#fff8e1', 'text' => '#f57f17'],
        ['bg' => '#f9fbe7', 'text' => '#689f38'],
        ['bg' => '#fef7ff', 'text' => '#8e24aa'],
        ['bg' => '#e8eaf6', 'text' => '#3f51b5']
    ];
    $hash = crc32($textoLower);
    $indice = abs($hash) % count($coloresSuaves);
    return $coloresSuaves[$indice];
}

/**
 * Colores para estado de análisis
 */
function generarColorAnalisis($analisis)
{
    $analisis = strtolower(trim((string)$analisis));
    switch ($analisis) {
        case 'analizar':    return ['bg' => '#4f46e5', 'text' => '#ffffff']; // morado/azul
        case 'pausa':
        case 'pausar':      return ['bg' => '#6366f1', 'text' => '#ffffff']; // indigo
        case 'detener':     return ['bg' => '#ef4444', 'text' => '#ffffff']; // rojo
        case 'continuar':   return ['bg' => '#22c55e', 'text' => '#ffffff']; // verde
        case 'experimento': return ['bg' => '#7c3aed', 'text' => '#ffffff']; // morado
        default:            return ['bg' => '#6b7280', 'text' => '#ffffff']; // gris
    }
}

/**
 * Resolver nombre de catálogo por ID con caché local
 */
function catalogName($id)
{
    static $cache = [];
    if (empty($id)) return 'Sin asignar';
    if (isset($cache[$id])) return $cache[$id];
    $name = \app\models\Catalogos::find()->select('nombre')->where(['id' => $id])->scalar();
    $cache[$id] = $name ?: 'Sin asignar';
    return $cache[$id];
}

// Capturar filtros actuales
$filtroAnalisis = Yii::$app->request->get('analisis', '');
$qNombre = Yii::$app->request->get('q', '');

// Builder de URL conservando query params (sin paginación)
function buildFilterUrl($newFilters = [])
{
    $currentParams = Yii::$app->request->queryParams;
    unset($currentParams['page'], $currentParams['_pjax']);
    $params = array_merge($currentParams, $newFilters);
    foreach ($params as $key => $value) {
        if ($value === null || $value === '' || $value === 'todos') unset($params[$key]);
    }
    return Url::current($params);
}

// Totales para KPI
$items   = $dataProvider->getModels();
$sumInv  = 0.0;
$sumPres = 0.0;
$sumRet  = 0.0;
$sumMsgs = 0;
foreach ($items as $c) {
    $sumInv  += (float)($c['inversion'] ?? 0);
    $sumPres += (float)($c['presupuesto'] ?? 0);
    $sumRet  += (float)($c['retorno'] ?? 0);
    $sumMsgs += (int)($c['mensajes'] ?? 0);
}

$fmtMoney = fn($n) => Yii::$app->formatter->asCurrency((float)$n, 'MXN');
$fmtInt   = fn($n) => number_format((float)$n, 0, '.', ',');

$this->registerCss(<<<CSS
/* Fondo morado marketing + cards */
.camps-bg {
  background-image:
    radial-gradient(70% 40% at 10% -10%, rgba(99,102,241,0.08) 0%, rgba(99,102,241,0) 60%),
    radial-gradient(70% 40% at 110% 0%, rgba(236,72,153,0.08) 0%, rgba(236,72,153,0) 60%),
    linear-gradient(#f1f5f9 1px, transparent 1px),
    linear-gradient(90deg, #f1f5f9 1px, transparent 1px);
  background-size: auto, auto, 24px 24px, 24px 24px;
  background-position: center, center, -1px -1px;
  background-color: #fff;
}
.card-soft {
  border-radius: 1rem;
  border: 1px solid #e5e7eb;
  background: #fff;
  box-shadow: 0 8px 20px rgba(2,6,23,.04);
}
.card-soft-accent {
  border-radius: 1rem;
  border: 1px solid rgba(124,58,237,.18);
  background: #fff;
  box-shadow: 0 10px 22px rgba(124,58,237,.09);
}

/* Tabs */
.navtab { display:inline-flex; align-items:center; padding:.5rem .75rem; border-radius:.75rem; border:1px solid #e5e7eb; color:#334155; text-decoration:none; transition:background-color .15s; }
.navtab:hover { background:#f8fafc; color:#1f2937; }
.navtab-active { background-image:linear-gradient(90deg,#4f46e5,#7c3aed); color:#fff !important; border-color:transparent; box-shadow:0 8px 24px rgba(79,70,229,.25); }

/* Hero */
.hero-card { background-image:linear-gradient(135deg,#4f46e5 0%, #d946ef 50%, #7c3aed 100%); color:#fff; border-radius:1rem; padding:16px 18px; }
.hero-card .title { font-size:1.125rem; font-weight:700; }
.hero-card .subtitle { font-size:.875rem; opacity:.95; }

/* Chips (filtros de análisis) – morado suave */
.filtros-diseño { background:#f5f3ff; padding:10px 12px; border-radius:10px; border:1px solid #e9d5ff; }
.btn-filtro { background:#ffffff; border:1px solid #ddd6fe; color:#4338ca; padding:6px 12px; border-radius:9999px; text-decoration:none; font-size:12px; font-weight:600; transition:all .2s ease; white-space:nowrap; display:inline-flex; align-items:center; }
.btn-filtro:hover { background:#eef2ff; border-color:#c7d2fe; color:#3730a3; }
.btn-filtro.active { background-image:linear-gradient(90deg,#4f46e5,#7c3aed); border-color:transparent; color:#fff; box-shadow:0 6px 18px rgba(79,70,229,.25); }

/* Grid */
.table-wrap { overflow-x:auto; }
.table-wrap table { width:100%!important; border-collapse:separate; border-spacing:0; }
.grid-view thead th { position:sticky; top:0; background:#fff; z-index:2; }
.grid-view .table>:not(caption)>*>* { background-color:transparent; }
.grid-view .table th { font-size:.8rem; letter-spacing:.02em; text-transform:uppercase; color:#64748b; }
.grid-view .table td { color:#0f172a; vertical-align:middle; }

/* Inline editing */
.editable-field.editing { position:relative; z-index:3; }
.edit-container { background:#fff; border:1px solid #e5e7eb; border-radius:.5rem; padding:.5rem; box-shadow:0 8px 20px rgba(2,6,23,.08); }
.save-cancel-buttons .btn { min-width:90px; }

/* KPI small labels */
.kpi .label { font-size:.8rem; color:#64748b; }
.kpi .value { font-size:1.35rem; font-weight:800; color:#0f172a; }
CSS);

// Exponer URLs JS globales
$this->registerJs(
    'window.campanasUpdateFieldUrl = ' . json_encode(Url::to(['campanas/update-field'])) . ';
     window.campanasGetSelectOptionsUrl = ' . json_encode(Url::to(['campanas/get-select-options'])) . ';',
    \yii\web\View::POS_HEAD
);
?>

<div class="camps-bg min-vh-100">
  <div class="container py-4">

    <?php if (Yii::$app->session->hasFlash('success')): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert" aria-live="polite">
        <i class="fas fa-check-circle me-2"></i><?= Yii::$app->session->getFlash('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
      </div>
    <?php endif; ?>

    <!-- Hero + Tabs -->
    <div class="d-flex flex-column gap-3 mb-3">
      <div class="hero-card d-flex align-items-center justify-content-between">
        <div>
          <div class="title"><?= Html::encode($this->title) ?></div>
          <div class="subtitle">Panel para administrar y comparar campañas de marketing</div>
        </div>
        <div class="d-none d-md-flex gap-2">
          <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#modalNuevaCampana">
            <i class="fas fa-plus me-1"></i> Nueva campaña
          </button>
          <a class="btn btn-outline-light btn-sm" href="<?= Url::to(['campanas/export-excel']) ?>">
            <i class="fas fa-file-excel me-1"></i> Exportar Excel
          </a>
        </div>
      </div>

      <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex gap-2">
          <a class="navtab navtab-active" href="<?= Url::to(['campanas/index']) ?>">Campañas</a>
          <a class="navtab" href="<?= Url::to(['retorno/index']) ?>">Retorno</a>
          <a class="navtab" href="<?= Url::to(['ventas-mensuales/index']) ?>">Comparativas</a>
          <a class="navtab" href="<?= Url::to(['productos-vendidos/index']) ?>">Productos</a>
        </div>
        <div class="d-flex gap-2 d-md-none">
          <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalNuevaCampana">+ Crear</button>
          <a class="btn btn-outline-secondary btn-sm" href="<?= Url::to(['campanas/export-excel']) ?>">Excel</a>
        </div>
      </div>
    </div>

    <!-- KPI cards -->
    <div class="row g-3 mb-3 kpi">
      <div class="col-12 col-sm-6 col-lg-3">
        <div class="card-soft-accent p-3">
          <div class="label">Inversión</div>
          <div class="value"><?= $fmtMoney($sumInv) ?></div>
        </div>
      </div>
      <div class="col-12 col-sm-6 col-lg-3">
        <div class="card-soft-accent p-3">
          <div class="label">Presupuesto</div>
          <div class="value"><?= $fmtMoney($sumPres) ?></div>
        </div>
      </div>
      <div class="col-12 col-sm-6 col-lg-3">
        <div class="card-soft-accent p-3">
          <div class="label">Retorno</div>
          <div class="value"><?= $fmtMoney($sumRet) ?></div>
        </div>
      </div>
      <div class="col-12 col-sm-6 col-lg-3">
        <div class="card-soft-accent p-3">
          <div class="label">Mensajes</div>
          <div class="value"><?= $fmtInt($sumMsgs) ?></div>
        </div>
      </div>
    </div>

    <!-- Toolbar: chips moradas + búsqueda -->
    <div class="card-soft p-3 mb-3">
      <div class="d-flex flex-wrap align-items-center gap-2">
        <div class="filtros-diseño flex-grow-1">
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <?php
              $chips = ['' => 'Todos', 'Analizar' => 'Analizar', 'Pausar' => 'Pausar', 'Detener' => 'Detener', 'Continuar' => 'Continuar', 'Experimento' => 'Experimento'];
              foreach ($chips as $val => $label):
                $active = ($filtroAnalisis === $val || ($val==='' && $filtroAnalisis==='')) ? 'active' : '';
                $url = buildFilterUrl(['analisis' => $val]);
            ?>
              <a href="<?= Html::encode($url) ?>" class="btn-filtro <?= $active ?>"><?= Html::encode($label) ?></a>
            <?php endforeach; ?>
          </div>
        </div>
        <form class="d-flex align-items-center gap-2" method="get" action="<?= Url::to(['campanas/index']) ?>">
          <input type="hidden" name="analisis" value="<?= Html::encode($filtroAnalisis) ?>">
          <input type="search" name="q" value="<?= Html::encode($qNombre) ?>" class="form-control form-control-sm" placeholder="Buscar por nombre..." style="max-width:220px;">
          <button class="btn btn-light btn-sm" type="submit">Buscar</button>
        </form>
      </div>
      <?php if ($qNombre): ?>
        <div class="mt-2">
          <span class="badge bg-light text-dark">Filtro: “<?= Html::encode($qNombre) ?>”</span>
          <a class="btn btn-link btn-sm" href="<?= Html::encode(buildFilterUrl(['q'=>null])) ?>">Limpiar</a>
        </div>
      <?php endif; ?>
    </div>

    <!-- Grid (con PJAX) -->
    <div class="card-soft p-2">
      <div class="table-wrap">
        <?php Pjax::begin(['id' => 'campanas-grid-pjax', 'timeout' => 0, 'enablePushState' => true]); ?>
        <?= CustomGridView::widget([
          'dataProvider' => $dataProvider,
          'filterModel'  => $searchModel ?? null,
          'summary'      => false,
          'tableOptions' => ['class' => 'table table-striped table-hover align-middle mb-0'],
          'columns'      => [
            ['class' => 'yii\grid\SerialColumn'],

            // Nombre (editable text)
            [
              'attribute' => 'nombre',
              'format'    => 'raw',
              'value'     => function ($model) {
                  $display = Html::encode((string)$model->nombre);
                  $current = Html::encode((string)$model->nombre);
                  return "<div class='editable-field' data-field-type='text' data-field-name='nombre' data-record-id='{$model->id}' data-current-value=\"{$current}\" title='Click para editar'>{$display}</div>";
              }
            ],

            // Tipo campaña (select)
            [
              'attribute' => 'campaña_id',
              'label'     => 'Campaña',
              'format'    => 'raw',
              'value'     => function ($model) {
                  $nombre  = catalogName($model->campaña_id ?? null);
                  $colores = generarColorUnico($nombre);
                  $badge   = Html::tag('span', Html::encode($nombre), [
                      'class' => 'badge',
                      'style' => "background-color:{$colores['bg']};color:{$colores['text']};font-weight:600;"
                  ]);
                  $current = $model->campaña_id ?? '';
                  return "<div class='editable-field' data-field-type='select' data-field-name='campaña_id' data-record-id='{$model->id}' data-current-value='{$current}' title='Click para editar'>{$badge}</div>";
              }
            ],

            // Asesor (select)
            [
              'attribute' => 'asesor_id',
              'label'     => 'Asesor',
              'format'    => 'raw',
              'value'     => function ($model) {
                  $nombre  = catalogName($model->asesor_id ?? null);
                  $colores = generarColorUnico($nombre);
                  $badge   = Html::tag('span', Html::encode($nombre), [
                      'class' => 'badge',
                      'style' => "background-color:{$colores['bg']};color:{$colores['text']};font-weight:600;"
                  ]);
                  $current = $model->asesor_id ?? '';
                  return "<div class='editable-field' data-field-type='select' data-field-name='asesor_id' data-record-id='{$model->id}' data-current-value='{$current}' title='Click para editar'>{$badge}</div>";
              }
            ],

            // Inversión (number)
            [
              'attribute' => 'inversion',
              'label'     => 'Inversión',
              'format'    => 'raw',
              'contentOptions' => ['style' => 'min-width:120px'],
              'value'     => function ($model) {
                  $valor   = (float)($model->inversion ?? 0);
                  $mostrar = '$' . number_format($valor, 2);
                  return "<div class='editable-field' data-field-type='number' data-field-name='inversion' data-record-id='{$model->id}' data-current-value='{$valor}' title='Click para editar'>{$mostrar}</div>";
              }
            ],

            // Mensajes (number)
            [
              'attribute' => 'mensajes',
              'format'    => 'raw',
              'contentOptions' => ['style' => 'min-width:90px'],
              'value'     => function ($model) {
                  $valor = (int)($model->mensajes ?? 0);
                  return "<div class='editable-field' data-field-type='number' data-field-name='mensajes' data-record-id='{$model->id}' data-current-value='{$valor}' title='Click para editar'>{$valor}</div>";
              }
            ],

            // Retorno (number)
            [
              'attribute' => 'retorno',
              'format'    => 'raw',
              'contentOptions' => ['style' => 'min-width:120px'],
              'value'     => function ($model) {
                  $valor   = (float)($model->retorno ?? 0);
                  $mostrar = '$' . number_format($valor, 2);
                  return "<div class='editable-field' data-field-type='number' data-field-name='retorno' data-record-id='{$model->id}' data-current-value='{$valor}' title='Click para editar'>{$mostrar}</div>";
              }
            ],

            // Análisis (select con badge)
            [
              'attribute' => 'analisis',
              'label'     => 'Análisis',
              'format'    => 'raw',
              'contentOptions' => ['style' => 'min-width:140px'],
              'value'     => function ($model) {
                  $estado  = $model->analisis ?: 'Analizar';
                  $colores = generarColorAnalisis($estado);
                  $texto   = ucfirst(strtolower((string)$estado));
                  $badge   = Html::tag('span', Html::encode($texto), [
                      'class' => 'badge',
                      'style' => "background-color:{$colores['bg']};color:{$colores['text']};font-weight:600;"
                  ]);
                  $current = Html::encode((string)$model->analisis);
                  return "<div class='editable-field' data-field-type='select' data-field-name='analisis' data-record-id='{$model->id}' data-current-value='{$current}' title='Click para editar'>{$badge}</div>";
              }
            ],

            // Mensaje predeterminado (texto + copiar)
            [
              'attribute' => 'mensaje_predeterminado',
              'label'     => 'Mensaje',
              'format'    => 'raw',
              'value'     => function ($model) {
                  $texto = trim((string)$model->mensaje_predeterminado);
                  $corto = Html::encode(mb_strimwidth($texto, 0, 60, '…', 'UTF-8'));
                  $full  = Html::encode($texto);
                  $copy  = '<button type="button" class="btn btn-light btn-sm ms-2 btn-copy-msg" title="Copiar" aria-label="Copiar mensaje" data-msg="'.$full.'"><i class="far fa-copy"></i></button>';
                  return "<div class='d-flex align-items-center'><div class='editable-field flex-grow-1' data-field-type='text' data-field-name='mensaje_predeterminado' data-record-id='{$model->id}' data-current-value='{$full}' title='{$full}'>{$corto}</div>{$copy}</div>";
              }
            ],
          ],
        ]); ?>
        <?php Pjax::end(); ?>
      </div>
    </div>

  </div>
</div>

<!-- Modal Nueva Campaña -->
<div class="modal fade" id="modalNuevaCampana" tabindex="-1" aria-labelledby="modalNuevaCampanaLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header" style="background:#4f46e5; color:#fff;">
        <h4 class="modal-title" id="modalNuevaCampanaLabel"><i class="fas fa-bullhorn me-2"></i> Nueva Campaña</h4>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <?php if (isset($modeloNuevo)): ?>
          <?= $this->render('_form', ['model' => $modeloNuevo]) ?>
        <?php else: ?>
          <div class="alert alert-warning mb-0">No se pasó $modeloNuevo desde el controlador. Para usar el modal, envía un modelo nuevo a la vista.</div>
        <?php endif; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light border me-2" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Cancelar</button>
        <button type="submit" class="btn btn-primary btn-lg" id="btnGuardarCampana" form="campanasForm"><i class="fas fa-save me-1"></i> Guardar Campaña</button>
      </div>
    </div>
  </div>
</div>

<?php
// JS: modal UX
$this->registerJs(<<<'JS'
(function(){
  const modal = $('#modalNuevaCampana');
  modal.on('hidden.bs.modal', function(){ const form = modal.find('form')[0]; if (form) form.reset(); });
  modal.on('shown.bs.modal',  function(){ const first = modal.find('input, select, textarea').filter(':visible:enabled').first(); if (first.length) first.focus(); });
})();
JS);

// JS: copiar mensaje + edición inline con delegación (funciona tras PJAX)
$this->registerJs(<<<'JS'
(function(){
  let editingCell = null;

  // Copiar mensaje
  $(document).on('click', '.btn-copy-msg', function(e){
    e.preventDefault();
    const msg = $(this).data('msg') || '';
    navigator.clipboard.writeText(msg).then(function(){
      toast('Mensaje copiado','success');
    }).catch(function(){ toast('No se pudo copiar','error'); });
  });

  // Editar con click o doble click
  $(document)
    .off('click.editable dblclick.editable')
    .on('click.editable dblclick.editable', '.editable-field', function(e){
      e.preventDefault(); e.stopPropagation();
      if (editingCell !== null) return;

      const cell = $(this);
      const fieldType = cell.data('field-type');
      const fieldName = cell.data('field-name');
      const recordId  = cell.data('record-id');
      const currentValue = cell.data('current-value') ?? '';
      const originalContent = cell.html();

      editingCell = cell;
      cell.addClass('editing');

      function mountEditor(innerHtml){
        const html = `
          <div class="edit-container" role="dialog" aria-label="Editar">
            ${innerHtml}
            <div class="save-cancel-buttons mt-2 d-flex gap-2">
              <button type="button" class="btn btn-success btn-sm save-btn" data-record-id="${recordId}" data-field-name="${fieldName}">
                <i class="fas fa-save me-1"></i>Guardar
              </button>
              <button type="button" class="btn btn-secondary btn-sm cancel-btn">
                <i class="fas fa-times me-1"></i>Cancelar
              </button>
            </div>
          </div>`;
        cell.html(html);
        const focusEl = cell.find('.inline-input, .inline-select');
        if (focusEl.length) setTimeout(()=>{ focusEl.focus(); if (focusEl.is('input[type="text"]')) focusEl.select(); }, 60);
        bindEditEvents(cell, recordId, fieldName, originalContent);
      }

      if (fieldType === 'select') {
        $.get(window.campanasGetSelectOptionsUrl, { field: fieldName }, function(res){
          const options = (res && res.options) ? res.options : [];
          let inner = '<select class="inline-select form-select form-select-sm"><option value="">-- Seleccionar --</option>';
          options.forEach(opt=>{
            const sel = (String(opt.value) === String(currentValue)) ? 'selected' : '';
            inner += `<option value="${opt.value}" ${sel}>${opt.text}</option>`;
          });
          inner += '</select>';
          mountEditor(inner);
        }, 'json').fail(function(){
          toast('Error cargando opciones','error');
          cell.removeClass('editing'); editingCell = null;
        });
        return;
      }

      const inputType = (fieldType === 'number') ? 'number' : 'text';
      const stepAttr  = (fieldType === 'number') ? ' step="0.01"' : '';
      const safeValue = String(currentValue).replace(/"/g, '&quot;');
      mountEditor(`<input type="${inputType}" class="inline-input form-control form-control-sm" value="${safeValue}"${stepAttr}>`);
    });

  function bindEditEvents(cell, recordId, fieldName, originalContent){
    // Guardar
    cell.off('click.save').on('click.save', '.save-btn', function(e){
      e.preventDefault(); e.stopPropagation();
      const saveBtn = $(this);

      let newValue;
      const selectEl = cell.find('.inline-select');
      const inputEl  = cell.find('.inline-input');

      if (selectEl.length) {
        newValue = selectEl.val();
      } else if (inputEl.length) {
        newValue = inputEl.val();
        if (['inversion','retorno','mensajes'].includes(fieldName)) {
          newValue = (fieldName === 'mensajes') ? parseInt(newValue||0,10) : parseFloat(newValue||0);
          if (isNaN(newValue)) newValue = 0;
        }
      } else {
        newValue = cell.data('current-value') ?? '';
      }

      $.ajax({
        url: window.campanasUpdateFieldUrl,
        type: 'POST',
        dataType: 'json',
        data: { id: recordId, field: fieldName, value: newValue, _csrf: yii.getCsrfToken() },
        beforeSend: function(){ saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Guardando...'); }
      }).done(function(resp){
        if (resp && resp.success) {
          // Preferir HTML del servidor
          if (resp.newContent) {
            cell.html(resp.newContent);
          } else {
            // Fallback local por tipo
            if (['inversion','retorno'].includes(fieldName)) {
              const num = parseFloat(newValue)||0;
              cell.html('$' + num.toFixed(2));
            } else if (fieldName === 'analisis') {
              const t = String(newValue||'');
              const txt = t.charAt(0).toUpperCase() + t.slice(1).toLowerCase();
              cell.html('<span class="badge bg-secondary" style="font-weight:600;">'+txt+'</span>');
            } else if (['asesor_id','campaña_id'].includes(fieldName)) {
              $.get(window.campanasGetSelectOptionsUrl, { field: fieldName }, function(r2){
                const opts = (r2 && r2.options) ? r2.options : [];
                const found = opts.find(o => String(o.value) === String(newValue));
                const nombre = found ? found.text : 'Sin asignar';
                cell.html('<span class="badge bg-light text-dark" style="font-weight:600;">'+nombre+'</span>');
              }, 'json');
            } else {
              cell.html(String(newValue ?? ''));
            }
          }
          cell.data('current-value', Array.isArray(newValue) ? newValue.join(',') : newValue);
          toast(resp.message || 'Guardado correctamente','success');
        } else {
          cell.html(originalContent);
          toast((resp && resp.message) ? resp.message : 'No se pudo guardar','error');
        }
      }).fail(function(){
        cell.html(originalContent);
        toast('Error de conexión','error');
      }).always(function(){
        cell.removeClass('editing'); editingCell = null;
      });
    });

    // Cancelar
    cell.off('click.cancel').on('click.cancel', '.cancel-btn', function(e){
      e.preventDefault(); e.stopPropagation();
      cell.html(originalContent); cell.removeClass('editing'); editingCell = null;
    });

    // Teclas rápidas
    cell.off('keydown.edit').on('keydown.edit', '.inline-input', function(e){
      if (e.keyCode === 13) { e.preventDefault(); cell.find('.save-btn').trigger('click'); }
      else if (e.keyCode === 27) { e.preventDefault(); cell.find('.cancel-btn').trigger('click'); }
    });
  }

  // Cerrar si clic fuera
  $(document).on('click', function(e){
    if (editingCell && !$(e.target).closest('.edit-container').length) {
      const cancelBtn = editingCell.find('.cancel-btn');
      if (cancelBtn.length) cancelBtn.trigger('click');
    }
  });

  // Toast pequeño
  function toast(message, type){
    const cls = (type==='success') ? 'alert-success' : 'alert-danger';
    const el = $(`<div class="alert ${cls} alert-dismissible fade show" role="alert" style="position:fixed;top:20px;right:20px;z-index:1060;min-width:280px;">${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`);
    $('body').append(el); setTimeout(()=>{ el.alert('close'); }, 2500);
  }
})();
JS);
?>