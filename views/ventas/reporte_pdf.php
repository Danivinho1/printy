<?php
// views/ventas/reporte_pdf.php
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ventas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 5px;
        }
        
        .report-title {
            font-size: 18px;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .report-date {
            color: #666;
            font-size: 14px;
        }
        
        .metrics-grid {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        
        .metric-card {
            display: table-cell;
            width: 25%;
            text-align: center;
            border: 1px solid #ddd;
            padding: 15px;
            background-color: #f8f9fa;
        }
        
        .metric-value {
            font-size: 20px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 5px;
        }
        
        .metric-label {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #007bff;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin: 25px 0 15px 0;
        }
        
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .products-table th,
        .products-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        .products-table th {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }
        
        .products-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 10px;
        }
        
        .summary-box {
            background-color: #e9ecef;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }
        
        .highlight {
            background-color: #fff3cd;
            padding: 2px 4px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">Tu Empresa de Letreros</div>
        <div class="report-title">Reporte de Ventas</div>
        <div class="report-date">Generado el: <?= $fechaReporte ?></div>
        <div class="report-date">Período: <?= date('F Y', strtotime($mesActual . '-01')) ?></div>
    </div>

    <!-- Métricas Principales -->
    <div class="metrics-grid">
        <div class="metric-card">
            <div class="metric-value"><?= number_format($totalVentas) ?></div>
            <div class="metric-label">Total Ventas</div>
        </div>
        <div class="metric-card">
            <div class="metric-value"><?= number_format($totalUnidades) ?></div>
            <div class="metric-label">Unidades Vendidas</div>
        </div>
        <div class="metric-card">
            <div class="metric-value">$<?= number_format($totalIngresos, 2) ?></div>
            <div class="metric-label">Ingresos Totales</div>
        </div>
        <div class="metric-card">
            <div class="metric-value">$<?= number_format($totalIngresos / ($totalVentas ?: 1), 2) ?></div>
            <div class="metric-label">Ticket Promedio</div>
        </div>
    </div>

    <!-- Resumen Financiero -->
    <div class="summary-box">
        <h3 style="margin-top: 0;">Resumen Financiero</h3>
        <table style="width: 100%;">
            <tr>
                <td><strong>Ingresos Totales:</strong></td>
                <td class="text-right"><span class="highlight">$<?= number_format($totalIngresos, 2) ?></span></td>
            </tr>
            <tr>
                <td><strong>Anticipos Recibidos:</strong></td>
                <td class="text-right">$<?= number_format($totalAnticipo, 2) ?></td>
            </tr>
            <tr>
                <td><strong>Saldo Pendiente:</strong></td>
                <td class="text-right">$<?= number_format($totalRestante, 2) ?></td>
            </tr>
            <tr>
                <td><strong>% Cobrado:</strong></td>
                <td class="text-right"><?= $totalIngresos > 0 ? number_format(($totalAnticipo / $totalIngresos) * 100, 1) : 0 ?>%</td>
            </tr>
        </table>
    </div>

    <!-- Productos Más Vendidos -->
    <div class="section-title">Productos Más Vendidos</div>
    <table class="products-table">
        <thead>
            <tr>
                <th>Ranking</th>
                <th>Producto</th>
                <th class="text-center">Ventas</th>
                <th class="text-center">Unidades</th>
                <th class="text-right">Ingresos</th>
                <th class="text-right">Promedio</th>
            </tr>
        </thead>
        <tbody>
            <?php $ranking = 1; ?>
            <?php foreach ($productosVendidos as $nombre => $datos): ?>
                <tr>
                    <td class="text-center"><?= $ranking ?></td>
                    <td><?= htmlspecialchars($nombre) ?></td>
                    <td class="text-center"><?= number_format($datos['cantidad']) ?></td>
                    <td class="text-center"><?= number_format($datos['unidades']) ?></td>
                    <td class="text-right">$<?= number_format($datos['ingresos'], 2) ?></td>
                    <td class="text-right">$<?= number_format($datos['ingresos'] / ($datos['cantidad'] ?: 1), 2) ?></td>
                </tr>
                <?php $ranking++; ?>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Análisis de Rendimiento -->
    <div class="section-title">Análisis de Rendimiento</div>
    <div style="display: table; width: 100%;">
        <div style="display: table-cell; width: 50%; padding-right: 10px;">
            <h4>Indicadores Clave</h4>
            <ul>
                <li><strong>Ventas por día:</strong> <?= number_format($totalVentas / date('j'), 1) ?></li>
                <li><strong>Unidades por venta:</strong> <?= number_format($totalUnidades / ($totalVentas ?: 1), 1) ?></li>
                <li><strong>Mejor día de ventas:</strong> 
                    <?php
                    $ventasPorDia = [];
                    foreach ($ventas as $venta) {
                        $dia = date('j', strtotime($venta->fecha_compra));
                        $ventasPorDia[$dia] = ($ventasPorDia[$dia] ?? 0) + 1;
                    }
                    $mejorDia = array_keys($ventasPorDia, max($ventasPorDia))[0] ?? 'N/A';
                    echo $mejorDia . ' (' . max($ventasPorDia) . ' ventas)';
                    ?>
                </li>
            </ul>
        </div>
        <div style="display: table-cell; width: 50%; padding-left: 10px;">
            <h4>Objetivos del Mes</h4>
            <ul>
                <li><strong>Meta unidades:</strong> 100 (<?= number_format(($totalUnidades / 100) * 100, 1) ?>% alcanzado)</li>
                <li><strong>Meta ingresos:</strong> $500,000 (<?= number_format(($totalIngresos / 500000) * 100, 1) ?>% alcanzado)</li>
                <li><strong>Estado:</strong> 
                    <?php
                    $porcentaje = ($totalIngresos / 500000) * 100;
                    if ($porcentaje >= 100) echo '<span style="color: green;">🎯 Meta Alcanzada</span>';
                    elseif ($porcentaje >= 75) echo '<span style="color: orange;">📈 Cerca de la Meta</span>';
                    else echo '<span style="color: red;">📊 Por Debajo de la Meta</span>';
                    ?>
                </li>
            </ul>
        </div>
    </div>

    <!-- Detalle de Ventas Recientes -->
    <div class="section-title">Últimas 10 Ventas</div>
    <table class="products-table">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Producto</th>
                <th class="text-center">Unidades</th>
                <th class="text-right">Total</th>
                <th class="text-right">Anticipo</th>
                <th class="text-right">Restante</th>
            </tr>
        </thead>
        <tbody>
            <?php $ultimasVentas = array_slice($ventas, 0, 10); ?>
            <?php foreach ($ultimasVentas as $venta): ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($venta->fecha_compra)) ?></td>
                    <td><?= htmlspecialchars($venta->telefono) ?></td>
                    <td><?= $venta->tipoLetrero ? htmlspecialchars($venta->tipoLetrero->nombre) : 'N/A' ?></td>
                    <td class="text-center"><?= number_format($venta->unidades) ?></td>
                    <td class="text-right">$<?= number_format($venta->precio_total, 2) ?></td>
                    <td class="text-right">$<?= number_format($venta->anticipo, 2) ?></td>
                    <td class="text-right">$<?= number_format($venta->restante, 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">
        <p>Este reporte fue generado automáticamente por el Sistema de Ventas</p>
        <p>Fecha de generación: <?= date('d/m/Y H:i:s') ?></p>
    </div>
</body>
</html>