<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\db\Query;
use yii\db\Expression;

use app\models\Catalogos;
use app\models\Campanas;
use app\models\Ventas;
use app\models\Retorno;

class RetornoController extends Controller
{
    // Campos reales en tu BD
    private string $ventasDateField    = 'fecha_compra';
    private string $ventasAmountField  = 'precio_total';
    private string $ventasAsesorField  = 'asesor_id';
    private string $ventasUnitsField   = 'unidades';
    private string $ventasPhoneField   = 'telefono';
    private string $ventasMedioField   = 'medio_id';
    private string $ventasCampField    = 'campaña_id';

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only'  => ['index', 'save-deposito', 'get-campanas-activas'],
                'rules' => [
                    ['allow' => true, 'roles' => ['@']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'save-deposito' => ['POST'],
                    'get-campanas-activas' => ['GET'],
                ],
            ],
        ];
    }

    public function actionIndex(?string $month = null)
    {
        if (!$month) {
            $month = Yii::$app->formatter->asDate('now', 'php:Y-m');
        }
        [$start, $end] = $this->monthRange($month);

        // Catálogos base
        $asesores = Catalogos::find()
            ->select(['id','nombre'])
            ->where(['tipo' => 'asesor'])
            ->orderBy('nombre')
            ->asArray()
            ->all();
        $asesoresById = ArrayHelper::map($asesores, 'id', 'nombre');

        // IDs de “Pagina Web” y “Organica” si existen
        $medioWebId = Catalogos::find()->select('id')->where(['tipo'=>'medio','nombre'=>'Pagina Web'])->scalar();
        $campOrganicaId = Catalogos::find()->select('id')->where(['tipo'=>'campaña','nombre'=>'Organica'])->scalar();

        // Subquery clasificando cada venta del mes en categoría exclusiva
        $v = Ventas::tableName();
        $df = $this->ventasDateField;
        $af = $this->ventasAmountField;
        $mf = $this->ventasMedioField;
        $cf = $this->ventasCampField;
        $pf = $this->ventasPhoneField;
        $sf = $this->ventasAsesorField;

        $categoriaExpr = "CASE " .
            ($medioWebId ? "WHEN v.$mf = :medioWeb THEN 'web' " : "") .
            ($campOrganicaId ? "WHEN v.$cf = :campOrg THEN 'organico' " : "") .
            "WHEN v.$pf IS NOT NULL AND EXISTS (SELECT 1 FROM $v v2 WHERE v2.$pf = v.$pf AND v2.$df < :start) THEN 'recompra' " .
            "ELSE 'desconocido' END";

        $ventasClasificadas = (new Query())
            ->select([
                "asesor_id" => "v.$sf",
                "categoria" => new Expression($categoriaExpr),
                "monto"     => new Expression("COALESCE(v.$af,0)"),
            ])
            ->from("$v v")
            ->where(['between', "v.$df", $start, $end]);

        $params = [':start' => $start];
        if ($medioWebId)      $params[':medioWeb'] = $medioWebId;
        if ($campOrganicaId)  $params[':campOrg']  = $campOrganicaId;

        // Totales por categoría (exclusivas)
        $totalesCat = (new Query())
            ->select(['categoria', 'total' => 'SUM(monto)'])
            ->from(['t' => $ventasClasificadas])
            ->params($params)
            ->groupBy('categoria')
            ->all();
        $byCat = ArrayHelper::map($totalesCat, 'categoria', 'total');

        // Totales por asesor y categoría
        $rowsAsesorCat = (new Query())
            ->select(['asesor_id', 'categoria', 'total' => 'SUM(monto)'])
            ->from(['t' => $ventasClasificadas])
            ->params($params)
            ->groupBy(['asesor_id','categoria'])
            ->all();

        $porAsesor = [
            'retorno'     => [], // viene de tabla retorno
            'organico'    => [],
            'recompra'    => [],
            'desconocido' => [],
            'web'         => [],
            'extra'       => [], // suma de extra_precio
        ];
        foreach ($rowsAsesorCat as $r) {
            $cat = $r['categoria'];
            if (!isset($porAsesor[$cat])) continue;
            $aid = (int)($r['asesor_id'] ?? 0);
            if ($aid <= 0) continue;
            $porAsesor[$cat][$aid] = round((float)$r['total'], 2);
        }

        // Extra: suma de extra_precio (no se incluye en la clasificación para no duplicar precio_total)
        $extraRows = (new Query())
            ->select([$sf . ' AS asesor_id', 'total' => 'SUM(COALESCE(extra_precio,0))'])
            ->from($v)
            ->where(['between', $df, $start, $end])
            ->groupBy($sf)
            ->all();
        $extraTotal = 0.0;
        foreach ($extraRows as $r) {
            $aid = (int)$r['asesor_id'];
            $val = round((float)$r['total'], 2);
            if ($aid) $porAsesor['extra'][$aid] = $val;
            $extraTotal += $val;
        }

        // Retorno: usa la tabla retorno.cantidad (separada de ventas)
        $retornoTotal = (float) (new Query())
            ->from(Retorno::tableName())
            ->where(['between', 'created_at', $start, $end])
            ->sum('cantidad');
        $retornoTotal = round($retornoTotal ?: 0, 2);

        $retornoAsesor = (new Query())
            ->select(['asesor_id', 'total' => 'SUM(cantidad)'])
            ->from(Retorno::tableName())
            ->where(['between', 'created_at', $start, $end])
            ->groupBy('asesor_id')
            ->all();
        foreach ($retornoAsesor as $r) {
            $aid = (int)$r['asesor_id'];
            if ($aid) $porAsesor['retorno'][$aid] = round((float)$r['total'], 2);
        }

        // Totales individuales (desde clasificación exclusiva + retorno/extra separadas)
        $organico    = round((float)($byCat['organico'] ?? 0), 2);
        $recompra    = round((float)($byCat['recompra'] ?? 0), 2);
        $desconocido = round((float)($byCat['desconocido'] ?? 0), 2);
        $web         = round((float)($byCat['web'] ?? 0), 2);
        $extra       = round($extraTotal, 2);
        $retorno     = round($retornoTotal, 2);

        // Total de ventas del mes (exclusivo por categoría) + retorno aparte
        $totalVentasMes = $organico + $recompra + $desconocido + $web;
        $granTotal = $totalVentasMes + $retorno; // si prefieres sólo ventas, usa $totalVentasMes

        // Estado de depósito por mes (en sesión)
        $deposito = $this->loadDepositoFromSession($month);

        // Ordenar montos por asesor desc
        foreach ($porAsesor as $k => $arr) {
            if (is_array($arr)) {
                arsort($arr);
                $porAsesor[$k] = $arr;
            }
        }

        return $this->render('index', [
            'month'        => $month,
            'asesores'     => $asesores,
            'asesoresById' => $asesoresById,
            'retorno'      => $retorno,
            'organico'     => $organico,
            'recompra'     => $recompra,
            'desconocido'  => $desconocido,
            'web'          => $web,
            'extra'        => $extra,
            'granTotal'    => $granTotal,
            'porAsesor'    => $porAsesor,
            'deposito'     => $deposito,
        ]);
    }

    public function actionSaveDeposito()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $month = Yii::$app->request->post('month');
        $fecha = Yii::$app->request->post('fecha');
        $monto = (float) Yii::$app->request->post('monto', 0);
        $resta = (float) Yii::$app->request->post('resta', 0);
        $conf  = Yii::$app->request->post('confirmaciones', []);

        if (!$month) {
            return ['success' => false, 'message' => 'Mes inválido'];
        }

        $state = [
            'fecha'          => $fecha,
            'monto'          => $monto,
            'resta'          => $resta,
            'confirmaciones' => is_array($conf) ? $conf : [],
        ];

        Yii::$app->session->set($this->depositoKey($month), $state);

        return ['success' => true, 'message' => 'Depósito guardado'];
    }

    public function actionGetCampanasActivas(int $asesorId)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $rows = Campanas::find()
            ->where(['asesor_id' => $asesorId, 'analisis' => 'Continuar'])
            ->orderBy(['id' => SORT_DESC])
            ->asArray()
            ->all();

        $items = [];
        foreach ($rows as $r) {
            $texto = $r['nombre'] ?? null;
            if (!$texto && isset($r['campaña_id'])) {
                $texto = Catalogos::find()->select('nombre')->where(['id' => $r['campaña_id']])->scalar();
            }
            $items[] = ['id' => (int)$r['id'], 'text' => $texto ?: ('Campaña #' . $r['id'])];
        }

        return ['items' => $items];
    }

    private function monthRange(string $ym): array
    {
        $start = $ym . '-01 00:00:00';
        $end   = date('Y-m-t 23:59:59', strtotime($ym . '-01'));
        return [$start, $end];
    }

    private function depositoKey(string $month): string
    {
        return 'deposito:' . $month;
    }

    private function loadDepositoFromSession(string $month): array
    {
        $state = Yii::$app->session->get($this->depositoKey($month), []);
        return array_merge([
            'fecha'          => '',
            'monto'          => 0.0,
            'resta'          => 0.0,
            'confirmaciones' => [],
        ], $state);
    }
}