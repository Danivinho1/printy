<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\db\Query;
use app\models\Ventas;

class VentasMensualesController extends Controller
{
    // Campos reales según tu BD
    private string $dateField   = 'fecha_compra';
    private string $amountField = 'precio_total';

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only'  => ['index'],
                'rules' => [
                    ['allow' => true, 'roles' => ['@']],
                ],
            ],
        ];
    }

    public function actionIndex(?int $year = null)
    {
        $year = $year ?: (int)date('Y');
        $df = $this->dateField;
        $af = $this->amountField;

        // Suma por mes del año seleccionado
        $rows = (new Query())
            ->select([
                "mes"   => "MONTH($df)",
                "total" => "SUM(COALESCE($af,0))",
            ])
            ->from(Ventas::tableName())
            ->where(["between", $df, "$year-01-01 00:00:00", "$year-12-31 23:59:59"])
            ->groupBy(["MONTH($df)"])
            ->all();

        // Mapear 12 meses
        $totales = array_fill(1, 12, 0.0);
        foreach ($rows as $r) {
            $m = (int)$r['mes'];
            $totales[$m] = round((float)$r['total'], 2);
        }

        $months = [];
        $maxTotal = 0.0; $maxMonth = 1;
        for ($m = 1; $m <= 12; $m++) {
            $start = sprintf('%04d-%02d-01', $year, $m);
            $end   = date('Y-m-t', strtotime($start));
            $total = $totales[$m] ?? 0.0;
            if ($total > $maxTotal) { $maxTotal = $total; $maxMonth = $m; }

            $months[] = [
                'num'   => $m,
                'label' => $this->monthNameEs($m),
                'start' => $start,
                'end'   => $end,
                'total' => $total,
            ];
        }

        return $this->render('index', [
            'year'      => $year,
            'months'    => $months,
            'maxTotal'  => $maxTotal,
            'maxMonth'  => $maxMonth,
            'today'     => date('Y-m-d'),
        ]);
    }

    private function monthNameEs(int $m): string
    {
        static $n = [1=>'Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
        return $n[$m] ?? (string)$m;
    }
}