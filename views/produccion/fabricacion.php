<?php
use yii\helpers\Html;

$this->title = 'Fabricacion';
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
                    
                    <!-- Unidades -->
                    <div class="unidades">
                        <span class="unidades-count"><?= $model->unidades ?> unidades</span>
                    </div>
                    
                    <!-- Estado/Toggle -->
                    <div class="estado-toggle">
                        <?php $checked = $model->fabricacion_listo == 1; ?>
                        <label class="toggle-checkbox">
                            <input type="checkbox" 
                                   class="toggle-input" 
                                   <?= $checked ? 'checked' : '' ?>
                                   data-id="<?= $model->id ?>"
                                   data-field="fabricacion_listo">
                            <span class="toggle-slider">
                                <span class="toggle-text">
                                    <?= $checked ? 'Listo' : 'Pendiente' ?>
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
    border-radius: 20px;
    transition: all 0.4s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.toggle-text {
    color: white;
    font-weight: 600;
    font-size: 0.875rem;
    transition: all 0.3s ease;
}

.toggle-input:checked + .toggle-slider {
    background-color: #28a745;
}

.toggle-input:checked + .toggle-slider:before {
    transform: translateX(26px);
}

.toggle-slider:before {
    content: '';
    position: absolute;
    height: 32px;
    width: 32px;
    left: 4px;
    background-color: white;
    border-radius: 50%;
    transition: all 0.4s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
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

// JS para actualizar con AJAX
$csrf = Yii::$app->request->getCsrfToken();
$this->registerJs("
$(document).on('change', '.toggle-input', function() {
    var input = $(this);
    var checkbox = input.closest('.toggle-checkbox');
    var id = input.data('id');
    var field = input.data('field');
    var isChecked = input.is(':checked');
    
    // Prevenir múltiples clicks
    if (checkbox.hasClass('loading')) {
        input.prop('checked', !isChecked);
        return;
    }
    
    // Agregar estado de carga
    checkbox.addClass('loading');
    
    $.post('" . \yii\helpers\Url::to(['/produccion/toggle']) . "', {
        id: id,
        field: field,
        _csrf: '$csrf'
    })
    .done(function(data){
        if(data.success){
            // Actualizar texto del toggle
            var toggleText = checkbox.find('.toggle-text');
            toggleText.text(data.value == 1 ? 'Listo' : 'Pendiente');
            
            // Asegurar que el checkbox esté en el estado correcto
            input.prop('checked', data.value == 1);
        } else if(data.error){
            // Revertir el estado si hay error
            input.prop('checked', !isChecked);
            alert('Error: ' + data.error);
        }
    })
    .fail(function(){
        // Revertir el estado si falla la petición
        input.prop('checked', !isChecked);
        alert('Error de conexión. Inténtalo de nuevo.');
    })
    .always(function(){
        // Remover estado de carga
        setTimeout(function(){
            checkbox.removeClass('loading');
        }, 300);
    });
});
");
?>