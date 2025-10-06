<?php
/* @var $this yii\web\View */
/* @var $metrics array */

use yii\helpers\Html;

$this->title = 'Dashboard Logística';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="dashboard-logistica">
    <h1><?= Html::encode($this->title) ?></h1>

    <!-- Métricas Principales -->
    <div class="row mb-4">
        <!-- Total Anticipos -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Anticipos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                $<?= number_format($metrics['total_anticipos'], 2) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Restantes -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Total Pendiente
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                $<?= number_format($metrics['total_restantes'], 2) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Paquetes a Tiempo -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Enviados a Tiempo
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $metrics['enviados_a_tiempo'] ?>
                            </div>
                            <div class="text-xs text-muted">
                                <?= number_format($metrics['porcentaje_a_tiempo'], 1) ?>% del total
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Paquetes con Retraso -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Enviados con Retraso
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $metrics['enviados_con_retraso'] ?>
                            </div>
                            <div class="text-xs text-muted">
                                <?= number_format($metrics['porcentaje_retraso'], 1) ?>% del total
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos y Detalles Adicionales -->
    <div class="row">
        <!-- Resumen de Estatus -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Resumen de Estatus de Pago</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="text-center">
                                <span class="badge badge-success p-2">Liquidados</span>
                                <div class="h4 mt-2"><?= $metrics['liquidados'] ?></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <span class="badge badge-warning p-2">Pendientes</span>
                                <div class="h4 mt-2"><?= $metrics['pendientes_pago'] ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resumen de Envíos -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Resumen de Envíos</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="text-center">
                                <span class="badge badge-success p-2">Enviados</span>
                                <div class="h4 mt-2"><?= $metrics['enviados'] ?></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <span class="badge badge-danger p-2">Pendientes</span>
                                <div class="h4 mt-2"><?= $metrics['pendientes_envio'] ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enlaces rápidos -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Acciones Rápidas</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <?= Html::a(
                                '<i class="fas fa-list"></i> Ver Todos los Pedidos',
                                ['index'],
                                ['class' => 'btn btn-primary btn-block']
                            ) ?>
                        </div>
                        <div class="col-md-3">
                            <?= Html::a(
                                '<i class="fas fa-exclamation-triangle"></i> Pedidos Atrasados',
                                ['index', 'filter' => 'atrasados'],
                                ['class' => 'btn btn-warning btn-block']
                            ) ?>
                        </div>
                        <div class="col-md-3">
                            <?= Html::a(
                                '<i class="fas fa-dollar-sign"></i> Pagos Pendientes',
                                ['index', 'filter' => 'pendientes_pago'],
                                ['class' => 'btn btn-info btn-block']
                            ) ?>
                        </div>
                        <div class="col-md-3">
                            <?= Html::a(
                                '<i class="fas fa-shipping-fast"></i> Listos para Envío',
                                ['index', 'filter' => 'listos_envio'],
                                ['class' => 'btn btn-success btn-block']
                            ) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}
.border-left-danger {
    border-left: 0.25rem solid #e74a3b !important;
}
.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}
.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}
</style>