<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\bootstrap5\BootstrapAsset;
use yii\bootstrap5\BootstrapPluginAsset;
use app\models\Catalogos;

/** @var yii\web\View $this */
/** @var app\models\Diseno $model */
/** @var yii\widgets\ActiveForm $form */

BootstrapAsset::register($this);
BootstrapPluginAsset::register($this);
?>

<div class="diseno-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'venta_id')->textInput() ?>

    <?= $form->field($model, 'tipo_letrero_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'nombre_letrero')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'telefono')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'entrega_id')->textInput() ?>

    <?= $form->field($model, 'fecha_confirmacion')->textInput() ?>

    <?= $form->field($model, 'responsable_id')->textInput() ?>

    <?= $form->field($model, 'extra_precio')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'contacto_cliente_id')->textInput() ?>

    <?= $form->field($model, 'especificaciones_id')->textInput() ?>

    <?= $form->field($model, 'vectorizado_id')->textInput() ?>

    <?= $form->field($model, 'enviado_corte_id')->textInput() ?>

    <?= $form->field($model, 'avance')->textInput() ?>

    <div class="row row-2">
    <div class="form-group">
        <?= $form->field($model, 'estatus_id')->dropDownList(
            Catalogos::getLista('estatus'),
            [
                'prompt' => 'Seleccione una estatus',
                'id' => 'estatus_id',
                'class' => 'form-select'
            ]
        )->label('Estatus') ?>
        <div class="btn-group mt-2" role="group">
            <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modal_estatus">
                ➕ 
            </button>
            <button type="button" class="btn btn-sm btn-outline-info editar-catalogo" data-tipo="estatus">
                ✏️ 
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger eliminar-catalogo" data-tipo="estatus">
                🗑️ 
            </button>
        </div>
    </div>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
    <?php
$catalogos = ['estatus'];
foreach($catalogos as $cat):
?>
<div class="modal fade" id="modal_<?= $cat ?>" tabindex="-1" aria-labelledby="modal_<?= $cat ?>Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title text-dark" id="modal_<?= $cat ?>Label">
                    ➕ Agregar <?= ucfirst($cat) ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label for="nueva_<?= $cat ?>" class="form-label fw-bold">
                    Nombre del <?= $cat ?>:
                </label>
                <input type="text" 
                       id="nueva_<?= $cat ?>" 
                       class="form-control" 
                       placeholder="Escriba el nombre del <?= $cat ?>"
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
    // JS ÚNICO para manejar catálogos
$this->registerJs("
    // USAR SOLO EVENT DELEGATION para evitar conflictos
    $(document).on('click', '.guardar_catalogo', function(e) {
        e.preventDefault();
        console.log('Guardando catálogo...');
        
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
            console.log('Respuesta:', response);
            if(response.success) {
                if(esEdicion) {
                    $('#' + tipo + '_id option[value=\"' + editarId + '\"]').text(response.nombre);
                } else {
                    $('#' + tipo + '_id').append('<option value=\"' + response.id + '\" selected>' + response.nombre + '</option>');
                    if (tipo === 'adicionales') $('#adicionales_ids').append('<option value=\"' + response.id + '\">' + response.nombre + '</option>');
                    if (tipo === 'extras') $('#extras_ids').append('<option value=\"' + response.id + '\">' + response.nombre + '</option>');
                }
                
                var modal = bootstrap.Modal.getInstance(document.getElementById('modal_' + tipo));
                if (modal) modal.hide();
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
    
    // Resto de funciones sin cambios
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
        
        var modal = new bootstrap.Modal(document.getElementById('modal_' + tipo));
        modal.show();
    });
", \yii\web\View::POS_READY);?>

    

</div>
