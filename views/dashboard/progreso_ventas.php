<?php
/** @var int $porcentajeUnidades */
/** @var int $porcentajeDinero */
/** @var int $totalUnidades */
/** @var int $metaUnidades */
/** @var float $totalDinero */
/** @var float $metaDinero */
?>

<div class="col-xl-6 col-lg-8 mb-4">
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <h6 class="section-title">Progreso hacia Objetivos (Ventas)</h6>

            <!-- Unidades -->
            <div class="mb-3">
                <div class="d-flex justify-content-between mb-2">
                    <span>Unidades</span>
                    <span class="badge bg-info"><?= $porcentajeUnidades ?>%</span>
                </div>
                <div class="progress">
                    <div class="progress-bar bg-info" style="width: <?= (int)min($porcentajeUnidades, 100) ?>%"></div>
                </div>
                <small><?= number_format($totalUnidades) ?> / <?= number_format($metaUnidades) ?> unidades</small>
            </div>

            <!-- Ingresos -->
            <div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Ingresos</span>
                    <span class="badge bg-success"><?= $porcentajeDinero ?>%</span>
                </div>
                <div class="progress">
                    <div class="progress-bar bg-success" style="width: <?= (int)min($porcentajeDinero, 100) ?>%"></div>
                </div>
                <small>$<?= number_format($totalDinero, 2) ?> / $<?= number_format($metaDinero, 2) ?></small>
            </div>
        </div>
    </div>
</div>
