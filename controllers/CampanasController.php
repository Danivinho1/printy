<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\Html;

use app\models\Campanas;
use app\models\CampanasSearch;
use app\models\Catalogos;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

/**
 * Controlador para el módulo de Campañas (Marketing)
 */
class CampanasController extends Controller
{
    public function behaviors()
    {
        return [
            // Restringe el acceso a usuarios autenticados e incluye export-excel y endpoints AJAX
            'access' => [
                'class' => AccessControl::class,
                'only' => [
                    'index', 'create', 'update', 'delete', 'view',
                    'get-select-options', 'update-field', 'update-inline',
                    'export-excel'
                ],
                'rules' => [
                    ['allow' => true, 'roles' => ['@']],
                ],
            ],
            // Reglas de verbo HTTP
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete'             => ['POST'],
                    'update-field'       => ['POST'],
                    'update-inline'      => ['POST'],
                    'get-select-options' => ['GET'],
                    'export-excel'       => ['GET'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel  = new CampanasSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // Modelo nuevo para el modal (como en Ventas)
        $modeloNuevo = new Campanas();
        if ($modeloNuevo->isNewRecord && empty($modeloNuevo->analisis)) {
            $modeloNuevo->analisis = 'Analizar';
        }

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
            'modeloNuevo'  => $modeloNuevo,
        ]);
    }

    public function actionCreate()
    {
        $model = new Campanas();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Campaña creada correctamente.');
            return $this->redirect(['index']);
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('_form', ['model' => $model]);
        }

