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

class RetornoController extends Controller
{
    // Campos en ventas
    private string $ventasDateField    = 'fecha_compra';
    private string $ventasAmountField  = 'precio_total';
    private string $ventasAsesorField  = 'asesor_id';
    private string $ventasPhoneField   = 'telefono';
    private string $ventasMedioField   = 'medio_id';
    private string $ventasCampField    = 'campaña_id';

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only'  => ['index', 'extra-totales', 'breakdown-asesor'],
                'rules' => [
                    ['allow' => true, 'roles' => ['@']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'extra-totales'   => ['POST'],
                    'breakdown-asesor'=> ['GET'],
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

        // IDs de catálogos de referencia
        $medioWebId      = Catalogos::find()->select('id')->where(['tipo'=>'medio','nombre'=>'Pagina Web'])->scalar();
        $campOrganicaId  = Catalogos::find()->select('id')->where(['tipo'=>'campaña','nombre'=>'Organica'])->scalar();
        $campRecompraId  = Catalogos::find()->select('id')->where(['tipo'=>'campaña','nombre'=>'Recompra'])->scalar();

        // Lista de campañas (para Retorno Extra)
        $campaniasCatalog = Catalogos::find()
            ->select(['id','nombre'])
            ->where(['tipo'=>'campaña'])
            ->orderBy('nombre')
            ->asArray()
            ->all();

        // Nombres de tablas / campos
        $v  = Ventas::tableName();
        $df = $this->ventasDateField;
        $af = $this->ventasAmountField;
        $sf = $this->ventasAsesorField;
        $mf = $this->ventasMedioField;
        $cf = $this->ventasCampField;

        // 1) Retorno Mensual (total por asesor en el mes)
        $mensualRows = (new Query())
            ->select(["$sf AS asesor_id", 'total' => "SUM(COALESCE($af,0))"])
            ->from($v)
            ->where(['between', $df, $start, $end])
            ->groupBy($sf)
            ->all();
        $mensualPorAsesor = [];
        foreach ($mensualRows as $r) {
            $aid = (int)($r['asesor_id'] ?? 0);
            if ($aid) $mensualPorAsesor[$aid] = round((float)$r['total'], 2);
        }

        // 2) Retorno Orgánico (campaña = Organica)
        $organicoPorAsesor = [];
        if ($campOrganicaId) {
            $rows = (new Query())
                ->select(["$sf AS asesor_id", 'total' => "SUM(COALESCE($af,0))"])
                ->from($v)
                ->where(['between', $df, $start, $end])
                ->andWhere([$cf => $campOrganicaId])
                ->groupBy($sf)
                ->all();
            foreach ($rows as $r) {
                $aid = (int)$r['asesor_id'];
                if ($aid) $organicoPorAsesor[$aid] = round((float)$r['total'], 2);
            }
        }

        // 3) Retorno Recompra (campaña = Recompra, según mock)
        $recompraPorAsesor = [];
        if ($campRecompraId) {
            $rows = (new Query())
                ->select(["$sf AS asesor_id", 'total' => "SUM(COALESCE($af,0))"])
                ->from($v)
                ->where(['between', $df, $start, $end])
                ->andWhere([$cf => $campRecompraId])
                ->groupBy($sf)
                ->all();
            foreach ($rows as $r) {
                $aid = (int)$r['asesor_id'];
                if ($aid) $recompraPorAsesor[$aid] = round((float)$r['total'], 2);
            }
        }

        // 4) Retorno Página Web (medio = Pagina Web)
        $webPorAsesor = [];
        if ($medioWebId) {
            $rows = (new Query())
                ->select(["$sf AS asesor_id", 'total' => "SUM(COALESCE($af,0))"])
                ->from($v)
                ->where(['between', $df, $start, $end])
                ->andWhere([$mf => $medioWebId])
                ->groupBy($sf)
                ->all();
            foreach ($rows as $r) {
                $aid = (int)$r['asesor_id'];
                if ($aid) $webPorAsesor[$aid] = round((float)$r['total'], 2);
            }
        }

        // 5) Desconocido = ventas que NO son web, NI organica, NI recompra
        $desconocidoPorAsesor = [];
        $whereNot = ['and', ['between', $df, $start, $end]];
        if ($medioWebId)     $whereNot[] = ['!=', $mf, $medioWebId];
        if ($campOrganicaId) $whereNot[] = ['!=', $cf, $campOrganicaId];
        if ($campRecompraId) $whereNot[] = ['!=', $cf, $campRecompraId];

        $rowsDes = (new Query())
            ->select(["$sf AS asesor_id", 'total' => "SUM(COALESCE($af,0))"])
            ->from($v)
            ->where($whereNot)
            ->groupBy($sf)
            ->all();
        foreach ($rowsDes as $r) {
            $aid = (int)$r['asesor_id'];
            if ($aid) $desconocidoPorAsesor[$aid] = round((float)$r['total'], 2);
        }

        return $this->render('index', [
            'month'                 => $month,
            'asesoresById'          => $asesoresById,
            'mensualPorAsesor'      => $mensualPorAsesor,
            'organicoPorAsesor'     => $organicoPorAsesor,
            'recompraPorAsesor'     => $recompraPorAsesor,
            'webPorAsesor'          => $webPorAsesor,
            'desconocidoPorAsesor'  => $desconocidoPorAsesor,
            'campaniasCatalog'      => $campaniasCatalog,
            'campOrganicaId'        => $campOrganicaId,
            'campRecompraId'        => $campRecompraId,
            'medioWebId'            => $medioWebId,
        ]);
    }

    // Retorno Extra (sumas por asesor para campañas seleccionadas)
    public function actionExtraTotales(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $month = Yii::$app->request->post('month');
        $campanas = Yii::$app->request->post('campanas', []);
        if (!$month || !is_array($campanas) || empty($campanas)) {
            return ['success'=>false, 'message'=>'Selecciona al menos una campaña'];
        }

        [$start, $end] = $this->monthRange($month);

        $v  = Ventas::tableName();
        $df = $this->ventasDateField;
        $af = $this->ventasAmountField;
        $sf = $this->ventasAsesorField;
        $cf = $this->ventasCampField;

        $rows = (new Query())
            ->select(["$sf AS asesor_id", 'total' => "SUM(COALESCE($af,0))"])
            ->from($v)
            ->where(['between', $df, $start, $end])
            ->andWhere(['in', $cf, array_map('intval', $campanas)])
            ->groupBy($sf)
            ->all();

        $porAsesor = [];
        $granTotal = 0.0;
        foreach ($rows as $r) {
            $aid = (int)$r['asesor_id'];
            $total = round((float)$r['total'], 2);
            if ($aid) {
                $porAsesor[$aid] = $total;
                $granTotal += $total;
            }
        }

        return ['success'=>true, 'porAsesor'=>$porAsesor, 'granTotal'=>round($granTotal,2)];
    }

    // Breakdown simple por asesor (para panel individual)
    public function actionBreakdownAsesor(int $asesorId, ?string $month = null): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$month) $month = Yii::$app->formatter->asDate('now','php:Y-m');
        [$start, $end] = $this->monthRange($month);

