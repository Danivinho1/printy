<?php
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * Espera:
 * - string $month           (YYYY-MM)
 * - array  $asesoresById    [asesor_id => nombre]
 * - arrays por categoría:   $mensualPorAsesor, $organicoPorAsesor, $recompraPorAsesor, $webPorAsesor, $desconocidoPorAsesor
 * - array  $campaniasCatalog (id, nombre)
 * - array  $campaniasById   [camp_id => nombre]
 */

$this->title = 'Retorno';
$this->params['breadcrumbs'][] = ['label' => 'Marketing', 'url' => ['campanas/index']];
$this->params['breadcrumbs'][] = $this->title;

$money = fn($n) => Yii::$app->formatter->asCurrency((float)$n, 'MXN');

// Estilo copiado de ventas-mensuales (morado, cards, tabs)
$this->registerCss(<<<CSS
.vm-bg {
  background-image:
    radial-gradient(70% 40% at 10% -10%, rgba(99,102,241,0.08) 0%, rgba(99,102,241,0) 60%),
    radial-gradient(70% 40% at 110% 0%, rgba(236,72,153,0.08) 0%, rgba(236,72,153,0) 60%),
    linear-gradient(#f1f5f9 1px, transparent 1px),
    linear-gradient(90deg, #f1f5f9 1px, transparent 1px);
  background-size: auto, auto, 24px 24px, 24px 24px;
  background-position: center, center, -1px -1px, -1px -1px;
  background-color: #fff;
  color:#0f172a;
}
.card { border-radius: 1rem; border:1px solid #e5e7eb; background:#fff; box-shadow:0 8px 20px rgba(2,6,23,.04); }
.navtab { display:inline-flex; align-items:center; padding:.5rem .75rem; border-radius:.75rem; border:1px solid #e5e7eb; color:#334155; transition:background-color .15s; text-decoration:none; }
.navtab:hover { background:#f8fafc; }
.navtab-active { background-image:linear-gradient(90deg,#4f46e5,#7c3aed); color:#fff; border-color:transparent; box-shadow:0 8px 24px rgba(79,70,229,.25); }
.table-lite th { font-size:.8rem; letter-spacing:.02em; text-transform:uppercase; color:#64748b; }
.table-lite td { color:#0f172a; }
.progress-wrap { background:#eef2f7; border-radius:9999px; overflow:hidden; height:10px; }
.progress-bar { height:100%; border-radius:9999px; background-image:linear-gradient(90deg,#4f46e5,#7c3aed); }
.badge { display:inline-flex; align-items:center; gap:.35rem; padding:.15rem .5rem; border-radius:9999px; font-size:.75rem; }
.badge-soft { background:#eef2ff; color:#3730a3; }
.table .collapse td { background:#fcfbff; }
CSS);

// Exponer URLs y parámetros a JS
$this->registerJs(
    'window.retornoExtraUrl = ' . json_encode(Url::to(['retorno/extra-totales'])) . ';
     window.retornoExtraMatrizUrl = ' . json_encode(Url::to(['retorno/extra-matriz'])) . ';
     window.retornoBreakdownUrl = ' . json_encode(Url::to(['retorno/breakdown-asesor'])) . ';
     window.retornoMonth = ' . json_encode($month) . ';
     window.asesoresById = ' . json_encode($asesoresById ?? [], JSON_UNESCAPED_UNICODE) . ';
     window.campaniasById = ' . json_encode($campaniasById ?? [], JSON_UNESCAPED_UNICODE) . ';',
    \yii\web\View::POS_HEAD
);
?>

<div class="vm-bg min-vh-100">
  <div class="container py-4">

    <!-- Tabs navegación -->
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div class="d-flex gap-2 text-sm">
        <a class="navtab" href="<?= Url::to(['campanas/index']) ?>">Campañas</a>
        <a class="navtab navtab-active" href="<?= Url::to(['retorno/index']) ?>">Retorno</a>
        <a class="navtab" href="<?= Url::to(['ventas-mensuales/index']) ?>">Comparativas</a>
        <a class="navtab" href="<?= Url::to(['productos-vendidos/index']) ?>">Productos</a>
      </div>
      <form method="get" action="<?= Url::to(['retorno/index']) ?>" class="d-flex align-items-center gap-2">
        <label class="text-muted small">Mes</label>
        <input type="month" name="month" value="<?= Html::encode($month) ?>" class="form-control form-control-sm" style="max-width: 180px;">
        <button class="btn btn-light border btn-sm">Ver</button>
      </form>
    </div>

    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h1 class="h5 mb-1">Retorno</h1>
        <div class="text-muted small">Mes seleccionado: <span class="fw-semibold"><?= Html::encode($month) ?></span></div>
      </div>
      <div class="card p-3">
        <div class="d-flex align-items-center gap-3">
          <div class="text-muted small">Panel</div>
          <div class="fw-bold">Marketing · Retorno</div>
        </div>
      </div>
    </div>

    <!-- Fila 1: Retorno mensual + Selector Extra -->
    <div class="row g-3 mb-3">
      <div class="col-12 col-xl-6">
        <div class="card p-3">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="fw-semibold">Retorno Mensual</div>
            <span class="badge badge-soft">Totales por asesor</span>
          </div>
          <div class="table-responsive">
            <table class="table table-hover table-lite align-middle">
              <thead>
                <tr>
                  <th>Nombre del asesor</th>
                  <th class="text-end">Total mensual</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($asesoresById)): ?>
                  <tr><td colspan="2" class="text-muted">Sin asesores</td></tr>
                <?php else: foreach ($asesoresById as $id=>$nombre): ?>
                  <tr>
                    <td><?= Html::encode($nombre) ?></td>
                    <td class="text-end fw-semibold"><?= $money(($mensualPorAsesor[$id] ?? 0)) ?></td>
                  </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="col-12 col-xl-6">
        <div class="card p-3">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="fw-semibold">Retorno Extra</div>
            <span class="badge badge-soft">Selecciona campañas</span>
          </div>
          <div class="mb-2 small text-muted">Selecciona una o varias campañas y presiona “Aplicar”.</div>
          <form id="extra-form" class="mb-2">
            <div class="row g-2">
              <div class="col-12">
                <select id="extra-campanas" name="campanas[]" class="form-select" multiple size="6">
                  <?php foreach (($campaniasCatalog ?? []) as $c): ?>
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
          <div class="text-muted small">Total global seleccionado: <span id="extra-grand-total" class="fw-bold">$0.00</span></div>
        </div>
      </div>
    </div>

    <!-- Fila 2: Extra por Campaña y por Asesor -->
    <div class="row g-3 mb-3">
      <div class="col-12 col-xl-6">
        <div class="card p-3">
          <div class="fw-semibold mb-2">Retorno Extra por Campaña</div>
          <div class="table-responsive">
            <table class="table table-hover table-lite align-middle" id="extra-por-campania">
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
          <div class="text-muted small">Tip: Haz clic en la fila para ver detalle por asesor.</div>
        </div>
      </div>
      <div class="col-12 col-xl-6">
        <div class="card p-3">
          <div class="fw-semibold mb-2">Retorno Extra por Asesor</div>
          <div class="table-responsive">
            <table class="table table-hover table-lite align-middle" id="extra-por-asesor">
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
          <div class="text-muted small">Tip: Haz clic en la fila para ver detalle por campaña.</div>
        </div>
      </div>
    </div>

    <!-- Fila 3: Orgánico + Retornos individuales -->
    <div class="row g-3 mb-3">
      <div class="col-12 col-xl-6">
        <div class="card p-3">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="fw-semibold">Retorno Orgánico</div>
            <span class="badge badge-soft">Campaña “Organica”</span>
          </div>
          <div class="table-responsive">
            <table class="table table-hover table-lite align-middle">
              <thead>
                <tr>
                  <th>Nombre del asesor</th>
                  <th class="text-end">Total orgánico</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach (($asesoresById ?? []) as $id=>$nombre): ?>
                  <tr>
                    <td><?= Html::encode($nombre) ?></td>
                    <td class="text-end fw-semibold"><?= $money(($organicoPorAsesor[$id] ?? 0)) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="col-12 col-xl-6">
        <div class="card p-3">
          <div class="fw-semibold mb-2">Retorno Individuales (por asesor)</div>
          <div class="accordion" id="acc-retornos">
            <?php foreach (($asesoresById ?? []) as $id=>$nombre): $cid="acc-asesor-$id"; ?>
              <div class="accordion-item">
                <h2 class="accordion-header" id="h-<?= $cid ?>">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#c-<?= $cid ?>">
                    Retorno de <?= Html::encode($nombre) ?>
                  </button>
                </h2>
                <div id="c-<?= $cid ?>" class="accordion-collapse collapse" data-bs-parent="#acc-retornos">
                  <div class="accordion-body">
                    <table class="table table-sm align-middle mb-0 table-lite">
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
    <div class="row g-3 mb-4">
      <div class="col-12 col-xl-4">
        <div class="card p-3">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="fw-semibold">Retorno Recompra</div>
            <span class="badge badge-soft">Campaña “Recompra”</span>
          </div>
          <div class="table-responsive">
            <table class="table table-hover table-lite align-middle">
              <thead><tr><th>Nombre del asesor</th><th class="text-end">Total</th></tr></thead>
              <tbody>
                <?php foreach (($asesoresById ?? []) as $id=>$nombre): ?>
                  <tr>
                    <td><?= Html::encode($nombre) ?></td>
                    <td class="text-end fw-semibold"><?= $money(($recompraPorAsesor[$id] ?? 0)) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="col-12 col-xl-4">
        <div class="card p-3">
          <div class="fw-semibold mb-2">Retorno Desconocido</div>
          <div class="table-responsive">
            <table class="table table-hover table-lite align-middle">
              <thead><tr><th>Nombre del asesor</th><th class="text-end">Total</th></tr></thead>
              <tbody>
                <?php foreach (($asesoresById ?? []) as $id=>$nombre): ?>
                  <tr>
                    <td><?= Html::encode($nombre) ?></td>
                    <td class="text-end fw-semibold"><?= $money(($desconocidoPorAsesor[$id] ?? 0)) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="col-12 col-xl-4">
        <div class="card p-3">
          <div class="fw-semibold mb-2">Retorno Página Web</div>
          <div class="table-responsive">
            <table class="table table-hover table-lite align-middle">
              <thead><tr><th>Nombre del asesor</th><th class="text-end">Total</th></tr></thead>
              <tbody>
                <?php foreach (($asesoresById ?? []) as $id=>$nombre): ?>
                  <tr>
                    <td><?= Html::encode($nombre) ?></td>
                    <td class="text-end fw-semibold"><?= $money(($webPorAsesor[$id] ?? 0)) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<?php
// JS: Extra matriz campaña×asesor + Breakdown por asesor
$this->registerJs(<<<'JS'
(function(){
  function fmt(n){ return '$' + Number(n||0).toFixed(2); }

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
           <td class="text-end fw-semibold">${fmt(obj.total)}</td>
         </tr>`
      );
      const items = (obj.items||[]).map(it =>
        `<tr><td>${it.name}</td><td class="text-end">${fmt(it.total)}</td></tr>`
      ).join('');
      rows.push(
        `<tr class="collapse" id="${rid}">
           <td colspan="2">
             <div class="table-responsive">
               <table class="table table-sm mb-0 table-lite">
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
           <td class="text-end fw-semibold">${fmt(obj.total)}</td>
         </tr>`
      );
      const items = (obj.items||[]).map(it =>
        `<tr><td>${it.name}</td><td class="text-end">${fmt(it.total)}</td></tr>`
      ).join('');
      rows.push(
        `<tr class="collapse" id="${rid}">
           <td colspan="2">
             <div class="table-responsive">
               <table class="table table-sm mb-0 table-lite">
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

  $('#extra-aplicar').on('click', function(){
    const sel = $('#extra-campanas').val() || [];
    if (sel.length === 0) {
      $('#extra-por-campania tbody').html('<tr><td colspan="2" class="text-muted">Selecciona campañas</td></tr>');
      $('#extra-por-asesor tbody').html('<tr><td colspan="2" class="text-muted">Selecciona campañas</td></tr>');
      $('#extra-grand-total').text(fmt(0));
      return;
    }
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

  // Carga breakdown al abrir cada acordeón
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