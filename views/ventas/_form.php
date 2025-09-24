<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Catalogos;
use yii\bootstrap5\BootstrapAsset;
use yii\bootstrap5\BootstrapPluginAsset;

/** @var yii\web\View $this */
/** @var app\models\Ventas $model */
/** @var yii\widgets\ActiveForm $form */

// Registrar assets de Bootstrap 5
BootstrapAsset::register($this);
BootstrapPluginAsset::register($this);

?>


<div class="ventas-form">

    <?php $form = ActiveForm::begin([
        'id' => 'ventasForm',
        'options' => ['class' => 'ventas-form-content'],
        'fieldConfig' => [
            'template' => '<div class="form-group">{label}{input}{error}</div>',
            'options' => ['class' => 'mb-0']
        ]
    ]); ?>
    <?php
$catalogos = ['tipo_letrero', 'entrega', 'adicionales', 'extras', 'medio', 'campaña', 'asesor'];
?>

<!-- CSS hover para botones -->
<style>
.botones-catalogo button {
    opacity: 0;
    transition: opacity 0.3s ease;
}
.form-group:hover .botones-catalogo button {
    opacity: 1;
}
</style>

<!-- Datos principales -->
<div class="row row-3">
    <div class="form-group">
        <?= $form->field($model, 'tipo_letrero_id')->dropDownList(
            Catalogos::getLista('tipo_letrero'),
            ['prompt' => 'Seleccione un tipo', 'id' => 'tipo_letrero_id', 'class' => 'form-select']
        )->label('🏷️ Tipo de Letrero') ?>
        <div class="btn-group botones-catalogo mt-2" role="group">
            <button type="button" class="btn btn-sm btn-outline-warning abrir-modal" data-tipo="tipo_letrero">➕</button>
            <button type="button" class="btn btn-sm btn-outline-info editar-catalogo" data-tipo="tipo_letrero">✏️</button>
        </div>
    </div>

    <div class="form-group">
        <?= $form->field($model, 'nombre_letrero')->textInput(['maxlength' => true])->label('📝 Nombre del Letrero') ?>
    </div>

    <div class="form-group">
        <?= $form->field($model, 'entrega_id')->dropDownList(
            Catalogos::getLista('entrega'),
            ['prompt' => 'Seleccione una entrega', 'id' => 'entrega_id', 'class' => 'form-select']
        )->label('🚚 Tipo de Entrega') ?>
        <div class="btn-group botones-catalogo mt-2" role="group">
            <button type="button" class="btn btn-sm btn-outline-warning abrir-modal" data-tipo="entrega">➕</button>
            <button type="button" class="btn btn-sm btn-outline-info editar-catalogo" data-tipo="entrega">✏️</button>
        </div>
    </div>
</div>

<!-- Fecha de entrega (oculta por defecto) -->
<div class="row" id="fecha-entrega-container" style="display:none;">
    <div class="form-group col-12 col-md-4">
        <?= $form->field($model, 'fecha_entrega')->input('date')->label('📆 Fecha de Entrega') ?>
    </div>
</div>

<!-- Medio, Teléfono y Campaña -->
<div class="row row-3">
    <div class="form-group">
        <?= $form->field($model, 'medio_id')->dropDownList(
            Catalogos::getLista('medio'),
            ['prompt' => 'Seleccione un medio', 'id' => 'medio_id', 'class' => 'form-select']
        )->label('📢 Medio') ?>
        <div class="btn-group botones-catalogo mt-2" role="group">
            <button type="button" class="btn btn-sm btn-outline-warning abrir-modal" data-tipo="medio">➕</button>
            <button type="button" class="btn btn-sm btn-outline-info editar-catalogo" data-tipo="medio">✏️</button>
        </div>
    </div>

    <div class="form-group">
        <?= $form->field($model, 'telefono')->textInput([
            'maxlength' => true,
            'placeholder' => 'Ej: 55-1234-5678',
            'type' => 'tel'
        ])->label('📱 Teléfono') ?>
    </div>

    <div class="form-group">
        <?= $form->field($model, 'campaña_id')->dropDownList(
            Catalogos::getLista('campaña'),
            ['prompt' => 'Seleccione una campaña', 'id' => 'campaña_id', 'class' => 'form-select']
        )->label('🎯 Campaña') ?>
        <div class="btn-group botones-catalogo mt-2" role="group">
            <button type="button" class="btn btn-sm btn-outline-warning abrir-modal" data-tipo="campaña">➕</button>
            <button type="button" class="btn btn-sm btn-outline-info editar-catalogo" data-tipo="campaña">✏️</button>
        </div>
    </div>
</div>

