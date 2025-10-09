<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Tablas Retorno';
$this->params['breadcrumbs'][] = $this->title;

$money = fn($n) => '$' . number_format((float)$n, 2);
$asesoresById   = $asesoresById   ?? [];
$campaniasById  = $campaniasById  ?? [];
$campaniasCatalog = $campaniasCatalog ?? [];

$this->registerCss(<<<CSS
.retorno-bg { background:#fff; }
.card-soft { background:#fff; border:1px solid #e5e7eb; border-radius:12px; box-shadow:0 8px 20px rgba(2,6,23,.04); }
.section-title { font-weight:800; color:#0f172a; }
.table thead th { background:#f8fafc; position:sticky; top:0; z-index:1; }
.badge-tag { background:#eef2ff; color:#3730a3; font-weight:600; }
.details-table { background:#fcfcfd; }
CSS);

// Exponer URLs y mapas para AJAX
$this->registerJs(
    'window.retornoExtraUrl = ' . json_encode(Url::to(['retorno/extra-totales'])) . ';
     window.retornoExtraMatrizUrl = ' . json_encode(Url::to(['retorno/extra-matriz'])) . ';
     window.retornoBreakdownUrl = ' . json_encode(Url::to(['retorno/breakdown-asesor'])) . ';
     window.retornoMonth = ' . json_encode($month) . ';
     window.asesoresById = ' . json_encode($asesoresById, JSON_UNESCAPED_UNICODE) . ';
     window.campaniasById = ' . json_encode($campaniasById, JSON_UNESCAPED_UNICODE) . ';',
    \yii\web\View::POS_HEAD
);
?>

<div class="retorno-bg container py-3">
  <h1 class="h4 mb-3"><?= Html::encode($this->title) ?> · <?= Html::encode($month) ?></h1>

  <!-- Fila 1: Retorno Mensual y Retorno Extra (selección) -->
  <div class="row g-3">
    <div class="col-12 col-xl-6">
      <div class="card-soft p-3">
        <div class="section-title mb-2">Retorno Mensual</div>
        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead>
              <tr>
                <th>Nombre del asesor</th>
                <th class="text-end">Total $$ ventas que generó mensual</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($asesoresById)): ?>
                <tr><td colspan="2" class="text-muted">Sin asesores</td></tr>
              <?php else: foreach ($asesoresById as $id=>$nombre): ?>
                <tr>
                  <td><?= Html::encode($nombre) ?></td>
                  <td class="text-end"><?= $money($mensualPorAsesor[$id] ?? 0) ?></td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-6">
      <div class="card-soft p-3">
        <div class="section-title mb-2">Retorno Extra (selección de campañas)</div>
        <div class="mb-2 small text-muted">Selecciona una o varias campañas de catálogos; luego “Aplicar”.</div>
        <form id="extra-form" class="mb-2">
          <div class="row g-2">
            <div class="col-12">
              <select id="extra-campanas" name="campanas[]" class="form-select" multiple size="6">
                <?php foreach ($campaniasCatalog as $c): ?>
                  <option value="<?= (int)$c['id'] ?>"><?= Html::encode($c['nombre']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12 d-flex gap-2">
              <button type="button" id="extra-aplicar" class="btn btn-primary btn-sm">Aplicar</button>
              <button type="button" id="extra-limpiar" class="btn btn-light border btn-sm">Limpiar</button>
            </div>
          </div>
        </form>

        <div class="small text-muted mb-1">Total global seleccionado: <span id="extra-grand-total">$0.00</span></div>
      </div>
    </div>
  </div>

  <!-- Fila 2: Retorno Extra dividido POR CAMPAÑA y POR ASESOR -->
  <div class="row g-3 mt-1">
    <div class="col-12 col-xl-6">
      <div class="card-soft p-3">
        <div class="section-title mb-2">Retorno Extra por Campaña</div>
        <div class="table-responsive">
          <table class="table table-sm align-middle" id="extra-por-campania">
            <thead>
              <tr>
                <th>Campaña</th>
                <th class="text-end">Total</th>
              </tr>
            </thead>
            <tbody>
              <tr><td colspan="2" class="text-muted">Selecciona campañas y presiona Aplicar</td></tr>
            </tbody>
          </table>
        </div>
        <div class="small text-muted">Tip: Haz clic en una fila para ver el desglose por asesor.</div>
      </div>
    </div>

    <div class="col-12 col-xl-6">
      <div class="card-soft p-3">
        <div class="section-title mb-2">Retorno Extra por Asesor</div>
        <div class="table-responsive">
          <table class="table table-sm align-middle" id="extra-por-asesor">
            <thead>
              <tr>
                <th>Nombre del asesor</th>
                <th class="text-end">Total</th>
              </tr>
            </thead>
            <tbody>
              <tr><td colspan="2" class="text-muted">Selecciona campañas y presiona Aplicar</td></tr>
            </tbody>
          </table>
        </div>
        <div class="small text-muted">Tip: Haz clic en una fila para ver el desglose por campaña.</div>
      </div>
    </div>
  </div>

  <!-- Fila 3: Orgánico + Retornos individuales -->
  <div class="row g-3 mt-1">
    <div class="col-12 col-xl-6">
      <div class="card-soft p-3">
        <div class="section-title mb-2">Retorno Orgánico</div>
        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead>
              <tr>
                <th>Nombre del asesor</th>
                <th class="text-end">Total $$ ventas con campaña orgánico</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($asesoresById)): ?>
                <tr><td colspan="2" class="text-muted">Sin asesores</td></tr>
              <?php else: foreach ($asesoresById as $id=>$nombre): ?>
                <tr>
                  <td><?= Html::encode($nombre) ?></td>
                  <td class="text-end"><?= $money($organicoPorAsesor[$id] ?? 0) ?></td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-6">
      <div class="card-soft p-3">
        <div class="section-title mb-2">Retorno Individuales (desglose por asesor)</div>
        <div class="accordion" id="acc-retornos">
          <?php $i=0; foreach ($asesoresById as $id=>$nombre): $i++; $cid="acc-asesor-$id"; ?>
          <div class="accordion-item">
            <h2 class="accordion-header" id="h-<?= $cid ?>">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#c-<?= $cid ?>">
                Retorno Mensual de <?= Html::encode($nombre) ?>
              </button>
            </h2>
            <div id="c-<?= $cid ?>" class="accordion-collapse collapse" data-bs-parent="#acc-retornos">
              <div class="accordion-body">
                <table class="table table-sm align-middle mb-0">
                  <thead><tr><th>Campañas</th><th class="text-end">Total</th></tr></thead>
                  <tbody id="bd-rows-<?= (int)$id ?>">
                    <tr><td colspan="2" class="text-muted">Cargando…</td></tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Fila 4: Recompra, Desconocido, Página Web -->
  <div class="row g-3 mt-1">
    <div class="col-12 col-xl-4">
      <div class="card-soft p-3">
        <div class="section-title mb-2">Retorno Recompra</div>
        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead><tr><th>Nombre del asesor</th><th class="text-end">Total $$ campaña Recompra</th></tr></thead>
            <tbody>
              <?php foreach ($asesoresById as $id=>$nombre): ?>
                <tr>
                  <td><?= Html::encode($nombre) ?></td>
                  <td class="text-end"><?= $money($recompraPorAsesor[$id] ?? 0) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-4">
      <div class="card-soft p-3">
        <div class="section-title mb-2">Retorno Desconocido</div>
        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead><tr><th>Nombre del asesor</th><th class="text-end">Total $$ Desconocido</th></tr></thead>
            <tbody>
              <?php foreach ($asesoresById as $id=>$nombre): ?>
                <tr>
                  <td><?= Html::encode($nombre) ?></td>
                  <td class="text-end"><?= $money($desconocidoPorAsesor[$id] ?? 0) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-4">
      <div class="card-soft p-3">
        <div class="section-title mb-2">Retorno Página Web</div>
        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead><tr><th>Nombre del asesor</th><th class="text-end">Total $$ Página Web</th></tr></thead>
            <tbody>
              <?php foreach ($asesoresById as $id=>$nombre): ?>
                <tr>
                  <td><?= Html::encode($nombre) ?></td>
                  <td class="text-end"><?= $money($webPorAsesor[$id] ?? 0) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
// JS: Retorno Extra (matriz campaña × asesor) + Breakdown por asesor al abrir acordeón
$this->registerJs(<<<'JS'
(function(){
  function fmt(n){ return '$' + Number(n||0).toFixed(2); }

  // Construir tabla por campaña (conplegable por asesor)
  function renderByCampaign(container, data){
    const tbody = $(container).find('tbody');
    if (!data || Object.keys(data).length === 0) {
      tbody.html('<tr><td colspan="2" class="text-muted">Sin resultados</td></tr>');
      return;
    }
    const rows = [];
    let idx = 0;
    for (const [campKey, obj] of Object.entries(data)) {
      const rid = 'camp-det-' + (++idx);
      rows.push(
        `<tr class="camp-row" data-bs-toggle="collapse" data-bs-target="#${rid}" style="cursor:pointer;">
           <td>${obj.name}</td>
           <td class="text-end">${fmt(obj.total)}</td>
         </tr>`
      );
      // detalle por asesor
      const items = (obj.items||[]).map(it =>
        `<tr><td>${it.name}</td><td class="text-end">${fmt(it.total)}</td></tr>`
      ).join('');
      rows.push(
        `<tr class="collapse details-table" id="${rid}">
           <td colspan="2">
             <div class="table-responsive">
               <table class="table table-sm mb-0">
                 <thead><tr><th>Asesor</th><th class="text-end">Total</th></tr></thead>
                 <tbody>${items || '<tr><td colspan="2" class="text-muted">Sin ventas</td></tr>'}</tbody>
               </table>
             </div>
           </td>
         </tr>`
      );
    }
    tbody.html(rows.join(''));
  }

  // Construir tabla por asesor (colapsable por campaña)
  function renderByAdvisor(container, data){
    const tbody = $(container).find('tbody');
    if (!data || Object.keys(data).length === 0) {
      tbody.html('<tr><td colspan="2" class="text-muted">Sin resultados</td></tr>');
      return;
    }
    const rows = [];
    let idx = 0;
    for (const [aid, obj] of Object.entries(data)) {
      const rid = 'ase-det-' + (++idx);
      rows.push(
        `<tr class="ase-row" data-bs-toggle="collapse" data-bs-target="#${rid}" style="cursor:pointer;">
           <td>${obj.name}</td>
           <td class="text-end">${fmt(obj.total)}</td>
         </tr>`
      );
      const items = (obj.items||[]).map(it =>
        `<tr><td>${it.name}</td><td class="text-end">${fmt(it.total)}</td></tr>`
      ).join('');
      rows.push(
        `<tr class="collapse details-table" id="${rid}">
           <td colspan="2">
             <div class="table-responsive">
               <table class="table table-sm mb-0">
                 <thead><tr><th>Campaña</th><th class="text-end">Total</th></tr></thead>
                 <tbody>${items || '<tr><td colspan="2" class="text-muted">Sin ventas</td></tr>'}</tbody>
               </table>
             </div>
           </td>
         </tr>`
      );
    }
    tbody.html(rows.join(''));
  }

  // Botones
  $('#extra-aplicar').on('click', function(){
    const sel = $('#extra-campanas').val() || [];
    if (sel.length === 0) {
      $('#extra-por-campania tbody').html('<tr><td colspan="2" class="text-muted">Selecciona campañas</td></tr>');
      $('#extra-por-asesor tbody').html('<tr><td colspan="2" class="text-muted">Selecciona campañas</td></tr>');
      $('#extra-grand-total').text(fmt(0));
      return;
    }

    // Endpoint matriz campaña × asesor
    $.post(window.retornoExtraMatrizUrl, {
      month: window.retornoMonth,
      campanas: sel,
      _csrf: yii.getCsrfToken()
    }, function(res){
      if (!res || !res.success) {
        $('#extra-por-campania tbody').html('<tr><td colspan="2" class="text-danger">Error al calcular</td></tr>');
        $('#extra-por-asesor tbody').html('<tr><td colspan="2" class="text-danger">Error al calcular</td></tr>');
        $('#extra-grand-total').text(fmt(0));
        return;
      }
      renderByCampaign('#extra-por-campania', res.byCampaign || {});
      renderByAdvisor('#extra-por-asesor',   res.byAdvisor  || {});
      $('#extra-grand-total').text(fmt(res.grandTotal || 0));
    }, 'json').fail(function(){
      $('#extra-por-campania tbody').html('<tr><td colspan="2" class="text-danger">Error de conexión</td></tr>');
      $('#extra-por-asesor tbody').html('<tr><td colspan="2" class="text-danger">Error de conexión</td></tr>');
      $('#extra-grand-total').text(fmt(0));
    });
  });

  $('#extra-limpiar').on('click', function(){
    $('#extra-campanas').val([]);
    $('#extra-por-campania tbody').html('<tr><td colspan="2" class="text-muted">Selecciona campañas y presiona Aplicar</td></tr>');
    $('#extra-por-asesor tbody').html('<tr><td colspan="2" class="text-muted">Selecciona campañas y presiona Aplicar</td></tr>');
    $('#extra-grand-total').text(fmt(0));
  });

  // Retornos individuales: cargar breakdown al abrir cada acordeón
  const month = window.retornoMonth;
  $('.accordion .accordion-button').on('click', function(){
    const target = $(this).attr('data-bs-target');
    if (!target) return;
    const aid = parseInt(String(target).split('-').pop(), 10);
    if (!aid) return;

    const tbody = $('#bd-rows-' + aid);
    if (!tbody.length || tbody.data('loaded') === '1') return;

    $.get(window.retornoBreakdownUrl, { asesorId: aid, month: month }, function(res){
      if (!res || !res.success) {
        tbody.html('<tr><td colspan="2" class="text-danger">No disponible</td></tr>');
        return;
      }
      const b = res.breakdown || {};
      const rows = [];
      rows.push(`<tr><td>Organico</td><td class="text-end">${fmt(b.organico)}</td></tr>`);
      rows.push(`<tr><td>Pagina Web</td><td class="text-end">${fmt(b.web)}</td></tr>`);
      rows.push(`<tr><td>Recompra</td><td class="text-end">${fmt(b.recompra)}</td></tr>`);
      rows.push(`<tr><td>Desconocido</td><td class="text-end">${fmt(b.desconocido)}</td></tr>`);
      tbody.html(rows.join(''));
      tbody.data('loaded','1');
    }, 'json').fail(function(){
      tbody.html('<tr><td colspan="2" class="text-danger">Error de conexión</td></tr>');
    });
  });
})();
JS);
?>