<?php
use yii\helpers\Html;

$this->title = 'Impresión';
$this->params['breadcrumbs'][] = $this->title;

// Reutilizamos la misma función de colores del index
function generarColorUnico($texto) {
    $textoLower = strtolower(trim($texto));
    if ($textoLower === 'urgente') {
        return ['bg' => '#dc3545', 'text' => '#ffffff'];
    }
    $coloresSuaves = [
        ['bg' => '#e3f2fd', 'text' => '#1565c0'], ['bg' => '#e8f5e8', 'text' => '#2e7d32'],
        ['bg' => '#fff3e0', 'text' => '#ef6c00'], ['bg' => '#f3e5f5', 'text' => '#7b1fa2'],
        ['bg' => '#e0f2f1', 'text' => '#00695c'], ['bg' => '#fce4ec', 'text' => '#c2185b'],
        ['bg' => '#f5f5f5', 'text' => '#424242'], ['bg' => '#e1f5fe', 'text' => '#0277bd'],
        ['bg' => '#fff8e1', 'text' => '#f57f17'], ['bg' => '#f9fbe7', 'text' => '#689f38'],
        ['bg' => '#fef7ff', 'text' => '#8e24aa'], ['bg' => '#e8eaf6', 'text' => '#3f51b5']
    ];
    $hash = crc32($texto);
    $indice = abs($hash) % count($coloresSuaves);
    return $coloresSuaves[$indice];
}
?>

<div class="impresion-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <!-- Encabezado general de la sección -->
    <div class="encabezado-toggles" style="display: flex; justify-content: flex-end; margin-bottom: 10px;">
    <strong>Impresión Lista / No Impresión</strong>
</div>

  
    <div class="items-container">
        <?php foreach ($dataProvider->getModels() as $model): ?>
            <div class="item-bar">
                <div class="bar-content">
                    <!-- Tipo de letrero -->
                    <div class="tipo-badge">
                        <?php if ($model->tipoLetrero): ?>
                            <?php $colores = generarColorUnico($model->tipoLetrero->nombre); ?>
                            <span class="badge" style="background-color: <?= $colores['bg'] ?>; color: <?= $colores['text'] ?>;">
                                <?= Html::encode($model->tipoLetrero->nombre) ?>
                            </span>
                        <?php else: ?>
                            <span class="badge bg-secondary">No definido</span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Nombre del letrero -->
                    <div class="nombre-letrero">
                        <h6><?= Html::encode($model->nombre_letrero) ?></h6>
                    </div>

                    <!-- Entrega -->
                    <div class="entrega">
                        <?php 
                        $entregaNombre = $model->venta->entrega->nombre ?? null;
                        if ($entregaNombre): 
                            $colores = generarColorUnico($entregaNombre);
                        ?>
                            <span class="badge" style="background-color: <?= $colores['bg'] ?>; color: <?= $colores['text'] ?>;">
                                <?= Html::encode($entregaNombre) ?>
                            </span>
                        <?php else: ?>
                            <span class="badge bg-secondary">No definido</span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Unidades -->
                    <div class="unidades">
                        <span class="unidades-count"><?= $model->unidades ?> unidades</span>
                    </div>
                    <!-- Enlace Vector -->
    <input type="text" 
           class="form-control enlace-vector-input" 
           value="<?= Html::encode($model->enlace_vector) ?>"
           placeholder="Agregar enlace del vector"
           style="width: 200px; height: 30px;"
           data-id="<?= $model->id ?>">
                    
                    <!-- Estado/Toggle + Enlace Vector -->
                    <div class="estado-toggle" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">

    <!-- Diseño/Impresión -->
    <label class="toggle-checkbox">
        <input type="checkbox" 
               class="toggle-input" 
               <?= $model->diseno_impresion == 1 ? 'checked' : '' ?>
               data-id="<?= $model->id ?>"
               data-field="diseno_impresion"
               data-text-on="Listo"
               data-text-off="Pendiente">
        <span class="toggle-slider">
            <span class="toggle-text">
                <?= $model->diseno_impresion == 1 ? 'Listo' : 'Pendiente' ?>
            </span>
        </span>
    </label>


    <!-- No Impresión -->
    <?php $checkedNoImpresion = $model->no_impresion == 1; ?>
    <label class="toggle-checkbox">
        <input type="checkbox" 
               class="toggle-input" 
               <?= $checkedNoImpresion ? 'checked' : '' ?>
               data-id="<?= $model->id ?>"
               data-field="no_impresion"
               data-text-on="No impresión"
               data-text-off="No impresion">
        <span class="toggle-slider">
            <span class="toggle-text">
                <?= $checkedNoImpresion ? "No impresión" : "No impresion" ?>
            </span>
        </span>
    </label>

               </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php