        $medioWebId     = Catalogos::find()->select('id')->where(['tipo'=>'medio','nombre'=>'Pagina Web'])->scalar();
        $campOrganicaId = Catalogos::find()->select('id')->where(['tipo'=>'campaña','nombre'=>'Organica'])->scalar();
        $campRecompraId = Catalogos::find()->select('id')->where(['tipo'=>'campaña','nombre'=>'Recompra'])->scalar();

        $v  = Ventas::tableName();
        $df = $this->ventasDateField;
        $af = $this->ventasAmountField;
        $sf = $this->ventasAsesorField;
        $mf = $this->ventasMedioField;
        $cf = $this->ventasCampField;

        $sumWhere = function($where) use($v,$df,$af,$sf,$start,$end){
            return (float)(new Query())->from($v)->where(['between',$df,$start,$end])->andWhere($where)->sum($af) ?: 0.0;
        };

        $res = [
            'organico'    => $campOrganicaId ? $sumWhere([$sf=>$asesorId, $cf=>$campOrganicaId]) : 0,
            'recompra'    => $campRecompraId ? $sumWhere([$sf=>$asesorId, $cf=>$campRecompraId]) : 0,
            'web'         => $medioWebId     ? $sumWhere([$sf=>$asesorId, $mf=>$medioWebId])     : 0,
            'desconocido' => 0,
        ];

        // Desconocido: no web, no organica, no recompra
        $whereNot = ['and', [$sf=>$asesorId], ['between', $df, $start, $end]];
        if ($medioWebId)     $whereNot[] = ['!=', $mf, $medioWebId];
        if ($campOrganicaId) $whereNot[] = ['!=', $cf, $campOrganicaId];
        if ($campRecompraId) $whereNot[] = ['!=', $cf, $campRecompraId];
        $res['desconocido'] = (float)(new Query())->from($v)->where($whereNot)->sum($af) ?: 0.0;

        foreach ($res as $k=>$vsum) $res[$k] = round($vsum, 2);

        return ['success'=>true, 'breakdown'=>$res];
    }

    private function monthRange(string $ym): array
    {
        $start = $ym . '-01 00:00:00';
        $end   = date('Y-m-t 23:59:59', strtotime($ym . '-01'));
        return [$start, $end];
    }
}