<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\widgets\CustomGridView;

/** @var yii\web\View $this */
/** @var app\models\CampanasSearch|null $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var app\models\Campanas $modeloNuevo Optional: pásalo desde el controlador para el modal */

$this->title = 'Marketing';
$this->params['breadcrumbs'][] = $this->title;

/**
 * Colores suaves para badges consistentes con Diseño
 */
function generarColorUnico($texto)
{
    $textoLower = strtolower(trim((string) $texto));
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
 * Colores específicos para el estado de análisis de campañas
 */
function generarColorAnalisis($analisis)
{
    $analisis = strtolower(trim((string) $analisis));
    switch ($analisis) {
        case 'analizar':
            return ['bg' => '#0d6efd', 'text' => '#ffffff']; // azul
        case 'pausa':      // compat
        case 'pausar':
            return ['bg' => '#ffc107', 'text' => '#000000']; // amarillo
        case 'detener':
            return ['bg' => '#dc3545', 'text' => '#ffffff']; // rojo
        case 'continuar':
            return ['bg' => '#28a745', 'text' => '#ffffff']; // verde
        case 'experimento':
            return ['bg' => '#6f42c1', 'text' => '#ffffff']; // morado
        default:
            return ['bg' => '#6c757d', 'text' => '#ffffff']; // gris
    }
}

/**
 * Resolver nombre de catálogo por ID con caché local
 */
function catalogName($id)
{
    static $cache = [];
    if (empty($id))
        return 'Sin asignar';
    if (isset($cache[$id]))
        return $cache[$id];
    $name = \app\models\Catalogos::find()->select('nombre')->where(['id' => $id])->scalar();
    $cache[$id] = $name ?: 'Sin asignar';
    return $cache[$id];
}

// Filtro por análisis desde query param para activar botones
$filtroAnalisis = Yii::$app->request->get('analisis');

// Helper para construir URLs conservando otros params (sin paginación)
function buildFilterUrl($newFilters = [])
{
    $currentParams = Yii::$app->request->queryParams;
    unset($currentParams['page']);
    $params = array_merge($currentParams, $newFilters);
    foreach ($params as $key => $value) {
        if ($value === null || $value === '' || $value === 'todos') {
            unset($params[$key]);
        }
    }
    return Url::current($params);
}

// Totales para resumen
$items = $dataProvider->getModels();
$sumInv = 0;
$sumPres = 0;
$sumRet = 0;
$sumMsgs = 0;
foreach ($items as $c) {
    $sumInv += (float) ($c['inversion'] ?? 0);
    $sumPres += (float) ($c['presupuesto'] ?? 0);
    $sumRet += (float) ($c['retorno'] ?? 0);
    $sumMsgs += (int) ($c['mensajes'] ?? 0);
}

$fmtMoney = fn($n) => Yii::$app->formatter->asCurrency((float) $n, 'MXN');
$fmtInt = fn($n) => number_format((float) $n, 0, '.', ',');
?>

<style>
    /* Fondo y tarjetas */
    .camps-bg {
        background-image:
            radial-gradient(80% 50% at 20% 0%, rgba(99, 102, 241, 0.07) 0%, rgba(99, 102, 241, 0.0) 60%),
            radial-gradient(60% 50% at 110% 20%, rgba(236, 72, 153, 0.07) 0%, rgba(236, 72, 153, 0.0) 60%),
            linear-gradient(#f1f5f9 1px, transparent 1px),
            linear-gradient(90deg, #f1f5f9 1px, transparent 1px);
        background-size: auto, auto, 24px 24px, 24px 24px;
        background-position: center, center, -1px -1px, -1px -1px;
        background-color: #fff;
    }

    .card-soft {
        border-radius: 1rem;
        border: 1px solid #e5e7eb;
        background: #fff;
        box-shadow: 0 8px 20px rgba(2, 6, 23, .04);
    }

    .card-soft-yellow {
        border-radius: 1rem;
        border: 1px solid #f6e6a2;
        background: #fffbea;
        /* amarillo suave */
        box-shadow: 0 8px 20px rgba(139, 92, 0, .08);
    }

    /* Tabs navegación */
    .navtab {
        display: inline-flex;
        align-items: center;
        padding: .5rem .75rem;
        border-radius: .75rem;
        border: 1px solid #e5e7eb;
        color: #334155;
        transition: background-color .15s;
        text-decoration: none;
    }

    .navtab:hover {
        background: #f8fafc;
        color: #1f2937;
    }

    .navtab-active {
        background-image: linear-gradient(90deg, #4f46e5, #7c3aed);
        color: #fff !important;
        border-color: transparent;
        box-shadow: 0 8px 24px rgba(79, 70, 229, .25);
    }

    /* Barra de filtros/chips (amarilla) */
    .filtros-diseño {
        background-color: #fff7cc;
        padding: 10px 12px;
        border-radius: 10px;
        border: 1px solid #f6e6a2;
    }

    .btn-filtro {
        background-color: #ffffff;
        border: 1px solid #f0e3a2;
        color: #6b5e00;
        padding: 6px 12px;
        border-radius: 9999px;
        text-decoration: none;
        font-size: 12px;
        font-weight: 600;
        transition: all 0.2s ease;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        cursor: pointer;
    }

    .btn-filtro:hover {
        background-color: #fff3b0;
        border-color: #e9d573;
        color: #4b4700;
    }

    .btn-filtro.active {
        background-image: linear-gradient(90deg, #f59e0b, #f97316);
        border-color: transparent;
        color: #ffffff;
        box-shadow: 0 6px 18px rgba(245, 158, 11, .35);
    }

    @media (max-width: 768px) {
        .filtros-diseño .d-flex {
            flex-wrap: wrap;
            gap: 6px;
        }

        .btn-filtro {
            margin-bottom: 6px;
            font-size: 11px;
            padding: 5px 10px;
        }
    }

    /* Tabla: evitar encimado y mejorar scroll */
    .table-wrap {
        overflow-x: auto;
    }

    .table-wrap .grid-view {
        margin-bottom: 0;
    }

    .table-wrap table {
        width: 100% !important;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table-wrap .table> :not(caption)>*>* {
        background-color: transparent;
    }

    /* Encabezado bonito */
    .hero-card {
        background-image: linear-gradient(135deg, #4f46e5 0%, #d946ef 50%, #7c3aed 100%);
        color: #fff;
        border-radius: 1rem;
        padding: 16px 18px;
    }

    .hero-card .title {
        font-size: 1.125rem;
        font-weight: 700;
    }

    .hero-card .subtitle {
        font-size: .875rem;
        opacity: .95;
    }

    /* Estilo cuando una celda está en edición */
    .editable-field.editing {
        position: relative;
        z-index: 2;
    }

    /* Modal ancho y estilo (igual que Ventas) */
    .modal-xl {
        max-width: 95%;
        width: 1400px;
    }

    .modal-content {
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    }

    .modal-header {
        border-radius: 15px 15px 0 0;
    }
</style>

<div class="camps-bg min-vh-100">
    <div class="container py-4">

        <!-- Flash -->
        <?php if (Yii::$app->session->hasFlash('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?= Yii::$app->session->getFlash('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Encabezado + Tabs -->
        <div class="d-flex flex-column gap-3 mb-3">
            <div class="hero-card d-flex align-items-center justify-content-between">
                <div>
                    <div class="title"><?= Html::encode($this->title) ?></div>
                    <div class="subtitle">Panel para administrar y comparar campañas de marketing</div>
                </div>
                <div class="d-none d-md-flex gap-2">
                    <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal"
                        data-bs-target="#modalNuevaCampana">
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
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                        data-bs-target="#modalNuevaCampana">+ Crear</button>
                    <a class="btn btn-outline-secondary btn-sm"
                        href="<?= Url::to(['campanas/export-excel']) ?>">Excel</a>
                </div>
            </div>
        </div>

        <!-- Barra superior con filtros estilo chips (amarilla) -->
        <div class="filtros-diseño mb-3 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="<?= Url::to(['campanas/index']) ?>"
                    class="btn-filtro <?= !$filtroAnalisis ? 'active' : '' ?>">Todos</a>
            </div>

        </div>

        <!-- Tabla en tarjeta amarilla, con contenedor responsive para evitar encimado -->
        <div class="card-soft-yellow p-2">
            <div class="table-wrap">
                <?= CustomGridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel ?? null,
                    'summary' => false,
                    'tableOptions' => ['class' => 'table table-striped table-hover align-middle mb-0'],
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],

                        // Campaña (editable text)
                        [
                            'attribute' => 'nombre',
                            'format' => 'raw',
                            'value' => function ($model) {
                                                $display = Html::encode((string) $model->nombre);
                                                $current = Html::encode((string) $model->nombre);
                                                return "<div class='editable-field' data-field-type='text' data-field-name='nombre' data-record-id='{$model->id}' data-current-value='{$current}' title='Click para editar'>{$display}</div>";
                                            }
                        ],

                        // Tipo de campaña (catálogo) editable select
                        [
                            'attribute' => 'campaña_id',
                            'label' => 'Campaña',
                            'format' => 'raw',
                            'value' => function ($model) {
                                                $nombre = catalogName($model->campaña_id ?? null);
                                                $colores = generarColorUnico($nombre);
                                                $badge = Html::tag('span', Html::encode($nombre), [
                                                    'class' => 'badge',
                                                    'style' => "background-color:{$colores['bg']};color:{$colores['text']};font-weight:600;"
                                                ]);
                                                $current = $model->campaña_id ?? '';
                                                return "<div class='editable-field' data-field-type='select' data-field-name='campaña_id' data-record-id='{$model->id}' data-current-value='{$current}' title='Click para editar'>{$badge}</div>";
                                            }
                        ],

                        // Asesor (catálogo) editable select
                        [
                            'attribute' => 'asesor_id',
                            'label' => 'Asesor',
                            'format' => 'raw',
                            'value' => function ($model) {
                                                $nombre = catalogName($model->asesor_id ?? null);
                                                $colores = generarColorUnico($nombre);
                                                $badge = Html::tag('span', Html::encode($nombre), [
                                                    'class' => 'badge',
                                                    'style' => "background-color:{$colores['bg']};color:{$colores['text']};font-weight:600;"
                                                ]);
                                                $current = $model->asesor_id ?? '';
                                                return "<div class='editable-field' data-field-type='select' data-field-name='asesor_id' data-record-id='{$model->id}' data-current-value='{$current}' title='Click para editar'>{$badge}</div>";
                                            }
                        ],

                        // Inversión (número)
                        [
                            'attribute' => 'inversion',
                            'label' => 'Inversión',
                            'format' => 'raw',
                            'value' => function ($model) {
                                                $valor = (float) ($model->inversion ?? 0);
                                                $mostrar = '$' . number_format($valor, 2);
                                                return "<div class='editable-field' data-field-type='number' data-field-name='inversion' data-record-id='{$model->id}' data-current-value='{$valor}' title='Click para editar'>{$mostrar}</div>";
                                            }
                        ],

                        // Mensajes (número)
                        [
                            'attribute' => 'mensajes',
                            'format' => 'raw',
                            'value' => function ($model) {
                                                $valor = (int) ($model->mensajes ?? 0);
                                                return "<div class='editable-field' data-field-type='number' data-field-name='mensajes' data-record-id='{$model->id}' data-current-value='{$valor}' title='Click para editar'>{$valor}</div>";
                                            }
                        ],

                        // Retorno (número)
                        [
                            'attribute' => 'retorno',
                            'format' => 'raw',
                            'value' => function ($model) {
                                                $valor = (float) ($model->retorno ?? 0);
                                                $mostrar = '$' . number_format($valor, 2);
                                                return "<div class='editable-field' data-field-type='number' data-field-name='retorno' data-record-id='{$model->id}' data-current-value='{$valor}' title='Click para editar'>{$mostrar}</div>";
                                            }
                        ],

                        // Análisis (estado) editable select con badge
                        [
                            'attribute' => 'analisis',
                            'label' => 'Análisis',
                            'format' => 'raw',
                            'value' => function ($model) {
                                                $estado = $model->analisis ?: 'Analizar';
                                                $colores = generarColorAnalisis($estado);
                                                $texto = ucfirst(strtolower($estado));
                                                $badge = Html::tag('span', Html::encode($texto), [
                                                    'class' => 'badge',
                                                    'style' => "background-color:{$colores['bg']};color:{$colores['text']};font-weight:600;"
                                                ]);
                                                return "<div class='editable-field' data-field-type='select' data-field-name='analisis' data-record-id='{$model->id}' data-current-value='" . Html::encode((string) $model->analisis) . "' title='Click para editar'>{$badge}</div>";
                                            }
                        ],

                        // Mensaje predeterminado (texto)
                        [
                            'attribute' => 'mensaje_predeterminado',
                            'label' => 'Mensaje',
                            'format' => 'raw',
                            'value' => function ($model) {
                                                $texto = trim((string) $model->mensaje_predeterminado);
                                                $corto = mb_strimwidth($texto, 0, 60, '…', 'UTF-8');
                                                return "<div class='editable-field' data-field-type='text' data-field-name='mensaje_predeterminado' data-record-id='{$model->id}' data-current-value='" . Html::encode($texto) . "' title='" . Html::encode($texto) . "'>{$corto}</div>";
                                            }
                        ],
                    ],
                ]); ?>
            </div>
        </div>

    </div>
</div>

<!-- Modal para Nueva Campaña (misma experiencia que Ventas) -->
<div class="modal fade" id="modalNuevaCampana" tabindex="-1" aria-labelledby="modalNuevaCampanaLabel" aria-hidden="true"
    data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background:#4f46e5; color:#fff;">
                <h4 class="modal-title" id="modalNuevaCampanaLabel">
                    <i class="fas fa-bullhorn me-2"></i> Nueva Campaña
                </h4>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php if (isset($modeloNuevo)): ?>
                    <?= $this->render('_form', ['model' => $modeloNuevo]) ?>
                <?php else: ?>
                    <div class="alert alert-warning mb-0">
                        No se pasó $modeloNuevo desde el controlador. Para usar el modal, envía un modelo nuevo a la vista.
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border me-2" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancelar
                </button>
                <button type="submit" class="btn btn-primary btn-lg" id="btnGuardarCampana" form="campanasForm">
                    <i class="fas fa-save me-1"></i> Guardar Campaña
                </button>
            </div>
        </div>
    </div>
</div>

<?php
// Exponer URLs como variables JS globales
$this->registerJs(
    'window.campanasUpdateFieldUrl = ' . json_encode(Url::to(['campanas/update-field'])) . ';
     window.campanasGetSelectOptionsUrl = ' . json_encode(Url::to(['campanas/get-select-options'])) . ';',
    \yii\web\View::POS_HEAD
);

// JS para modal (replica comportamiento de Ventas: reset al cerrar, spinner al guardar)
$this->registerJs(<<<'JS'
(function(){
  const modal = $('#modalNuevaCampana');

  modal.on('hidden.bs.modal', function () {
    const form = modal.find('form')[0];
    if (form) form.reset();
  });

  modal.on('shown.bs.modal', function () {
    // Enfocar primer input del form
    const first = modal.find('input, select, textarea').filter(':visible:enabled').first();
    if (first.length) first.focus();
  });

  modal.on('click', '#btnGuardarCampana', function(e){
    // Si el form en _form tiene id "campanasForm", el atributo form del botón ya disparará submit.
    // De todas formas añadimos spinner y protección.
    const btn = $(this);
    e.preventDefault();
    const form = modal.find('form')[0];
    if (!form) return;

    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Guardando...');
    // Enviar el formulario normal (no AJAX) para respetar validaciones del controlador
    form.submit();
  });
})();
JS);

// Script de edición inline (igual que antes)
$this->registerJs(<<<'JS'
$(document).ready(function() {
    let editingCell = null;

    // Click para iniciar edición
    $(document).off('click.editable').on('click.editable', '.editable-field', function(e) {
        e.preventDefault();
        e.stopPropagation();

        if (editingCell !== null) return;

        const cell = $(this);
        const fieldType = cell.data('field-type');
        const fieldName = cell.data('field-name');
        const recordId = cell.data('record-id');
        const currentValue = cell.data('current-value') ?? '';
        const originalContent = cell.html();

        editingCell = cell;
        cell.addClass('editing');

        function createEditContainer(inner) {
            const container = `
                <div class="edit-container" style="min-width: 220px;">
                    ${inner}
                    <div class="save-cancel-buttons mt-2 d-flex gap-2">
                        <button type="button" class="btn btn-success btn-sm save-btn" data-record-id="${recordId}" data-field-name="${fieldName}">
                            <i class="fas fa-save me-1"></i>Guardar
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm cancel-btn">
                            <i class="fas fa-times me-1"></i>Cancelar
                        </button>
                    </div>
                </div>
            `;
            cell.html(container);

            // focus
            const focusEl = cell.find('.inline-input, .inline-select');
            if (focusEl.length) {
                setTimeout(() => {
                    focusEl.focus();
                    if (focusEl.is('input[type="text"]')) focusEl.select();
                }, 80);
            }

            bindEditEvents(cell, recordId, fieldName, originalContent);
        }

        if (fieldType === 'select') {
            // Cargar opciones del servidor en base al campo
            $.ajax({
                url: window.campanasGetSelectOptionsUrl,
                type: 'GET',
                dataType: 'json',
                data: { field: fieldName },
                success: function(res) {
                    const options = (res && res.options) ? res.options : [];
                    let html = '<select class="inline-select form-select form-select-sm"><option value="">-- Seleccionar --</option>';
                    options.forEach(opt => {
                        const selected = (String(opt.value) === String(currentValue)) ? 'selected' : '';
                        html += `<option value="${opt.value}" ${selected}>${opt.text}</option>`;
                    });
                    html += '</select>';
                    createEditContainer(html);
                },
                error: function() {
                    showNotification('Error cargando opciones', 'error');
                    cell.removeClass('editing');
                    editingCell = null;
                }
            });
            return;
        }

        // Texto / Número
        const inputType = (fieldType === 'number') ? 'number' : 'text';
        const extra = (fieldType === 'number') ? ' step="0.01"' : '';
        const safeValue = String(currentValue).replace(/"/g, '&quot;');
        const input = `<input type="${inputType}" class="inline-input form-control form-control-sm" value="${safeValue}"${extra}>`;
        createEditContainer(input);
    });

    function bindEditEvents(cell, recordId, fieldName, originalContent) {
        // Guardar
        cell.off('click.save').on('click.save', '.save-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const saveBtn = $(this);
            let newValue;

            const selectEl = cell.find('.inline-select');
            const inputEl = cell.find('.inline-input');

            if (selectEl.length) {
                newValue = selectEl.val();
            } else if (inputEl.length) {
                newValue = inputEl.val();
                if (fieldName === 'inversion' || fieldName === 'retorno' || fieldName === 'mensajes') {
                    const isNumber = (fieldName === 'mensajes') ? parseInt : parseFloat;
                    newValue = isNumber(newValue);
                    if (isNaN(newValue)) newValue = 0;
                }
            } else {
                newValue = cell.data('current-value') ?? '';
            }

            $.ajax({
                url: window.campanasUpdateFieldUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    id: recordId,
                    field: fieldName,
                    value: newValue,
                    _csrf: yii.getCsrfToken()
                },
                beforeSend: function() {
                    saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Guardando...');
                },
                success: function(response) {
                    if (response && response.success) {
                        // Si el servidor envía HTML de reemplazo úsalo, si no, muestra el valor nuevo
                        let displayContent = response.newContent || response.display;

                        if (!displayContent) {
                            // Fallback local simple
                            if (fieldName === 'inversion' || fieldName === 'retorno') {
                                const num = parseFloat(newValue) || 0;
                                displayContent = '$' + num.toFixed(2);
                            } else if (fieldName === 'analisis') {
                                const estado = String(newValue || '');
                                const texto = estado.charAt(0).toUpperCase() + estado.slice(1).toLowerCase();
                                displayContent = '<span class="badge bg-secondary" style="font-weight:600;">' + texto + '</span>';
                            } else if (fieldName === 'asesor_id' || fieldName === 'campaña_id') {
                                // Para selects de catálogo, refrescar texto con llamada ligera
                                $.get(window.campanasGetSelectOptionsUrl, {field: fieldName}, function(res) {
                                    const opts = (res && res.options) ? res.options : [];
                                    const found = opts.find(o => String(o.value) === String(newValue));
                                    const nombre = found ? found.text : 'Sin asignar';
                                    const badge = `<span class="badge bg-light text-dark" style="font-weight:600;">${nombre}</span>`;
                                    cell.data('current-value', newValue);
                                    cell.html(badge);
                                }, 'json');
                                return; // ya actualizamos arriba
                            } else {
                                displayContent = (newValue !== null && newValue !== undefined) ? String(newValue) : '';
                            }
                        }

                        // Persistir nuevo valor para futuras ediciones
                        cell.data('current-value', Array.isArray(newValue) ? newValue.join(',') : newValue);
                        cell.html(displayContent);
                        cell.removeClass('editing');
                        editingCell = null;

                        if (response.message) showNotification(response.message, 'success');
                    } else {
                        cell.html(originalContent);
                        cell.removeClass('editing');
                        editingCell = null;
                        showNotification((response && response.message) ? response.message : 'Error al actualizar', 'error');
                    }
                },
                error: function(xhr) {
                    cell.html(originalContent);
                    cell.removeClass('editing');
                    editingCell = null;
                    let msg = 'Error de conexión';
                    try {
                        const r = JSON.parse(xhr.responseText);
                        msg = r.message || msg;
                    } catch(e) {}
                    showNotification(msg, 'error');
                },
                complete: function() {
                    saveBtn.prop('disabled', false).html('<i class="fas fa-save me-1"></i>Guardar');
                }
            });
        });

        // Cancelar
        cell.off('click.cancel').on('click.cancel', '.cancel-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            cell.html(originalContent);
            cell.removeClass('editing');
            editingCell = null;
        });

        // Enter/Escape en inputs
        cell.off('keydown.edit').on('keydown.edit', '.inline-input', function(e) {
            if (e.keyCode === 13) { // Enter
                e.preventDefault();
                cell.find('.save-btn').click();
            } else if (e.keyCode === 27) { // Esc
                e.preventDefault();
                cell.find('.cancel-btn').click();
            }
        });
    });

    // Cerrar edición si clic fuera
    $(document).on('click', function(e) {
        if (editingCell !== null && !$(e.target).closest('.edit-container').length) {
            const cancelBtn = editingCell.find('.cancel-btn');
            if (cancelBtn.length) cancelBtn.click();
        }
    });

    // Notificación reutilizable
    window.showNotification = window.showNotification || function(message, type) {
        const alertClass = (type === 'success') ? 'alert-success' : 'alert-danger';
        const icon = (type === 'success') ? 'fa-check-circle' : 'fa-exclamation-triangle';
        const node = document.createElement('div');
        node.className = `alert ${alertClass} alert-dismissible fade show notification-toast`;
        node.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 1060; min-width: 300px;';
        node.innerHTML = `<i class="fas ${icon} me-2"></i>${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
        document.body.appendChild(node);
        setTimeout(()=>{ $(node).fadeOut(function(){ $(this).remove(); }); }, 3000);
    };
});
JS);
?>