// Estilos para las barras
$this->registerCss("
.items-container {
    max-width: 100%;
}

.item-bar {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    margin-bottom: 12px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.item-bar:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    transform: translateY(-1px);
}

.bar-content {
    display: flex;
    align-items: center;
    padding: 16px 20px;
    gap: 20px;
}

.tipo-badge {
    flex-shrink: 0;
    min-width: 120px;
}

.tipo-badge .badge {
    font-size: 0.85rem;
    padding: 6px 12px;
    border-radius: 20px;
}

.nombre-letrero {
    flex-grow: 1;
    min-width: 0;
}

.nombre-letrero h6 {
    margin: 0;
    font-weight: 600;
    font-size: 1rem;
    color: #495057;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.unidades {
    flex-shrink: 0;
    min-width: 100px;
    text-align: center;
}

.unidades-count {
    background: #f8f9fa;
    color: #6c757d;
    padding: 6px 12px;
    border-radius: 15px;
    font-size: 0.875rem;
    font-weight: 500;
}

.estado-toggle {
    flex-shrink: 0;
    min-width: 120px;
}

/* Toggle personalizado */
.toggle-checkbox {
    position: relative;
    display: inline-block;
    width: 100px;
    height: 40px;
    cursor: pointer;
}

/* Toggle específico vector */
.toggle-checkbox[data-field='vector_listo'] .toggle-text {
    font-size: 0.65rem;        /* más pequeño */
    line-height: 0.8rem;       /* controlar separación entre líneas */
    white-space: pre-line;      /* respetar saltos de línea */
    text-align: center;
    padding-left: 2px;          /* ajuste fino horizontal */
    padding-right: 2px;
}

.toggle-input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #dc3545;
    border-radius: 10px;
    transition: all 0.4s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.toggle-text {
    color: white;
    font-weight: 600;
    font-size: 0.65rem; /* texto más pequeño */
    line-height: 1.1rem; /* ajustar altura de línea */
    text-align: center;  /* centrado, útil para vector */
    white-space: normal; /* permite salto de línea */
    transition: all 0.3s ease;
}


.toggle-input:checked + .toggle-slider {
    background-color: #28a745;
}

.toggle-input:checked + .toggle-slider:before {
    transform: translateX(5px);
}

.toggle-slider:before {
    content: '';
    position: absolute;
    height: 26px; /* reducido */
    width: 26px;  /* reducido */
    left: 4px;
    background-color: white;
    border-radius: 50%;
    transition: all 0.4s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    z-index: 1; /* debajo del texto */
}


.toggle-input:checked + .toggle-slider:before {
    transform: translateX(60px);
}

/* Estados de carga */
.toggle-checkbox.loading .toggle-slider {
    opacity: 0.7;
    pointer-events: none;
}

/* Responsive */
@media (max-width: 768px) {
    .bar-content {
        flex-direction: column;
        align-items: stretch;
        gap: 12px;
        padding: 16px;
    }
    
    .tipo-badge,
    .unidades,
    .estado-toggle {
        min-width: auto;
        text-align: center;
    }
    
    .nombre-letrero h6 {
        text-align: center;
        white-space: normal;
    }
    
    .toggle-checkbox {
        width: 100px;
        margin: 0 auto;
    }
}

@media (max-width: 576px) {
    .item-bar {
        margin-bottom: 8px;
    }
    
    .bar-content {
        padding: 12px;
        gap: 10px;
    }
    
    .tipo-badge .badge {
        font-size: 0.75rem;
        padding: 4px 8px;
    }
    
    .nombre-letrero h6 {
        font-size: 0.9rem;
    }
    
    .toggle-checkbox {
        width: 80px;
        height: 32px;
    }
    
    .toggle-slider:before {
        height: 24px;
        width: 24px;
        left: 4px;
    }
    
    .toggle-input:checked + .toggle-slider:before {
        transform: translateX(44px);
    }
    
    .toggle-text {
        font-size: 0.75rem;
    }
}
");

$csrf = Yii::$app->request->getCsrfToken();
$this->registerJs("
    // Guardar toggles (tu código existente)
    $(document).on('change', '.toggle-input', function() {
        var input = $(this);
        var checkbox = input.closest('.toggle-checkbox');
        var id = input.data('id');
        var field = input.data('field');
        var isChecked = input.is(':checked');
        var value = isChecked ? 1 : 0;

        if (checkbox.hasClass('loading')) {
            input.prop('checked', !isChecked);
            return;
        }

        checkbox.addClass('loading');

        $.post('" . \yii\helpers\Url::to(['/produccion/toggle']) . "', {
            id: id,
            field: field,
            value: value,
            _csrf: '$csrf'
        })
        .done(function(data) {
            var toggleText = checkbox.find('.toggle-text');
            var onText = input.data('text-on') || 'Listo';
            var offText = input.data('text-off') || 'Pendiente';

            if (data.success) {
                toggleText.text(value == 1 ? onText : offText);
                input.prop('checked', value == 1);
            } else if (data.error) {
                input.prop('checked', !isChecked);
                alert('Error: ' + data.error);
            }
        })
        .fail(function() {
            input.prop('checked', !isChecked);
            alert('Error de conexión. Inténtalo de nuevo.');
        })
        .always(function() {
            checkbox.removeClass('loading');
        });
    });

    $(document).on('keypress', '.enlace-vector-input', function(e) {
        if(e.which === 13) { // Enter
            e.preventDefault();

            var input = $(this);
            var id = input.data('id');
            var value = input.val().trim();

            if(id === undefined) {
                alert('ID no encontrado para guardar el enlace.');
                return;
            }

            $.ajax({
                url: '" . \yii\helpers\Url::to(['/produccion/guardar-enlace']) . "',
                type: 'POST',
                dataType: 'json',
                data: {
                    id: id,
                    enlace_vector: value,
                    _csrf: '$csrf'
                },
                success: function(response) {
                    if(response.success) {
                        input.css('border', '2px solid #28a745');
                        setTimeout(function(){ input.css('border', ''); }, 1000);
                    } else if(response.error) {
                        alert('Error: ' + response.error);
                    }
                },
                error: function() {
                    alert('Error de conexión. Inténtalo de nuevo.');
                }
            });
        }
    });
");