<!-- Asesor, Unidades y Precio Extra -->
<div class="row row-3">
    <div class="form-group">
        <?= $form->field($model, 'asesor_id')->dropDownList(
            Catalogos::getLista('asesor'),
            ['prompt' => 'Seleccione un asesor', 'id' => 'asesor_id', 'class' => 'form-select']
        )->label('👤 Asesor Responsable') ?>
        <div class="btn-group botones-catalogo mt-2" role="group">
            <button type="button" class="btn btn-sm btn-outline-warning abrir-modal" data-tipo="asesor">➕</button>
            <button type="button" class="btn btn-sm btn-outline-info editar-catalogo" data-tipo="asesor">✏️</button>
        </div>
    </div>

    <div class="form-group">
        <?= $form->field($model, 'unidades')->input('number', [
            'min' => 1,
            'value' => $model->isNewRecord ? 1 : $model->unidades
        ])->label('📦 Cantidad de Unidades') ?>
    </div>

    <div class="form-group">
        <?= $form->field($model, 'extra_precio')->input('number', [
            'step' => '0.01',
            'min' => '0',
            'placeholder' => '0.00'
        ])->label('💵 Precio Extra') ?>
    </div>
</div>

<!-- Precio Total, Anticipo y Restante -->
<div class="row row-3">
    <div class="form-group">
        <?= $form->field($model, 'precio_total')->input('number', [
            'step' => '0.01',
            'min' => '0',
            'id' => 'precio_total',
            'placeholder' => '0.00'
        ])->label('💸 Precio Total') ?>
    </div>

    <div class="form-group">
        <?= $form->field($model, 'anticipo')->input('number', [
            'step' => '0.01',
            'min' => '0',
            'id' => 'anticipo',
            'placeholder' => '0.00'
        ])->label('💰 Anticipo') ?>
    </div>

    <div class="form-group">
        <?= $form->field($model, 'restante')->input('number', [
            'step' => '0.01',
            'id' => 'restante',
            'readonly' => true,
            'class' => 'form-control bg-light'
        ])->label('💰 Restante') ?>
    </div>
</div>

<!-- Fecha de compra -->
<div class="row row-3">
    <div class="form-group">
        <?= $form->field($model, 'fecha_compra')->input('date', [
            'value' => $model->isNewRecord ? date('Y-m-d') : $model->fecha_compra
        ])->label('📅 Fecha de Compra') ?>
    </div>
</div>

<!-- Adicionales y Extras -->
<div class="row row-3">
    <div class="form-group">
        <?= $form->field($model, 'adicionales_ids[]')->listBox(
            Catalogos::getLista('adicionales'),
            [
                'multiple' => true,
                'id' => 'adicionales_ids',
                'class' => 'form-select',
                'size' => 4,
                'options' => array_fill_keys((array) $model->getAdicionalesIds(), ['selected' => true])
            ]
        )->label('🔧 Adicionales') ?>
        <div class="btn-group botones-catalogo mt-2" role="group">
            <button type="button" class="btn btn-sm btn-outline-warning abrir-modal" data-tipo="adicionales">➕</button>
            <button type="button" class="btn btn-sm btn-outline-info editar-catalogo" data-tipo="adicionales">✏️</button>
        </div>
    </div>

    <div class="form-group">
        <?= $form->field($model, 'extras_ids[]')->listBox(
            Catalogos::getLista('extras'),
            [
                'multiple' => true,
                'id' => 'extras_ids',
                'class' => 'form-select',
                'size' => 4,
                'options' => array_fill_keys((array) $model->getExtrasIds(), ['selected' => true])
            ]
        )->label('✨ Extras') ?>
        <div class="btn-group botones-catalogo mt-2" role="group">
            <button type="button" class="btn btn-sm btn-outline-warning abrir-modal" data-tipo="extras">➕</button>
            <button type="button" class="btn btn-sm btn-outline-info editar-catalogo" data-tipo="extras">✏️</button>
        </div>
    </div>
</div>
      
      <!-- Fecha de entrega (oculta por defecto) -->
        <div class="row" id="ubicacion-container" style="display:none;">
        <div class="form-group col-12 col-md-4">
         <?= $form->field($model, 'ubicacion')->label('Ubicacion') ?>
           </div>
        </div>

<?php ActiveForm::end(); ?>

</div>

<!-- Modales para todos los catálogos -->
<?php foreach($catalogos as $cat): ?>
<div class="modal fade" id="modal_<?= $cat ?>" tabindex="-1" aria-labelledby="modal_<?= $cat ?>Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title text-dark" id="modal_<?= $cat ?>Label">
                    ➕ Agregar <?= ucfirst(str_replace('_',' ',$cat)) ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label for="nueva_<?= $cat ?>" class="form-label fw-bold">
                    Nombre del <?= str_replace('_',' ',$cat) ?>:
                </label>
                <input type="text" 
                       id="nueva_<?= $cat ?>" 
                       class="form-control" 
                       placeholder="Escriba el nombre del <?= str_replace('_',' ',$cat) ?>"
                       maxlength="100">
                <input type="hidden" id="editar_id_<?= $cat ?>" value="">
                <div id="<?= $cat ?>_error" class="text-danger mt-2"></div>
                <div id="<?= $cat ?>_success" class="text-success mt-2"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    ❌ Cancelar
                </button>
                <button type="button" class="btn btn-warning guardar_catalogo" data-tipo="<?= $cat ?>">
                    💾 Guardar
                </button>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>


