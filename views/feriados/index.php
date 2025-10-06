<?php
use yii\helpers\Url;
use yii\helpers\Html;

$this->title = 'Calendario de Feriados';

// Cargar CSS y JS de FullCalendar 5
$this->registerCssFile('https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css');
$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);
$this->registerJsFile('https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/es.js', ['depends' => [\yii\web\JqueryAsset::class]]);
?>

<div class="feriados-container">
    <div class="calendar-wrapper">
        <div class="calendar-header">
            <h1><i class="fas fa-calendar-alt"></i> <?= Html::encode($this->title) ?></h1>
            <p class="calendar-subtitle">Gestiona los días feriados de manera fácil e intuitiva</p>
        </div>

        <div class="calendar-controls">
            <div class="legend">
                <div class="legend-item">
                    <div class="legend-color" style="background: #ffd700;"></div>
                    
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #ffaa00;"></div>
                    
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #fff200;"></div>
                    
                </div>
                <div class="legend-item">
                    <i class="fas fa-mouse" style="color: #ffd700;"></i>
                    <span>Clic para agregar</span>
                </div>
                <div class="legend-item">
                    <i class="fas fa-mouse-pointer" style="color: #ffd700;"></i>
                    <span>Clic derecho para eliminar</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #ffd700;"></div>
                    
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #ffaa00;"></div>
                    
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #fff200;"></div>
                    
                </div>
            </div>
        </div>

        <!-- Calendario -->
        <div id="calendar"></div>
    </div>
</div>

<!-- Modal para agregar feriado -->
<div id="feriadoModal" class="custom-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">Agregar Feriado</h2>
            <button class="close-btn" type="button">&times;</button>
        </div>
        <div class="modal-body">
            <form id="feriadoForm">
                <div class="form-group">
                    <label for="fechaFeriado" class="form-label">Fecha:</label>
                    <input type="text" id="fechaFeriado" class="form-input" readonly>
                </div>
                <div class="form-group">
                    <label for="descripcionFeriado" class="form-label">Descripción:</label>
                    <input type="text" id="descripcionFeriado" class="form-input" placeholder="Ingrese la descripción del feriado" required>
                </div>
            </form>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn btn-outline-custom btn-cancelar">Cancelar</button>
            <button type="button" class="btn btn-custom btn-agregar">Agregar Feriado</button>
        </div>
    </div>
</div>

<?php
$eventosUrl = Url::to(['feriados/eventos']);
$agregarUrl = Url::to(['feriados/agregar']);
$eliminarUrl = Url::to(['feriados/eliminar']);

$js = <<<JS
jQuery(document).ready(function($) {
    var calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'es',
        height: 600,
        editable: true,
        events: '{$eventosUrl}',
        headerToolbar: {
            left: 'prev,next',
            center: 'title',
            right: ''
        },

        // Agregar feriado con clic
        dateClick: function(info) {
            $('#fechaFeriado').val(info.dateStr);
            $('#descripcionFeriado').val('');
            $('#feriadoModal').show();
            $('#descripcionFeriado').focus();
        },

        // Eliminar feriado con clic derecho
        eventDidMount: function(info) {
            info.el.addEventListener('contextmenu', function(e) {
                e.preventDefault();
                if(confirm('¿Eliminar feriado "' + info.event.title + '"?')) {
                    $.post('{$eliminarUrl}', { id: info.event.id }, function(data) {
                        if(data.status == 'success') {
                            info.event.remove();
                        } else {
                            alert('Error al eliminar feriado');
                        }
                    });
                }
            });
        }
    });

    calendar.render();

    // Cerrar modal
    $('.close-btn, .btn-cancelar').click(function() {
        $('#feriadoModal').hide();
    });

    // Agregar feriado desde modal
    $('.btn-agregar').click(function() {
        var descripcion = $('#descripcionFeriado').val().trim();
        if (descripcion) {
            var fecha = $('#fechaFeriado').val();
            $.post('{$agregarUrl}', { fecha: fecha, descripcion: descripcion }, function(data) {
                if(data.status == 'success') {
                    calendar.addEvent(data.feriado);
                    $('#feriadoModal').hide();
                } else {
                    alert('Error al agregar feriado');
                }
            });
        } else {
            alert('Por favor ingrese una descripción');
        }
    });

    // Actualizar calendario
    // $('#btn-actualizar').click(function() {
    //     calendar.refetchEvents();
    // });

    // Cerrar modal con ESC
    $(document).keydown(function(e) {
        if (e.key === "Escape") {
            $('#feriadoModal').hide();
        }
    });

    // Enviar formulario con Enter
    $('#descripcionFeriado').keydown(function(e) {
        if (e.key === "Enter") {
            $('.btn-agregar').click();
        }
    });
});
JS;