        return $this->render('create', ['model' => $model]);
    }

    /** ===============================
     *  VISTA DETALLADA DE UNA CAMPAÑA
     * =============================== */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /** ===============================
     *  ACTUALIZAR UNA CAMPAÑA
     * =============================== */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /** ===============================
     *  ELIMINAR UNA CAMPAÑA
     * =============================== */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }

    /** ===============================
     *  EXPORTAR LISTADO (FILTRADO) A EXCEL
     *  Nota: se genera en memoria y se envía; no crea archivos temporales en disco.
     * =============================== */
    public function actionExportExcel()
    {
        // Usa los mismos filtros que el index para exportar exactamente lo que ves
        $searchModel  = new CampanasSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination = false;
        $rows = $dataProvider->getModels();

        // Mapear IDs a nombres para columnas de catálogo
        $idsTipo   = array_values(array_filter(array_unique(array_map(static fn($m) => (int)($m->campaña_id ?? 0), $rows))));
        $idsAsesor = array_values(array_filter(array_unique(array_map(static fn($m) => (int)($m->asesor_id ?? 0), $rows))));
        $tipos  = empty($idsTipo)   ? [] : Catalogos::find()->select(['nombre','id'])->where(['id'=>$idsTipo])->indexBy('id')->column();
        $asesor = empty($idsAsesor) ? [] : Catalogos::find()->select(['nombre','id'])->where(['id'=>$idsAsesor])->indexBy('id')->column();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Campañas');

        // Encabezados
        $headers = [
            'A1' => 'ID',
            'B1' => 'Nombre',
            'C1' => 'Tipo campaña',
            'D1' => 'Asesor',
            'E1' => 'Inversión',
            'F1' => 'Presupuesto',
            'G1' => 'Retorno',
            'H1' => 'Mensajes',
            'I1' => 'Estado',
            'J1' => 'Creada',
            'K1' => 'Actualizada',
            'L1' => 'Mensaje predeterminado',
        ];
        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }
        // Estilo encabezado
        $sheet->getStyle('A1:L1')->getFont()->setBold(true);
        $sheet->getStyle('A1:L1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFEFF4FA');
        $sheet->freezePane('A2');

        // Filas
        $r = 2;
        foreach ($rows as $m) {
            $sheet->setCellValueExplicit("A{$r}", (int)$m->id, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $sheet->setCellValue("B{$r}", (string)($m->nombre ?? ''));
            $sheet->setCellValue("C{$r}", (string)($tipos[$m->campaña_id] ?? ''));
            $sheet->setCellValue("D{$r}", (string)($asesor[$m->asesor_id] ?? ''));
            $sheet->setCellValue("E{$r}", (float)($m->inversion ?? 0));
            $sheet->setCellValue("F{$r}", (float)($m->presupuesto ?? 0));
            $sheet->setCellValue("G{$r}", (float)($m->retorno ?? 0));
            $sheet->setCellValue("H{$r}", (int)($m->mensajes ?? 0));
            $sheet->setCellValue("I{$r}", (string)($m->analisis ?? ''));
            $created = $m->created_at ? date('Y-m-d H:i', strtotime($m->created_at)) : '';
            $updated = $m->updated_at ? date('Y-m-d H:i', strtotime($m->updated_at)) : '';
            $sheet->setCellValue("J{$r}", $created);
            $sheet->setCellValue("K{$r}", $updated);
            $sheet->setCellValue("L{$r}", (string)($m->mensaje_predeterminado ?? ''));
            $r++;
        }

        // Formatos numéricos
        $currencyFormat = '"$"#,##0.00_-';
        $sheet->getStyle("E2:E{$r}")->getNumberFormat()->setFormatCode($currencyFormat);
        $sheet->getStyle("F2:F{$r}")->getNumberFormat()->setFormatCode($currencyFormat);
        $sheet->getStyle("G2:G{$r}")->getNumberFormat()->setFormatCode($currencyFormat);
        $sheet->getStyle("H2:H{$r}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER);

        // Auto-ajuste de columnas
        foreach (range('A','L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Enviar archivo: streaming en memoria (evita tempnam y Notices)
        $filename = 'campanas_' . date('Ymd_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        // Opción A: stream en memoria usando output buffer (simple y compatible)
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return Yii::$app->response->sendContentAsFile(
            $content,
            $filename,
            [
                'mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'inline'   => false,
            ]
        );
    }

    /** ===============================
     *  OPCIONES PARA SELECTS (AJAX)
     * =============================== */
    public function actionGetSelectOptions($field)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($field === 'asesor_id') {
            $rows = Catalogos::find()
                ->select(['id', 'nombre'])
                ->where(['tipo' => 'asesor'])
                ->orderBy('nombre')
                ->asArray()
                ->all();

            $options = array_map(static fn($r) => ['value' => (string)$r['id'], 'text' => $r['nombre']], $rows);
            return ['options' => $options];
        }

        if ($field === 'campaña_id') {
            $rows = Catalogos::find()
                ->select(['id', 'nombre'])
                ->where(['tipo' => 'campaña'])
                ->orderBy('nombre')
                ->asArray()
                ->all();

            $options = array_map(static fn($r) => ['value' => (string)$r['id'], 'text' => $r['nombre']], $rows);
            return ['options' => $options];
        }

        if ($field === 'analisis') {
            $opts = Campanas::optsAnalisis();
            $options = [];
            foreach ($opts as $value => $text) {
                $options[] = ['value' => $value, 'text' => $text];
            }
            return ['options' => $options];
        }

        return ['options' => []];
    }

    /** ===============================
     *  GUARDADO EN TIEMPO REAL (AJAX)
     * =============================== */
    public function actionUpdateField()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $id    = Yii::$app->request->post('id');
        $field = Yii::$app->request->post('field', Yii::$app->request->post('attr'));
        $value = Yii::$app->request->post('value');

        if (!$id || !$field) {
            return ['success' => false, 'message' => 'Faltan parámetros'];
        }

        $model = Campanas::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('Registro no encontrado');
        }

        // Campos permitidos
        $allowed = [
            'nombre',
            'asesor_id', 'campaña_id',
            'inversion', 'mensajes', 'retorno',
            'analisis',
            'mensaje_predeterminado',
        ];
        if (!in_array($field, $allowed, true)) {
            return ['success' => false, 'message' => 'Campo no permitido'];
        }

        try {
            switch ($field) {
                case 'nombre':
                    $model->nombre = (string)$value;
                    break;

                case 'asesor_id':
                case 'campaña_id':
                    $model->$field = ($value === '' || $value === null) ? null : (int)$value;
                    break;

                case 'inversion':
                case 'retorno':
                    $model->$field = ($value === '' || $value === null) ? 0 : (float)$value;
                    break;

                case 'mensajes':
                    $model->$field = ($value === '' || $value === null) ? 0 : (int)$value;
                    break;

                case 'analisis':
                    $opts = Campanas::optsAnalisis();
                    if (!isset($opts[$value])) {
                        return ['success' => false, 'message' => 'Valor de análisis inválido'];
                    }
                    $model->analisis = $value;
                    break;

                case 'mensaje_predeterminado':
                    $model->mensaje_predeterminado = (string)$value;
                    break;
            }

            if (!$model->save()) {
                return ['success' => false, 'message' => 'No se pudo guardar', 'errors' => $model->getErrors()];
            }

            $newContent = $this->renderCellContent($model, $field);

            return [
                'success'    => true,
                'message'    => 'Guardado correctamente',
                'newContent' => $newContent,
            ];
        } catch (\Throwable $e) {
            Yii::error($e->getMessage() . "\n" . $e->getTraceAsString(), __METHOD__);
            return ['success' => false, 'message' => 'Error del servidor'];
        }
    }

    /** ===============================
     *  GUARDADO EN TIEMPO REAL (AJAX) LEGADO
     * =============================== */
    public function actionUpdateInline(): array
    {
        return $this->actionUpdateField();
    }

    /** ===============================
     *  AUXILIARES
     * =============================== */
    protected function findModel($id)
    {
        if (($model = Campanas::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('La campaña solicitada no existe.');
    }

    protected function renderCellContent(Campanas $model, string $field): string
    {
        switch ($field) {
            case 'asesor_id':
            case 'campaña_id':
                $nombre = Catalogos::find()->select('nombre')->where(['id' => $model->$field])->scalar();
                $nombre = $nombre ?: 'Sin asignar';
                return '<span class="badge bg-light text-dark">' . Html::encode($nombre) . '</span>';

            case 'inversion':
            case 'retorno':
                $num = (float)$model->$field;
                return '$' . number_format($num, 2);

            case 'mensajes':
                return (string)((int)$model->mensajes);

            case 'analisis':
                $texto = Campanas::optsAnalisis()[$model->analisis] ?? $model->analisis;
                return '<span class="badge bg-secondary">' . Html::encode($texto) . '</span>';

            case 'mensaje_predeterminado':
                return Html::encode(mb_strimwidth((string)$model->mensaje_predeterminado, 0, 60, '…', 'UTF-8'));
        }
        return '';
    }
}