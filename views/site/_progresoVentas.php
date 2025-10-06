<?php
/**
 * @var int $porcentajeUnidades
 * @var int $porcentajeDinero
 * @var int|float $totalUnidades
 * @var int|float $metaUnidades
 * @var int|float $totalDinero
 * @var int|float $metaDinero
 */
?>

<div class="col-md-6">
    <div class="card shadow-sm">
        <div class="card-body">
            <h6>Progreso Unidades</h6>
            <div class="progress mb-2" style="height: 22px;">
                <div class="progress-bar bg-info" role="progressbar" style="width: <?= $porcentajeUnidades ?>%;" aria-valuenow="<?= $porcentajeUnidades ?>" aria-valuemin="0" aria-valuemax="100">
                    <?= $porcentajeUnidades ?>%
                </div>
            </div>
            <small><?= $totalUnidades ?> de <?= $metaUnidades ?> unidades</small>
        </div>
    </div>
</div>
<div class="col-md-6">
    <div class="card shadow-sm">
        <div class="card-body">
            <h6>Progreso Ingresos</h6>
            <div class="progress mb-2" style="height: 22px;">
                <div class="progress-bar bg-success" role="progressbar" style="width: <?= $porcentajeDinero ?>%;" aria-valuenow="<?= $porcentajeDinero ?>" aria-valuemin="0" aria-valuemax="100">
                    <?= $porcentajeDinero ?>%
                </div>
            </div>
            <small>$<?= number_format($totalDinero, 2) ?> de $<?= number_format($metaDinero, 2) ?></small>
        </div>
    </div>
</div>