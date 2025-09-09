console.log('jQuery version:', $.fn.jquery);
console.log('Initializing Diseno inline editing');

$(document).ready(function() {
    let editingCell = null;

    // Función principal: clic en editable-field
    $('.editable-field').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        if(editingCell!==null){
            console.log('Another cell is already being edited');
            return;
        }

        let cell = $(this);
        let fieldType = cell.data('field-type');
        let fieldName = cell.data('field-name');
        let recordId = cell.data('record-id');
        let currentValue = cell.data('current-value') || '';
        editingCell = cell;
        cell.addClass('editing');
        let originalContent = cell.html();
        let inputElement;

        if(fieldType==='text' || fieldType==='number' || fieldType==='date'){
            let type = fieldType==='text'? 'text': fieldType==='number'? 'number':'date';
            inputElement = '<input type="'+type+'" class="inline-input" value="'+currentValue+'">';
            createEditContainer(inputElement, originalContent);
        }
        else if(fieldType==='select'){
            loadSelectOptions(fieldName,currentValue,function(options){
                inputElement = '<select class="inline-select"><option value="">-- Seleccionar --</option>';
                options.forEach(function(opt){
                    let selected = opt.value==currentValue?'selected':'';
                    inputElement += '<option value="'+opt.value+'" '+selected+'>'+opt.text+'</option>';
                });
                inputElement+='</select>';
                createEditContainer(inputElement, originalContent);
            });
        }
        else if(fieldType==='multiselect'){
            let catalogType = cell.data('catalog-type');
            loadMultiSelectOptions(catalogType,currentValue,function(options){
                let selectedValues = currentValue? currentValue.toString().split(',').map(v=>v.trim()) : [];
                inputElement = '<div class="multiselect-container border border-primary rounded p-3" style="max-height:200px; overflow-y:auto;background-color:#f8f9fa;">';
                inputElement += '<div class="mb-2"><strong>Selecciona una o múltiples opciones:</strong></div>';
                options.forEach(function(opt){
                    let isChecked = selectedValues.includes(opt.value.toString());
                    let checkboxId = 'ms_'+catalogType+'_'+opt.value+'_'+Math.random().toString(36).substr(2,9);
                    inputElement += '<div class="form-check mb-2">';
                    inputElement += '<input class="form-check-input multiselect-checkbox" type="checkbox" value="'+opt.value+'" id="'+checkboxId+'" '+(isChecked?'checked':'')+'>';
                    inputElement += '<label class="form-check-label ms-2" for="'+checkboxId+'" style="cursor:pointer;font-weight:500;">'+opt.text+'</label>';
                    inputElement += '</div>';
                });
                inputElement += '</div>';
                createEditContainer(inputElement, originalContent);
            });
        }

        // Crear contenedor con botones
        function createEditContainer(inputEl, origContent){
            let editContainer = '<div class="edit-container">'+inputEl+
                                '<div class="save-cancel-buttons mt-2">'+
                                '<button type="button" class="btn btn-success btn-sm save-btn" data-record-id="'+recordId+'" data-field-name="'+fieldName+'">Guardar</button> '+
                                '<button type="button" class="btn btn-secondary btn-sm cancel-btn">Cancelar</button>'+
                                '</div></div>';
            cell.html(editContainer);
            cell.find('.inline-input, .inline-select').focus();
            bindEditEvents(cell,recordId,fieldName,origContent);
        }
    });

    // Eventos de guardar/cancelar y teclas
    function bindEditEvents(cell,recordId,fieldName,originalContent){
        cell.off('click.save').on('click.save','.save-btn',function(e){
            e.preventDefault(); e.stopPropagation();
            let newValue;
            if(cell.find('.multiselect-checkbox').length>0){
                let checkedValues = [];
                cell.find('.multiselect-checkbox:checked').each(function(){checkedValues.push($(this).val());});
                newValue = checkedValues.join(',');
            }else{
                let inputField = cell.find('.inline-input, .inline-select');
                newValue = inputField.val();
            }
            saveField(recordId,fieldName,newValue,cell,originalContent);
        });

        cell.off('click.cancel').on('click.cancel','.cancel-btn',function(e){
            e.preventDefault(); e.stopPropagation();
            cell.html(originalContent);
            cell.removeClass('editing');
            editingCell=null;
        });

        cell.off('keydown.edit').on('keydown.edit','.inline-input, .inline-select',function(e){
            if(e.which===13){ // Enter
                e.preventDefault();
                let newValue;
                if(cell.find('.multiselect-checkbox').length>0){
                    let checkedValues=[];
                    cell.find('.multiselect-checkbox:checked').each(function(){checkedValues.push($(this).val());});
                    newValue = checkedValues.join(',');
                }else{
                    newValue=$(this).val();
                }
                saveField(recordId,fieldName,newValue,cell,originalContent);
            }else if(e.which===27){ // Escape
                e.preventDefault();
                cell.html(originalContent);
                cell.removeClass('editing');
                editingCell=null;
            }
        });
    }

    // AJAX para select
    function loadSelectOptions(fieldName,currentValue,callback){
        $.ajax({
            url:'/index.php?r=diseno/get-select-options',
            type:'GET',
            data:{field:fieldName},
            success:function(response){ callback(response.options || []); },
            error:function(xhr,status,error){ alert('Error cargando opciones: '+error); editingCell=null;}
        });
    }

    // AJAX para multiselect
    function loadMultiSelectOptions(catalogType,currentValue,callback){
        $.ajax({
            url:'/index.php?r=diseno/get-multiselect-options',
            type:'GET',
            data:{type:catalogType},
            success:function(response){ callback(response.options || []); },
            error:function(xhr,status,error){ alert('Error cargando opciones: '+error); editingCell=null;}
        });
    }

    // Guardar campo
    function saveField(recordId,fieldName,newValue,cell,originalContent){
        let ajaxUrl = (fieldName==='extras'||fieldName==='adicionales')? '/index.php?r=diseno/update-many-to-many':'/index.php?r=diseno/update-field';
        $.ajax({
            url:ajaxUrl,
            type:'POST',
            data:{id:recordId, field:fieldName, value:newValue, _csrf: yii.getCsrfToken()},
            beforeSend:function(){ cell.find('.save-btn').prop('disabled',true).text('Guardando...'); },
            success:function(response){
                if(response.success){
                    cell.html(response.newContent || newValue);
                    cell.removeClass('editing');
                    editingCell=null;
                    showMessage('Campo actualizado correctamente','success');
                }else{
                    cell.html(originalContent);
                    cell.removeClass('editing');
                    editingCell=null;
                    showMessage(response.message || 'Error al actualizar','error');
                }
            },
            error:function(xhr,status,error){
                cell.html(originalContent);
                cell.removeClass('editing');
                editingCell=null;
                showMessage('Error de conexión: '+error,'error');
            }
        });
    }

    // Mensajes flash
    function showMessage(msg,type){
        let alertClass = type==='success'?'alert-success':'alert-danger';
        let alertHtml = '<div class="alert '+alertClass+' alert-dismissible fade show mt-2" role="alert">'+
                        msg+'<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        $('.diseno-index h1').after(alertHtml);
        setTimeout(function(){ $('.alert').fadeOut(); },3000);
    }
});
