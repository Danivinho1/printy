<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap5\Tabs;
use app\widgets\CustomGridView;

/** @var yii\web\View $this */
/** @var app\models\CampanasSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

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
        case 'pausa':
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
?>

<div class="campanas-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <!-- Pestañas de Marketing -->
    <?= Tabs::widget([
        'items' => [
            [
                'label' => 'Campañas',
                'active' => true,
                'url' => Url::to(['campanas/index']),
            ],
            [
                'label' => 'Retorno',
                'url' => Url::to(['retorno/index']),
            ],
            [
                'label' => 'Comparativas Mensuales',
                'url' => Url::to(['ventas-mensuales/index']),
            ],
            [
                'label' => 'Productos Vendidos',
                'url' => Url::to(['productos-vendidos/index']),
            ],
        ],
        'options' => ['class' => 'mb-4'],
    ]); ?>

    <!-- Barra superior con filtros estilo Diseño y acciones -->
    <div class="filtros-diseño mb-4 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <a href="<?= Url::to(['campanas/index']) ?>" class="btn-filtro <?= !$filtroAnalisis ? 'active' : '' ?>">
                Todos
            </a>

            <a href="<?= Url::to(['campanas/index', 'analisis' => 'analizar']) ?>"
                class="btn-filtro <?= $filtroAnalisis === 'analizar' ? 'active' : '' ?>">
                Analizar
            </a>

            <a href="<?= Url::to(['campanas/index', 'analisis' => 'pausa']) ?>"
                class="btn-filtro <?= $filtroAnalisis === 'pausa' ? 'active' : '' ?>">
                Pausa
            </a>

            <a href="<?= Url::to(['campanas/index', 'analisis' => 'continuar']) ?>"
                class="btn-filtro <?= $filtroAnalisis === 'continuar' ? 'active' : '' ?>">
                Continuar
            </a>

            <a href="<?= Url::to(['campanas/index', 'analisis' => 'detener']) ?>"
                class="btn-filtro <?= $filtroAnalisis === 'detener' ? 'active' : '' ?>">
                Detener
            </a>

            <a href="<?= Url::to(['campanas/index', 'analisis' => 'experimento']) ?>"
                class="btn-filtro <?= $filtroAnalisis === 'experimento' ? 'active' : '' ?>">
                Experimento
            </a>
        </div>

        <div class="d-flex align-items-center gap-2">
            <?= Html::a(
                '<i class="fas fa-file-excel me-2"></i>Exportar a Excel',
                ['campanas/export-excel'],
                [
                    'class' => 'btn btn-light border btn-sm',
                    'title' => 'Descargar campañas en Excel',
                    'data-bs-toggle' => 'tooltip',
                    'data-bs-placement' => 'top'
                ]
            )
                ?>

            <?= Html::a('+ Crear Campaña', ['create'], [
                'class' => 'btn btn-primary btn-sm'
            ]) ?>
        </div>
    </div>

    <style>
        .filtros-diseño {
            background-color: #f8f9fa;
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #e9ecef;
        }

        .btn-filtro {
            background-color: #ffffff;
            border: 1px solid #dee2e6;
            color: #6c757d;
            padding: 4px 10px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 12px;
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

        @media (max-width: 768px) {
            .filtros-diseño .d-flex {
                flex-wrap: wrap;
            }

            .btn-filtro {
                margin-bottom: 5px;
                font-size: 11px;
                padding: 3px 8px;
            }
        }

        /* Estilo cuando una celda está en edición */
        .editable-field.editing {
            position: relative;
            z-index: 2;
        }
    </style>

    <?= CustomGridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel ?? null,
        'summary' => false,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            // Campaña (badge con color, clic para editar select)
            [
    'attribute' => 'nombre',
    'format' => 'raw',
    'value' => function ($model) {
        $display = Html::encode((string)$model->nombre);
        $current = Html::encode((string)$model->nombre);
        return "<div class='editable-field' data-field-type='text' data-field-name='nombre' data-record-id='{$model->id}' data-current-value='{$current}' title='Click para editar'>{$display}</div>";
    }
],

            [
                'attribute' => 'campaña_id',
                'label' => 'Campaña',
                'format' => 'raw',
                'value' => function ($model) {
            $nombre = catalogName($model->campaña_id ?? null);
            $colores = generarColorUnico($nombre);
            $badge = Html::tag('span', Html::encode($nombre), [
                'class' => 'badge',
                'style' => "background-color:{$colores['bg']};color:{$colores['text']}"
            ]);
            $current = $model->campaña_id ?? '';
            return "<div class='editable-field' data-field-type='select' data-field-name='campaña_id' data-record-id='{$model->id}' data-current-value='{$current}' title='Click para editar'>{$badge}</div>";
        }
            ],

            // Asesor (badge con color, clic para editar select)
            [
                'attribute' => 'asesor_id',
                'label' => 'Asesor',
                'format' => 'raw',
                'value' => function ($model) {
            $nombre = catalogName($model->asesor_id ?? null);
            $colores = generarColorUnico($nombre);
            $badge = Html::tag('span', Html::encode($nombre), [
                'class' => 'badge',
                'style' => "background-color:{$colores['bg']};color:{$colores['text']}"
            ]);
            $current = $model->asesor_id ?? '';
            return "<div class='editable-field' data-field-type='select' data-field-name='asesor_id' data-record-id='{$model->id}' data-current-value='{$current}' title='Click para editar'>{$badge}</div>";
        }
            ],

            // Inversión (número con formato)
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

            // Retorno (número con formato)
            [
                'attribute' => 'retorno',
                'format' => 'raw',
                'value' => function ($model) {
            $valor = (float) ($model->retorno ?? 0);
            $mostrar = '$' . number_format($valor, 2);
            return "<div class='editable-field' data-field-type='number' data-field-name='retorno' data-record-id='{$model->id}' data-current-value='{$valor}' title='Click para editar'>{$mostrar}</div>";
        }
            ],

            // Análisis (badge de estado, clic para editar select)
            [
                'attribute' => 'analisis',
                'label' => 'Análisis',
                'format' => 'raw',
                'value' => function ($model) {
            $estado = $model->analisis ?: 'analizar';
            $colores = generarColorAnalisis($estado);
            $texto = ucfirst($estado);
            $badge = Html::tag('span', Html::encode($texto), [
                'class' => 'badge',
                'style' => "background-color:{$colores['bg']};color:{$colores['text']}"
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

<?php
// Exponer URLs como variables JS globales (compatible con cualquier versión de Yii2)
$this->registerJs(
    'window.campanasUpdateFieldUrl = ' . json_encode(Url::to(['campanas/update-field'])) . ';
     window.campanasGetSelectOptionsUrl = ' . json_encode(Url::to(['campanas/get-select-options'])) . ';',
    \yii\web\View::POS_HEAD
);

// Script principal en nowdoc para evitar interpolación de ${...}
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
                                const texto = estado.charAt(0).toUpperCase() + estado.slice(1);
                                displayContent = '<span class="badge bg-secondary">' + texto + '</span>';
                            } else if (fieldName === 'asesor_id' || fieldName === 'campaña_id') {
                                // Para selects de catálogo, refrescar texto con llamada ligera
                                $.get(window.campanasGetSelectOptionsUrl, {field: fieldName}, function(res) {
                                    const opts = (res && res.options) ? res.options : [];
                                    const found = opts.find(o => String(o.value) === String(newValue));
                                    const nombre = found ? found.text : 'Sin asignar';
                                    const badge = `<span class="badge bg-light text-dark">${nombre}</span>`;
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
    }

    // Cerrar edición si clic fuera
    $(document).on('click', function(e) {
        if (editingCell !== null && !$(e.target).closest('.edit-container').length) {
            const cancelBtn = editingCell.find('.cancel-btn');
            if (cancelBtn.length) cancelBtn.click();
        }
    });

    function showNotification(message, type) {
        const alertClass = (type === 'success') ? 'alert-success' : 'alert-danger';
        const icon = (type === 'success') ? 'fa-check-circle' : 'fa-exclamation-triangle';

        const notification = `
            <div class="alert ${alertClass} alert-dismissible fade show notification-toast" role="alert"
                 style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                <i class="fas ${icon} me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        $('body').append(notification);
        setTimeout(function() {
            $('.notification-toast').fadeOut(function() { $(this).remove(); });
        }, 3000);
    }
});
JS);
?>