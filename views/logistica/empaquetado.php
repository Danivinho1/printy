<?php
use yii\helpers\Html;

$this->title = 'Empaquetado';
$this->params['breadcrumbs'][] = $this->title;

// Función de colores
function generarColorUnico($texto) {
    // Color específico para Retrabajo Urgente
    if ($texto === 'Retrabajo Urgente') {
        return ['bg' => '#007bff', 'text' => '#ffffff']; // Azul
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

<div class="empaquetado-index">
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

                    <!-- Entrega -->
                    <div class="entrega-badge">
                        <?php if ($model->venta && $model->venta->entrega): ?>
                            <?php $colores = generarColorUnico($model->venta->entrega->nombre); ?>
                            <span class="badge" style="background-color: <?= $colores['bg'] ?>; color: <?= $colores['text'] ?>;">
                                <?= Html::encode($model->venta->entrega->nombre) ?>
                            </span>
                        <?php else: ?>
                            <span class="badge bg-secondary">No definido</span>
                        <?php endif; ?>
                    </div>

                    <!-- Extras -->
                    <div class="extras">
                        <?php 
                        $extras = $model->venta ? $model->venta->extras : [];
                        if (!empty($extras)):
                            foreach ($extras as $extra):
                                $coloresExtra = generarColorUnico($extra->nombre);
                        ?>
                                <span class="badge" style="background-color: <?= $coloresExtra['bg'] ?>; color: <?= $coloresExtra['text'] ?>;">
                                    <?= Html::encode($extra->nombre) ?>
                                </span>
                        <?php 
                            endforeach;
                        else: ?>
                            <span class="badge bg-light text-dark">Ninguno</span>
                        <?php endif; ?>
                    </div>

                    <!-- Días para empaquetar / retraso -->
                    <div class="dias-empaquetado" id="dias-empaquetado-<?= $model->id ?>">
                        <?php
                        $hoy = new \DateTime();
                        $fechaLiquidado = $model->fecha_pago_liquidado ? new \DateTime($model->fecha_pago_liquidado) : null;

                        if(!$fechaLiquidado){
                            echo '<span class="badge bg-secondary">Fecha de pago no definida</span>';
                        } else {
                            $fechaLimite = clone $fechaLiquidado;
                            $fechaLimite->modify('+1 day');

                            if($model->revision_calidad == 1){
                                $textoBadge = $hoy <= $fechaLimite ? 'Empaquetado a tiempo' : 'Empaquetado con retraso';
                                $colorBadge = $hoy <= $fechaLimite ? '#28a745' : '#dc3545';
                                echo "<span class='badge' style='background-color:{$colorBadge};color:white;'>{$textoBadge}</span>";
                            } else {
                                $diff = $hoy <= $fechaLimite ? $hoy->diff($fechaLimite)->days + 1 : $hoy->diff($fechaLimite)->days + 1;
                                $textoBadge = $hoy <= $fechaLimite ? "Falta {$diff} día(s) para empaquetar" : "{$diff} día(s) de retraso";
                                $colorBadge = $hoy <= $fechaLimite ? '#e7a108ff' : '#dc3545';
                                echo "<span class='badge' style='background-color:{$colorBadge};color:white;'>{$textoBadge}</span>";
                            }
                        }
                        ?>
                    </div>

                    <!-- Toggle empaquetado -->
                    <div class="estado-toggle">
                        <?php $checked = $model->revision_calidad == 1; ?>
                        <label class="toggle-checkbox">
                            <input type="checkbox" 
                                   class="toggle-input" 
                                   <?= $checked ? 'checked' : '' ?>
                                   data-id="<?= $model->id ?>"
                                   data-field="revision_calidad">
                            <span class="toggle-slider">
                                <span class="toggle-text">
                                    <?= $checked ? 'Listo' : 'Pendiente' ?>
                                </span>
                            </span>
                        </label>
                    </div>

                    <!-- Botón Retrabajo -->
                    <div class="retrabajo-button">
                        <button type="button" 
                                class="btn btn-retrabajo" 
                                data-id="<?= $model->id ?>"
                                data-nombre="<?= Html::encode($model->nombre_letrero) ?>">
                            <i class="fas fa-tools"></i>
                            Retrabajo
                        </button>
                    </div>

                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal para Retrabajo -->
<div class="modal fade" id="retrabajoModal" tabindex="-1" aria-labelledby="retrabajoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="retrabajoModalLabel">
                    <i class="fas fa-tools text-warning"></i>
                    Retrabajo - <span id="nombreLetreroModal" class="text-muted"></span>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold mb-2">Área del error:</label>
                    <div class="area-selection-compact">
                        <div class="area-btn" data-area="impresion">
                            <input type="radio" name="area_error" id="area_impresion" value="impresion" hidden>
                            <label for="area_impresion">
                                <i class="fas fa-print"></i>
                                <span>Impresión</span>
                            </label>
                        </div>
                        
                        <div class="area-btn" data-area="corte">
                            <input type="radio" name="area_error" id="area_corte" value="corte" hidden>
                            <label for="area_corte">
                                <i class="fas fa-cut"></i>
                                <span>Corte</span>
                            </label>
                        </div>
                        
                        <div class="area-btn" data-area="fabricacion">
                            <input type="radio" name="area_error" id="area_fabricacion" value="fabricacion" hidden>
                            <label for="area_fabricacion">
                                <i class="fas fa-hammer"></i>
                                <span>Fabricación</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="observaciones" class="form-label">Observaciones:</label>
                    <textarea class="form-control form-control-sm" id="observaciones" rows="2" placeholder="Breve descripción del problema..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning btn-sm" id="confirmarRetrabajo">
                    <i class="fas fa-check"></i> Registrar
                </button>
            </div>
        </div>
    </div>
</div>

<?php
// Estilos para las barras - copiado de la vista que funciona + nuevos estilos
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

.entrega-badge {
    flex-shrink: 0;
    min-width: 120px;
    text-align: center;
}

.entrega-badge .badge {
    font-size: 0.85rem;
    padding: 6px 12px;
    border-radius: 20px;
}

.extras {
    flex-shrink: 0;
    min-width: 120px;
    text-align: center;
}

.dias-empaquetado {
    flex-shrink: 0;
    min-width: 180px;
    text-align: center;
}

.estado-toggle {
    flex-shrink: 0;
    min-width: 120px;
}

/* Botón Retrabajo */
.retrabajo-button {
    flex-shrink: 0;
    min-width: 110px;
}

.btn-retrabajo {
    background: linear-gradient(135deg, #31b4e3ff, #31b4e3ff);
    border: none;
    color: white;
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(255, 149, 0, 0.3);
    width: 100%;
}

.btn-retrabajo:hover {
    background: linear-gradient(135deg, #ffcc00ff,  #ffcc00ff);
    color: white;);
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(255, 149, 0, 0.4);
}

.btn-retrabajo i {
    margin-right: 6px;
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
    font-size: 0.65rem;
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

/* Forzar color azul para badges de Retrabajo Urgente */
.badge:contains('Retrabajo Urgente') {
    background-color: #007bff !important;
    color: white !important;
}

/* Como :contains no funciona en CSS puro, usaremos JavaScript */

/* Modal de Retrabajo - Versión Compacta */
.modal-content {
    border: none;
    border-radius: 8px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.12);
}

.modal-header {
    border-bottom: 1px solid #e9ecef;
    padding: 1rem 1.25rem 0.75rem;
}

.modal-title {
    font-weight: 600;
    font-size: 1rem;
    color: #495057;
}

.modal-body {
    padding: 1rem 1.25rem;
}

.modal-footer {
    border-top: 1px solid #e9ecef;
    padding: 0.75rem 1.25rem;
    gap: 8px;
}

/* Área de selección compacta */
.area-selection-compact {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 8px;
}

.area-btn {
    position: relative;
    border-radius: 6px;
    overflow: hidden;
    transition: all 0.2s ease;
}

.area-btn label {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 12px 8px;
    border: 2px solid #e9ecef;
    border-radius: 6px;
    background: white;
    cursor: pointer;
    transition: all 0.2s ease;
    margin: 0;
    min-height: 70px;
}

.area-btn:hover label {
    border-color: #ff9500;
    background-color: #fff8f0;
}

.area-btn.selected label {
    border-color: #ff9500;
    background-color: #fff8f0;
    color: #ff7b00;
}

.area-btn i {
    font-size: 1.2rem;
    margin-bottom: 4px;
    color: #ff9500;
}

.area-btn span {
    font-size: 0.8rem;
    font-weight: 600;
    text-align: center;
    color: #495057;
}

.area-btn.selected span {
    color: #ff7b00;
}

.form-control:focus {
    border-color: #ff9500;
    box-shadow: 0 0 0 0.15rem rgba(255, 149, 0, 0.15);
}

#confirmarRetrabajo:disabled {
    background: #6c757d;
    border-color: #6c757d;
}

/* Responsive para modal compacto */
@media (max-width: 576px) {
    .area-selection-compact {
        grid-template-columns: 1fr;
        gap: 6px;
    }
    
    .area-btn label {
        flex-direction: row;
        justify-content: flex-start;
        min-height: 50px;
        text-align: left;
    }
    
    .area-btn i {
        margin-right: 8px;
        margin-bottom: 0;
    }
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
    .entrega-badge,
    .extras,
    .dias-empaquetado,
    .estado-toggle,
    .retrabajo-button {
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
    
    .btn-retrabajo {
        max-width: 200px;
        margin: 0 auto;
    }
}
");
$csrf = Yii::$app->request->getCsrfToken();
$this->registerJs("
$(document).on('change', '.toggle-input', function() {
    var input = $(this);
    var checkbox = input.closest('.toggle-checkbox');
    var id = input.data('id');
    var field = input.data('field');
    var isChecked = input.is(':checked');

    if (checkbox.hasClass('loading')) {
        input.prop('checked', !isChecked);
        return;
    }

    checkbox.addClass('loading');

    $.post('" . \yii\helpers\Url::to(['/logistica/toggle-empaquetado']) . "', {
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

            // Actualizar badge de días/empaquetado
            if(data.badgeHtml) {
                $('#dias-empaquetado-' + id).html(data.badgeHtml);
            }
        } else if(data.error){
            input.prop('checked', !isChecked);
            alert('Error: ' + data.error);
        }
    })
    .fail(function(){
        input.prop('checked', !isChecked);
        alert('Error de conexión. Inténtalo de nuevo.');
    })
    .always(function(){
        setTimeout(function(){
            checkbox.removeClass('loading');
        }, 300);
    });
});
");

$csrf = Yii::$app->request->getCsrfToken();
$this->registerJs("
// Función para aplicar color azul a badges de Retrabajo Urgente
function aplicarColorRetrabajoUrgente() {
    $('.entrega-badge .badge').each(function() {
        if ($(this).text().trim() === 'Retrabajo Urgente') {
            $(this).css({
                'background-color': '#007bff',
                'color': 'white'
            });
            console.log('Color azul aplicado a badge de entrega');
        }
    });
}

// Aplicar colores al cargar la página
$(document).ready(function() {
    aplicarColorRetrabajoUrgente();
});

// Variables globales para el modal
var currentItemId = null;



// Botón de Retrabajo
$(document).on('click', '.btn-retrabajo', function() {
    currentItemId = $(this).data('id');
    var nombreLetrero = $(this).data('nombre');
    
    // Debug: verificar qué ID se está obteniendo
    console.log('ID del botón:', currentItemId);
    console.log('Nombre del letrero:', nombreLetrero);
    
    // Establecer el nombre del letrero en el modal
    $('#nombreLetreroModal').text(nombreLetrero);
    
    // Limpiar selecciones previas
    $('input[name=\"area_error\"]').prop('checked', false);
    $('.area-btn').removeClass('selected');
    $('#observaciones').val('');
    $('#confirmarRetrabajo').prop('disabled', true);
    
    // Mostrar el modal
    $('#retrabajoModal').modal('show');
});

// Selección de área de error (versión compacta)
$(document).on('click', '.area-btn', function() {
    var input = $(this).find('input[name=\"area_error\"]');
    
    // Limpiar selecciones previas
    $('.area-btn').removeClass('selected');
    $('input[name=\"area_error\"]').prop('checked', false);
    
    // Seleccionar actual
    $(this).addClass('selected');
    input.prop('checked', true);
    $('#confirmarRetrabajo').prop('disabled', false);
});

// Confirmar retrabajo
$(document).on('click', '#confirmarRetrabajo', function() {
    var areaError = $('input[name=\"area_error\"]:checked').val();
    var observaciones = $('#observaciones').val();
    
    if (!areaError) {
        alert('Por favor selecciona un área de error.');
        return;
    }
    
    // Deshabilitar botón mientras se procesa
    $(this).prop('disabled', true).html('<i class=\"fas fa-spinner fa-spin\"></i> Procesando...');
    
    $.post('" . \yii\helpers\Url::to(['/logistica/registrar-retrabajo']) . "', {
        id: currentItemId,
        area_error: areaError,
        observaciones: observaciones,
        _csrf: '$csrf'
    })
    .done(function(data){
        if(data.success){
            $('#retrabajoModal').modal('hide');
            
            // Mostrar mensaje de éxito
            var alertHtml = '<div class=\"alert alert-success alert-dismissible fade show\" role=\"alert\">' +
                           '<i class=\"fas fa-check-circle\"></i> ' +
                           'Retrabajo registrado correctamente para el área de ' + areaError + '.' +
                           '<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button>' +
                           '</div>';
            
            $('.empaquetado-index').prepend(alertHtml);
            
            // Opcional: Recargar la página o actualizar la vista
            // location.reload();
        } else {
            alert('Error: ' + (data.error || 'No se pudo registrar el retrabajo'));
        }
    })
    .fail(function(){
        alert('Error de conexión. Inténtalo de nuevo.');
    })
    .always(function(){
        $('#confirmarRetrabajo').prop('disabled', false).html('<i class=\"fas fa-check\"></i> Registrar Retrabajo');
    });
});

// Limpiar modal al cerrarse
$(document).on('hidden.bs.modal', '#retrabajoModal', function() {
    currentItemId = null;
    $('input[name=\"area_error\"]').prop('checked', false);
    $('.area-btn').removeClass('selected');
    $('#observaciones').val('');
    $('#confirmarRetrabajo').prop('disabled', true);
});
");
?>