$this->registerJs($js);
?>

<style>
.feriados-container {
    background: #f8f9fa;
    min-height: 100vh;
    padding: 20px 10px; /* menos padding lateral */
    position: relative;
    width: 100%;
    box-sizing: border-box;
}

.calendar-wrapper {
    background: #ffffff;
    border: 2px solid #ffd700;
    border-radius: 15px;
    box-shadow: 0 20px 40px rgba(255, 215, 0, 0.2);
    padding: 20px;        /* reducir padding para más espacio horizontal */
    margin: 20px auto;
    width: 100%;          /* ocupar todo el ancho del contenedor */
    max-width: 1800px;    /* ancho máximo grande */
    box-sizing: border-box;
}

#calendar {
    width: 100% !important;  /* ocupar todo el ancho del wrapper */
    margin: 0 auto;
}


.calendar-header {
    text-align: center;
    margin-bottom: 30px;
    color: #333333;
}

.calendar-header h1 {
    font-size: 2.8rem;
    font-weight: 700;
    margin-bottom: 15px;
    color: #333333;
    text-shadow: 0 2px 4px rgba(255, 215, 0, 0.3);
    letter-spacing: -1px;
}

.calendar-subtitle {
    color: #6c757d;
    font-size: 1.1rem;
    margin-bottom: 25px;
    font-weight: 400;
}

.calendar-controls {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 20px;
}

.btn-custom {
    background: #ffd700;
    border: 1px solid #ffd700;
    color: #000000;
    padding: 10px 20px;
    border-radius: 6px;
    font-weight: 500;
    font-size: 0.9rem;
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(255, 215, 0, 0.2);
}

.btn-custom:hover {
    background: #e6c200;
    border-color: #e6c200;
    color: #000000;
}

.btn-outline-custom {
    background: transparent;
    border: 1px solid #6c757d;
    color: #6c757d;
    padding: 10px 20px;
    border-radius: 6px;
    font-weight: 500;
    font-size: 0.9rem;
    transition: all 0.2s ease;
}

.btn-outline-custom:hover {
    background: #6c757d;
    color: #ffffff;
}

.legend {
    display: flex;
    gap: 25px;
    align-items: center;
    flex-wrap: wrap;
    background: rgba(255, 255, 255, 0.8);
    padding: 15px 20px;
    border-radius: 20px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #495057;
    font-size: 0.95rem;
    font-weight: 500;
}

.legend-color {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    border: 2px solid rgba(255,255,255,0.8);
}

#calendar {
    max-width: 100%;
    height: 600px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 
        0 20px 40px rgba(0,0,0,0.15),
        0 0 0 1px rgba(255,255,255,0.1) inset;
}