<?php
$this->registerJs("
    //  Abrir modal dinámicamente
    $(document).on('click', '.abrir-modal', function() {
        var tipo = $(this).data('tipo');
        var modalEl = document.getElementById('modal_' + tipo);

        if(modalEl) {
            var modal = new bootstrap.Modal(modalEl);
            modal.show();
        } else {
            console.error('No se encontró el modal:', tipo);
            alert('Error: el modal no existe.');
        }
    });

    // Guardar catálogo (crear o editar)
    $(document).on('click', '.guardar_catalogo', function(e) {
        e.preventDefault();

        var tipo = $(this).data('tipo');
        var nombre = $('#nueva_' + tipo).val().trim();
        var editarId = $('#editar_id_' + tipo).val();
        var esEdicion = editarId !== '';

        if (!nombre) {
            alert('Debe escribir un nombre válido.');
            return;
        }

        var button = $(this);
        button.prop('disabled', true).text('Guardando...');

        var url = esEdicion ? 
            '" . \yii\helpers\Url::to(['/catalogos/update-ajax']) . "' : 
            '" . \yii\helpers\Url::to(['/catalogos/create-ajax']) . "';

        var data = {
            nombre: nombre, 
            tipo: tipo,
            '_csrf': $('meta[name=csrf-token]').attr('content')
        };

        if(esEdicion) data.id = editarId;

        $.post(url, data)
        .done(function(response) {
            if(response.success) {
                if(esEdicion) {
                    $('#' + tipo + '_id option[value=\"' + editarId + '\"]').text(response.nombre);
                } else {
                    $('#' + tipo + '_id').append('<option value=\"' + response.id + '\" selected>' + response.nombre + '</option>');
                    if (tipo === 'adicionales') $('#adicionales_ids').append('<option value=\"' + response.id + '\">' + response.nombre + '</option>');
                    if (tipo === 'extras') $('#extras_ids').append('<option value=\"' + response.id + '\">' + response.nombre + '</option>');
                }

                var modalEl = document.getElementById('modal_' + tipo);
                var modal = bootstrap.Modal.getInstance(modalEl);
                if(modal) modal.hide();

                alert('Elemento guardado correctamente');
            } else {
                alert('Error: ' + (response.error || 'Error desconocido'));
            }
        })
        .fail(function(xhr) {
            console.error('Error AJAX:', xhr);
            alert('Error de conexión');
        })
        .always(function() {
            button.prop('disabled', false).text('💾 Guardar');
        });
    });

    // 3️⃣ Editar catálogo: cargar datos en modal
    $(document).on('click', '.editar-catalogo', function() {
        var tipo = $(this).data('tipo');
        var selectElement = $('#' + tipo + '_id');
        var selectedOption = selectElement.find('option:selected');

        if(!selectedOption.val()) {
            alert('Seleccione un elemento para editar.');
            return;
        }

        $('#nueva_' + tipo).val(selectedOption.text());
        $('#editar_id_' + tipo).val(selectedOption.val());

        var modalEl = document.getElementById('modal_' + tipo);
        var modal = new bootstrap.Modal(modalEl);
        modal.show();
    });
", \yii\web\View::POS_READY);

$this->registerJs("
    function toggleFechaEntrega() {
        let selectedText = $('#entrega_id option:selected').text().trim();
        if (selectedText.toLowerCase() === 'urgente') {
            $('#fecha-entrega-container').slideDown();
        } else {
            $('#fecha-entrega-container').slideUp();
            $('#fechaentrega').val('');
        }
    }

    // Ejecutar cuando se seleccione instalacion
    $('#entrega_id').on('change', toggleFechaEntrega);

    // Ejecutar al cargar la página, por si ya hay un valor seleccionado
    toggleFechaEntrega();

    function toggleUbicacion() {
        let selectedText = $('#entrega_id option:selected').text().trim();
        if (selectedText.toLowerCase() === 'urgente') {
            $('#fecha-entrega-container').slideDown();
        } else {
            $('#fecha-entrega-container').slideUp();
            $('#fechaentrega').val('');
        }
    }

    // Ejecutar cuando se seleccione instalacion
    $('#entrega_id').on('change', toggleFechaEntrega);

    // Ejecutar al cargar la página, por si ya hay un valor seleccionado
    toggleFechaEntrega();
");

// JS para calcular automáticamente el restante
$this->registerJs("
    function calcularRestante() {
        var precio = parseFloat($('#precio_total').val()) || 0;
        var anticipo = parseFloat($('#anticipo').val()) || 0;
        var restante = precio - anticipo;
        $('#restante').val(restante >= 0 ? restante.toFixed(2) : '0.00');
        
        if (restante < 0) {
            $('#restante').addClass('border-danger');
        } else {
            $('#restante').removeClass('border-danger');
        }
    }
    
    $('#precio_total, #anticipo').on('input', calcularRestante);
    
    $(document).ready(function() {
        calcularRestante();
    });
");
?>

