<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\db\Query;

class ProductosVendidosController extends Controller
{
    // Campos reales según tu BD
    private string $dateField    = 'fecha_compra';
    private string $unitsField   = 'unidades';
    private string $amountField  = 'precio_total';
    private string $productField = 'tipo_letrero_id';

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

        $df = $this->dateField;    // v.fecha_compra
        $uf = $this->unitsField;   // v.unidades
        $af = $this->amountField;  // v.precio_total
        $pf = $this->productField; // v.tipo_letrero_id

        // SELECT por producto con columnas mensuales "Pzs" (u1..u12) y "Dinero" (m1..m12)
        // Importante: no usar SUM dentro del THEN; usar medida directa y el SUM afuera.
        $select = array_merge(
            [
                'producto_id' => 'c.id',
                'producto'    => 'c.nombre',
            ],
            $this->monthSelects("COALESCE(v.$uf,0)", 'u', 'v', $df),
            $this->monthSelects("COALESCE(v.$af,0)", 'm', 'v', $df),
            [
                // Totales anuales por producto (piezas y dinero)
                'total_u' => "COALESCE(SUM(v.$uf),0)",
                'total_m' => "COALESCE(SUM(v.$af),0)",
            ]
        );

        $q = (new Query())
            ->select($select)
            ->from(['c' => 'catalogos'])
            ->leftJoin(['v' => 'ventas'], "v.$pf = c.id AND v.$df BETWEEN :start AND :end")
            ->where(['c.tipo' => 'tipo_letrero'])
            ->groupBy(['c.id', 'c.nombre'])
            ->orderBy(['c.nombre' => SORT_ASC])
            ->params([
                ':start' => "$year-01-01 00:00:00",
                ':end'   => "$year-12-31 23:59:59",
            ]);

        $rows = $q->all();

        // Estructura para la vista
        $data = [];
        $totalsByMonth = array_fill(1, 12, ['u' => 0.0, 'm' => 0.0]);
        $grandUnits = 0.0;
        $grandMoney = 0.0;

        foreach ($rows as $r) {
            $perMonth = [];
            for ($m = 1; $m <= 12; $m++) {
                $u = (float)($r["u$m"] ?? 0);
                $x = (float)($r["m$m"] ?? 0);
                $perMonth[$m] = ['u' => $u, 'm' => $x];
                $totalsByMonth[$m]['u'] += $u;
                $totalsByMonth[$m]['m'] += $x;
            }

            $rowUnits = (float)($r['total_u'] ?? 0);
            $rowMoney = (float)($r['total_m'] ?? 0);
            $grandUnits += $rowUnits;
            $grandMoney += $rowMoney;

            $data[] = [
                'producto_id' => (int)$r['producto_id'],
                'producto'    => $r['producto'],
                'm'           => $perMonth,
                'total_u'     => $rowUnits,
                'total_m'     => $rowMoney,
            ];
        }

        return $this->render('index', [
            'year'          => $year,
            'rows'          => $data,
            'totalsByMonth' => $totalsByMonth,
            'grandUnits'    => $grandUnits,
            'grandMoney'    => $grandMoney,
            'today'         => date('Y-m-d'),
            'months'        => $this->monthsEs(),
        ]);
    }

    /**
     * Genera columnas mensuales con formato:
     *   alias => SUM(CASE WHEN MONTH(v.fecha) = N THEN medida ELSE 0 END)
     */
    private function monthSelects(string $measureExpr, string $prefix, string $tAlias, string $dateField): array
    {
        $out = [];
        for ($m = 1; $m <= 12; $m++) {
            $out["{$prefix}{$m}"] = "SUM(CASE WHEN MONTH($tAlias.$dateField) = $m THEN $measureExpr ELSE 0 END)";
        }
        return $out;
    }

    private function monthsEs(): array
    {
        return [1=>'Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
    }
}