<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Tablas Retorno';
$this->params['breadcrumbs'][] = $this->title;

$money = fn($n) => '$' . number_format((float)$n, 2);
$asesoresById = $asesoresById ?? [];

$this->registerCss(<<<CSS
.retorno-bg { background:#fff; }
.card-soft { background:#fff; border:1px solid #e5e7eb; border-radius:12px; box-shadow:0 8px 20px rgba(2,6,23,.04); }
.section-title { font-weight:800; color:#0f172a; }
.table thead th { background:#f8fafc; }
.badge-tag { background:#eef2ff; color:#3730a3; font-weight:600; }
CSS);

// Exponer URL para AJAX de Retorno Extra y Breakdown
$this->registerJs(
    'window.retornoExtraUrl = ' . json_encode(Url::to(['retorno/extra-totales'])) . ';
     window.retornoBreakdownUrl = ' . json_encode(Url::to(['retorno/breakdown-asesor'])) . ';
     window.retornoMonth = ' . json_encode($month) . ';',
    \yii\web\View::POS_HEAD
);
?>

<div class="retorno-bg container py-3">
  <h1 class="h4 mb-3"><?= Html::encode($this->title) ?> · <?= Html::encode($month) ?></h1>

  <!-- Fila 1: Retorno Mensual y Retorno Extra -->
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
        <div class="section-title mb-2">Retorno Extra</div>
        <div class="mb-2 small text-muted">Selecciona una o varias campañas de catálogos; se suman las ventas generadas por esas campañas.</div>
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

        <div class="table-responsive">
          <table class="table table-sm align-middle" id="extra-table">
            <thead>
              <tr>
                <th>Nombre del asesor</th>
                <th class="text-end">Total</th>
              </tr>
            </thead>
            <tbody>
              <tr><td colspan="2" class="text-muted">Selecciona campañas y presiona Aplicar</td></tr>
            </tbody>
            <tfoot>
              <tr>
                <th>Total</th>
                <th class="text-end" id="extra-total">$0.00</th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Fila 2: Retorno Orgánico y Retornos individuales -->
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
        <div class="section-title mb-2">Retorno Individuales (una tabla por asesor)</div>
        <div class="accordion" id="acc-retornos">
          <?php $acc=0; foreach ($asesoresById as $id=>$nombre): $acc++; $cid="acc-asesor-$id"; ?>
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

  <!-- Fila 3: Recompra, Desconocido, Página Web -->
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
// JS: Retorno Extra (AJAX) + Breakdown por asesor al abrir acordeón
$this->registerJs(<<<'JS'
(function(){
  // Retorno Extra
  $('#extra-aplicar').on('click', function(){
    const sel = $('#extra-campanas').val() || [];
    if (sel.length === 0) {
      $('#extra-table tbody').html('<tr><td colspan="2" class="text-muted">Selecciona campañas</td></tr>');
      $('#extra-total').text('$0.00');
      return;
    }
    $.post(window.retornoExtraUrl, { month: window.retornoMonth, campanas: sel, _csrf: yii.getCsrfToken() }, function(res){
      if (!res || !res.success) {
        $('#extra-table tbody').html('<tr><td colspan="2" class="text-danger">Error al calcular</td></tr>');
        $('#extra-total').text('$0.00');
        return;
      }
      const map = res.porAsesor || {};
      const rows = [];
      // Ordenar por total desc (opcional)
      const sorted = Object.entries(map).sort((a,b)=>b[1]-a[1]);
      if (sorted.length === 0) {
        rows.push('<tr><td colspan="2" class="text-muted">Sin resultados</td></tr>');
      } else {
        sorted.forEach(([aid, total])=>{
          // Buscamos el nombre del asesor en el DOM (servidor no lo manda aquí)
          const td = $('table:contains("Retorno Mensual")').find('tbody tr').filter(function(){
            return $(this).find('td:first').text().trim().length > 0;
          });
          // No es confiable leer de otra tabla; mejor dejamos el ID como número si no hay mapeo.
          rows.push(`<tr><td data-asesor-id="${aid}">Asesor #${aid}</td><td class="text-end">$${Number(total).toFixed(2)}</td></tr>`);
        });
      }
      $('#extra-table tbody').html(rows.join(''));
      $('#extra-total').text('$' + Number(res.granTotal||0).toFixed(2));
    }, 'json').fail(function(){
      $('#extra-table tbody').html('<tr><td colspan="2" class="text-danger">Error de conexión</td></tr>');
      $('#extra-total').text('$0.00');
    });
  });

  $('#extra-limpiar').on('click', function(){
    $('#extra-campanas').val([]);
    $('#extra-table tbody').html('<tr><td colspan="2" class="text-muted">Selecciona campañas y presiona Aplicar</td></tr>');
    $('#extra-total').text('$0.00');
  });

  // Retornos individuales: cargar breakdown al abrir cada acordeón
  const month = window.retornoMonth;
  $('.accordion .accordion-button').on('click', function(){
    const target = $(this).attr('data-bs-target');
    const id = String(target||'').split('-').pop(); // c-acc-asesor-<id>
    const aid = parseInt(id, 10);
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
      rows.push(`<tr><td>Organico</td><td class="text-end">$${Number(b.organico||0).toFixed(2)}</td></tr>`);
      rows.push(`<tr><td>Pagina Web</td><td class="text-end">$${Number(b.web||0).toFixed(2)}</td></tr>`);
      rows.push(`<tr><td>Recompra</td><td class="text-end">$${Number(b.recompra||0).toFixed(2)}</td></tr>`);
      rows.push(`<tr><td>Desconocido</td><td class="text-end">$${Number(b.desconocido||0).toFixed(2)}</td></tr>`);
      tbody.html(rows.join(''));
      tbody.data('loaded','1');
    }, 'json').fail(function(){
      tbody.html('<tr><td colspan="2" class="text-danger">Error de conexión</td></tr>');
    });
  });
})();
JS);
?>