/* Estilos personalizados para FullCalendar */
.fc {
    background: linear-gradient(145deg, #f8f9ff 0%, #ffffff 100%);
}

.fc-theme-standard .fc-view-harness {
    border-radius: 15px;
    overflow: hidden;
    box-shadow: none;
    background: transparent;
}

.fc-toolbar {
    margin-bottom: 25px !important;
    padding: 20px 25px !important;
    background: #f8f9fa;
    border-radius: 10px;
    border: 1px solid #ffd700;
}

.fc-toolbar-title {
    font-size: 2rem !important;
    font-weight: 700 !important;
    color: #333333 !important;
    text-shadow: 0 2px 4px rgba(255, 215, 0, 0.3);
}

.fc-button-primary {
    background: #ffd700 !important;
    border: 2px solid #ffd700 !important;
    border-radius: 6px !important;
    font-weight: 600 !important;
    padding: 8px 16px !important;
    color: #000000 !important;
    transition: all 0.3s ease !important;
}

.fc-button-primary:hover {
    background: #333333 !important;
    border-color: #ffd700 !important;
    color: #ffd700 !important;
}

.fc-button-primary:disabled {
    opacity: 0.5 !important;
    background: #e9ecef !important;
    border-color: #dee2e6 !important;
    color: #6c757d !important;
}

.fc-daygrid-day {
    transition: all 0.3s ease !important;
    border: 1px solid #dee2e6 !important;
    background: #ffffff !important;
}

.fc-daygrid-day:hover {
    background: #fff9e6 !important;
    cursor: pointer;
    border-color: #ffd700 !important;
}

.fc-day-today {
    background: #fffbf0 !important;
    border: 2px solid #ffd700 !important;
}

.fc-daygrid-day-number {
    font-weight: 600 !important;
    color: #495057 !important;
    font-size: 0.95rem !important;
    text-decoration: none !important;
    cursor: default !important;
}

.fc-daygrid-day-number:hover {
    text-decoration: none !important;
    color: #333333 !important;
}

.fc-day-today .fc-daygrid-day-number {
    color: #333333 !important;
    font-weight: 700 !important;
}

.fc-event {
    border-radius: 6px !important;
    border: 2px solid #ffd700 !important;
    background: #ffd700 !important;
    cursor: pointer !important;
    transition: all 0.3s ease !important;
    margin: 2px !important;
}

.fc-event:hover {
    transform: scale(1.05) !important;
    box-shadow: 0 4px 15px rgba(255, 215, 0, 0.5) !important;
}

.fc-event-title {
    font-weight: 600 !important;
    padding: 4px 8px !important;
    font-size: 0.85rem !important;
    color: #000000 !important;
}

.fc-daygrid-event-dot {
    display: none !important;
}

/* Headers de los días */
.fc-col-header {
    background: #f8f9fa !important;
    border-bottom: 2px solid #ffd700 !important;
}

.fc-col-header-cell {
    border: 1px solid #dee2e6 !important;
}

.fc-col-header-cell-cushion {
    color: #333333 !important;
    font-weight: 700 !important;
    font-size: 0.9rem !important;
    padding: 12px 8px !important;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    text-decoration: none !important;
}

.fc-col-header-cell-cushion:hover {
    text-decoration: none !important;
    color: #333333 !important;
}

/* Estilos para el modal personalizado */
.custom-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9999;
}

.modal-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: #ffffff;
    border: 1px solid #e9ecef;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    max-width: 450px;
    width: 90%;
    max-height: 85vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e9ecef;
}

.modal-title {
    font-size: 1.4rem;
    font-weight: 600;
    color: #495057;
    margin: 0;
}

.close-btn {
    background: transparent;
    border: none;
    font-size: 1.5rem;
    color: #6c757d;
    cursor: pointer;
    width: 32px;
    height: 32px;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.close-btn:hover {
    background: #f8f9fa;
    color: #495057;
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #495057;
    font-size: 0.9rem;
}

.form-input {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #ced4da;
    border-radius: 6px;
    font-size: 0.95rem;
    transition: all 0.2s ease;
    box-sizing: border-box;
    background: #ffffff;
    color: #495057;
    font-weight: 400;
}

.form-input:focus {
    outline: none;
    border-color: #ffd700;
    box-shadow: 0 0 0 0.2rem rgba(255, 215, 0, 0.25);
}

.form-input[readonly] {
    background-color: #f8f9fa;
    color: #6c757d;
}

.modal-body {
    margin-bottom: 20px;
}

.modal-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 25px;
}

@media (max-width: 768px) {
    .calendar-wrapper {
        margin: 10px;
        padding: 20px 15px;
        border-radius: 15px;
    }
    
    .calendar-header h1 {
        font-size: 2rem;
    }
    
    .calendar-controls {
        flex-direction: column;
        align-items: stretch;
    }
    
    .legend {
        justify-content: center;
    }
    
    #calendar {
        height: 500px;
    }
    
    .modal-content {
        padding: 20px;
        width: 95%;
    }
}
</style>

