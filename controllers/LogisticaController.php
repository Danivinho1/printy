<?php

namespace app\controllers;

use app\models\Logistica;
use app\models\LogisticaSearch;
use app\models\Ventas;
use app\models\Diseno;
use app\models\Produccion;
use app\models\Catalogos;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\ArrayDataProvider;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Yii;

/**
 * LogisticaController implements the CRUD actions for Logistica model.
 */
class LogisticaController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Logistica models.
     *
     * @return string
     */
    public function actionIndex()
{
    // 🔹 Crear instancia del search model
    $searchModel = new LogisticaSearch();

    // 🔹 Cargar parámetros GET
    $params = Yii::$app->request->queryParams;

    // 🔹 Obtener el data provider usando el search model
    $dataProvider = $searchModel->search($params);
    $metrics = $this->calculateMetrics();

    // 🔹 Renderizar la vista y pasar searchModel + dataProvider
    return $this->render('index', [
        'searchModel' => $searchModel,
        'dataProvider' => $dataProvider,
        'metrics' => $metrics,
    ]);
}


    /**
     * Displays a single Logistica model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Logistica model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Logistica();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Logistica model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Logistica model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Logistica model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Logistica the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Logistica::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionUpdateField()
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    if (!Yii::$app->request->isAjax || !Yii::$app->request->isPost) {
        return ['success' => false, 'message' => 'Solicitud inválida'];
    }

    $id = Yii::$app->request->post('id');
    $field = Yii::$app->request->post('field');
    $value = Yii::$app->request->post('value');

    if (!$id || !$field) {
        return ['success' => false, 'message' => 'Parámetros faltantes'];
    }

    $model = Logistica::findOne($id);
    if (!$model) {
        return ['success' => false, 'message' => 'Registro no encontrado'];
    }

    $allowedFields = ['total', 'anticipo', 'restante'];
    if (!in_array($field, $allowedFields)) {
        return ['success' => false, 'message' => 'Campo no editable'];
    }

    try {
        // Convertir y validar valores
        switch ($field) {
            case 'total':
            case 'anticipo':
            case 'restante':
                $value = (float)$value;
                if ($value < 0) return ['success' => false, 'message' => 'Los precios no pueden ser negativos'];
                break;
        }

        $model->$field = $value;

        if (!$model->validate([$field])) {
            $errors = $model->getFirstErrors();
            return ['success' => false, 'message' => reset($errors) ?: 'Error de validación'];
        }

        $model->save(false, [$field]);
        $model->refresh();

        // Formatear contenido para mostrar en tabla
        switch ($field) {
            case 'total':
                $newContent = '<span class="badge" style="color:green; font-weight:bold;">$' . number_format($value, 2) . '</span>';
                break;
            case 'anticipo':
                $newContent = '<span class="badge" style="color:orange; font-weight:bold;">$' . number_format($value, 2) . '</span>';
                break;
            case 'restante':
                $newContent = '<span class="badge" style="color:red; font-weight:bold;">$' . number_format($value, 2) . '</span>';
                break;
            default:
                $newContent = $value;
        }

        // 🚀 Sincronizar Restante con Ventas si se editó total o anticipo
        if (in_array($field, ['total', 'anticipo'])) {
            $venta = Ventas::findOne($model->venta_id);
            if ($venta) {
                $venta->precio_total = $model->total;
                $venta->anticipo = $model->anticipo;
                $venta->restante = max(0, $venta->precio_total - $venta->anticipo);
                $venta->save(false, ['precio_total', 'anticipo', 'restante']);
            }
        }

        return [
            'success' => true,
            'message' => 'Campo actualizado correctamente',
            'newContent' => $newContent,
        ];

    } catch (\Exception $e) {
        Yii::error("Excepción en updateField (Logistica): " . $e->getMessage(), __METHOD__);
        return ['success' => false, 'message' => 'Error interno del servidor'];
    }
}


    public function actionActualizarRestante()
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    if (!Yii::$app->request->isAjax || !Yii::$app->request->isPost) {
        return ['success' => false, 'message' => 'Solicitud inválida'];
    }

    $id = Yii::$app->request->post('id');

    if (!$id) {
        return ['success' => false, 'message' => 'ID faltante'];
    }

    $model = Ventas::findOne($id);
    if (!$model) {
        return ['success' => false, 'message' => 'Registro no encontrado'];
    }

    // Calcular restante
    $precioTotal = $model->precio_total ?? 0;
    $anticipo = $model->anticipo ?? 0;
    $restante = max(0, $precioTotal - $anticipo);
    $model->restante = $restante;

    if (!$model->save(false, ['restante'])) {
        return ['success' => false, 'message' => 'No se pudo actualizar restante'];
    }

    // Generar HTML con formato de badge
    $restanteHtml = '<span class="badge" style="color:red; font-weight:bold;">$' . number_format($restante, 2) . '</span>';

    return [
        'success' => true,
        'restanteHtml' => $restanteHtml
    ];
}

   public function actionAtencionClientes()
{
    // Obtener los registros de ventas
    $ventas = Ventas::find()->all();

    // Construir un array combinando datos de ventas, diseño y producción
    $data = [];
    foreach ($ventas as $venta) {
        $diseno = Diseno::find()->where(['venta_id'=>$venta->id])->one();
        $produccion = Produccion::find()->where(['venta_id'=>$venta->id])->one();
        $logistica = Logistica::find()->where(['venta_id'=>$venta->id])->one();

        // Obtener el nombre del envío desde el catálogo
        $envioNombre = 'Pendiente';
        if ($logistica && $logistica->estatus_envio_id) {
            $catalogoEnvio = \app\models\Catalogos::findOne($logistica->estatus_envio_id);
            $envioNombre = $catalogoEnvio ? $catalogoEnvio->nombre : 'Pendiente';
        }

        // Obtener el nombre del estatus de pago desde el catálogo (también de logística)
        $pagoNombre = 'Pendiente';
        if ($logistica && $logistica->estatus_pago_id) {
            $catalogoPago = \app\models\Catalogos::findOne($logistica->estatus_pago_id);
            $pagoNombre = $catalogoPago ? $catalogoPago->nombre : 'Pendiente';
        }

        // CORREGIDO: Obtener el estatus del diseño desde la tabla catalogos
        $disenoEstatusNombre = 'Pendiente';
        $disenoEstatusId = null;
        if ($diseno && $diseno->estatus_id) {
            $catalogoEstatusDiseno = \app\models\Catalogos::findOne($diseno->estatus_id);
            if ($catalogoEstatusDiseno) {
                $disenoEstatusNombre = $catalogoEstatusDiseno->nombre;
                $disenoEstatusId = $diseno->estatus_id;
            }
        }

        $data[] = [
            'id' => $venta->id,
            'tipo_letrero' => $venta->tipoLetrero->nombre ?? 'No definido',
            'nombre_letrero' => $venta->nombre_letrero,
            'telefono' => $venta->telefono,
            'contacto_cliente' => $diseno->contacto_cliente_id ?? 0,
            'vectorizado' => $diseno->vectorizado_id ?? 0,
            'estatus' => $venta->estatus->nombre ?? 'Pendiente',
            'fecha_confirmacion' => $diseno->fecha_confirmacion,
            'diseno_impresion' => $produccion->diseno_impresion ?? 0,
            'corte_listo' => $produccion->corte_listo ?? 0,
            'fabricacion_listo' => $produccion->fabricacion_listo ?? 0,
            'empaquetado_nombre' => $produccion->empaquetado->nombre ?? 'Pendiente',
            'revision_calidad' => $logistica->revision_calidad ?? 0,
            'restante' => $venta->restante ?? 0,
            // Datos de envío
            'estatus_envio_id' => $logistica->estatus_envio_id ?? null,
            'envio_nombre' => $envioNombre,
            // Datos de pago (también de logística)
            'estatus_pago_id' => $logistica->estatus_pago_id ?? null,
            'pago_nombre' => $pagoNombre,
            // AGREGADO: Datos del estatus del diseño
            'diseno_estatus_id' => $disenoEstatusId,
            'diseno_estatus_nombre' => $disenoEstatusNombre,
        ];
    }

    // Crear un DataProvider para la vista
    $dataProvider = new ArrayDataProvider([
        'allModels' => $data,
        'pagination' => [
            'pageSize' => 20,
        ],
    ]);

    return $this->render('atencion-clientes', [
        'dataProvider' => $dataProvider,
    ]);
}

    public function actionToggleField()
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    $id = Yii::$app->request->post('id');
    $field = Yii::$app->request->post('field');
    $value = Yii::$app->request->post('value');

    $model = Logistica::findOne($id);
    if($model && $field == 'estatus_envio_id'){
        $model->$field = $value;
        if($model->save(false)){
            return [
                'success' => true,
                'nombre' => $model->envio ? $model->envio->nombre : 'Desconocido'
            ];
        }
    }
    return ['success'=>false];
}
public function actionUpdateEnvio()
{
    try {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        // Validar que sea POST
        if (!Yii::$app->request->isPost) {
            return [
                'success' => false,
                'message' => 'Método no permitido'
            ];
        }

        $ventaId = Yii::$app->request->post('id');
        $field = Yii::$app->request->post('field');
        $value = Yii::$app->request->post('value');

        // Validar datos requeridos
        if (empty($ventaId) || empty($field) || empty($value)) {
            return [
                'success' => false,
                'message' => 'Datos incompletos',
                'debug' => [
                    'ventaId' => $ventaId,
                    'field' => $field,
                    'value' => $value
                ]
            ];
        }

        // Validar que el campo sea el correcto
        if ($field !== 'estatus_envio_id') {
            return [
                'success' => false,
                'message' => 'Campo no válido: ' . $field
            ];
        }

        // Buscar logística por venta_id
        $logistica = Logistica::find()->where(['venta_id' => $ventaId])->one();
        
        if (!$logistica) {
            // Crear nuevo registro de logística si no existe
            $logistica = new Logistica();
            $logistica->venta_id = $ventaId;
        }

        // Actualizar el campo
        $logistica->estatus_envio_id = $value;
        
        if ($logistica->save()) {
            // Obtener el nuevo nombre para la respuesta
            $catalogoEnvio = \app\models\Catalogos::findOne($value);
            $nuevoNombre = $catalogoEnvio ? $catalogoEnvio->nombre : 'Desconocido';
            
            return [
                'success' => true,
                'nombre' => $nuevoNombre,
                'message' => 'Actualizado correctamente'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al guardar',
                'errors' => $logistica->getErrors()
            ];
        }

    } catch (\Exception $e) {
        // Log del error para debug
        Yii::error('Error en actionUpdateEnvio: ' . $e->getMessage(), __METHOD__);
        
        return [
            'success' => false,
            'message' => 'Error interno del servidor',
            'debug' => YII_DEBUG ? $e->getMessage() : null
        ];
    }
}
public function actionUpdatePago()
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    $id = Yii::$app->request->post('id');
    $field = Yii::$app->request->post('field');
    $value = Yii::$app->request->post('value');

    try {
        // Buscar el registro de logística por venta_id
        $logistica = Logistica::find()->where(['venta_id' => $id])->one();

        // Si no existe, crear uno nuevo
        if (!$logistica) {
            $logistica = new Logistica();
            $logistica->venta_id = $id;
        }

        // Solo permitimos actualizar estatus_pago_id
        if ($field === 'estatus_pago_id') {
            $logistica->estatus_pago_id = $value;

            // Guardar sin validar otros campos
            if ($logistica->save(false)) {
                $catalogo = \app\models\Catalogos::findOne($value);
                $nuevoNombre = $catalogo ? $catalogo->nombre : 'Desconocido';

                return [
                    'success' => true,
                    'nombre' => $nuevoNombre
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al guardar el estatus de pago',
                    'errors' => $logistica->getErrors()
                ];
            }
        }

        return [
            'success' => false,
            'message' => 'Campo no válido: ' . $field
        ];

    } catch (\Exception $e) {
        return [
            'success' => false,
            'message' => 'Error del servidor: ' . $e->getMessage()
        ];
    }
}

public function actionExportExcel()
{
    $logisticas = Logistica::find()->all(); // Cambia por tu modelo de Logística
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Título
    $mes = date('F Y');
    $sheet->setCellValue('A1', "Logística del mes: $mes");
    $sheet->mergeCells('A1:K1'); // Ajustado para las columnas reales
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Encabezados basados en tu GridView real
    $headers = [
        'ID', 'Tipo Letrero', 'Nombre Letrero', 'Fecha Entrega', 'Extras', 
        'Teléfono', 'Total', 'Anticipo', 'Restante', 'Estatus Pago', 'Estatus Envío'
    ];
    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col.'2', $header);
        $sheet->getStyle($col.'2')->getFont()->setBold(true);
        $col++;
    }

    // Función de color igual que generarColorUnico
    $getBadgeColor = function($nombre){
        if (!$nombre) return ['bg'=>'CCCCCC','text'=>'000000'];
        $hash = substr(md5($nombre),0,6);
        return ['bg'=>$hash,'text'=>'FFFFFF'];
    };

    $row = 3;
    foreach($logisticas as $logistica){
        $sheet->setCellValue('A'.$row, $logistica->id);

        // Tipo Letrero
        $tipoLetrero = $logistica->tipoLetrero->nombre ?? 'No definido';
        $color = $getBadgeColor($tipoLetrero);
        $sheet->setCellValue('B'.$row, $tipoLetrero);
        if ($tipoLetrero !== 'No definido') {
            $sheet->getStyle('B'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($color['bg']);
            $sheet->getStyle('B'.$row)->getFont()->getColor()->setRGB($color['text']);
        } else {
            $sheet->getStyle('B'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('6c757d');
            $sheet->getStyle('B'.$row)->getFont()->getColor()->setRGB('FFFFFF');
        }

        // Nombre Letrero
        $sheet->setCellValue('C'.$row, $logistica->nombre_letrero);

        // Fecha Entrega
        if ($logistica->venta && $logistica->venta->fecha_entrega) {
            $fechaEntrega = Yii::$app->formatter->asDate($logistica->venta->fecha_entrega, 'php:d/m/Y');
            $sheet->setCellValue('D'.$row, $fechaEntrega);
        } else {
            $sheet->setCellValue('D'.$row, 'No definida');
            $sheet->getStyle('D'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('6c757d');
            $sheet->getStyle('D'.$row)->getFont()->getColor()->setRGB('FFFFFF');
        }

        // Extras
        $extras = $logistica->venta ? $logistica->venta->extras : [];
        $extrasText = '';
        if (!empty($extras)) {
            $extrasNames = [];
            foreach ($extras as $extra) {
                $extrasNames[] = $extra->nombre;
            }
            $extrasText = implode(', ', $extrasNames);
        } else {
            $extrasText = 'Ninguno';
        }
        $sheet->setCellValue('E'.$row, $extrasText);
        
        // Si no hay extras, colorear como "Ninguno"
        if ($extrasText === 'Ninguno') {
            $sheet->getStyle('E'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('f8f9fa');
            $sheet->getStyle('E'.$row)->getFont()->getColor()->setRGB('212529');
        }

        // Teléfono
        $sheet->setCellValue('F'.$row, $logistica->telefono);

        // Total
        $total = $logistica->total ?? 0;
        $sheet->setCellValue('G'.$row, '$' . number_format($total, 2));
        $sheet->getStyle('G'.$row)->getFont()->setBold(true);
        $sheet->getStyle('G'.$row)->getFont()->getColor()->setRGB('28a745'); // Verde

        // Anticipo
        $anticipo = $logistica->anticipo ?? 0;
        $sheet->setCellValue('H'.$row, '$' . number_format($anticipo, 2));
        $sheet->getStyle('H'.$row)->getFont()->setBold(true);
        $sheet->getStyle('H'.$row)->getFont()->getColor()->setRGB('ffc107'); // Naranja

        // Restante
        $restante = $logistica->restante ?? 0;
        $sheet->setCellValue('I'.$row, '$' . number_format($restante, 2));
        $sheet->getStyle('I'.$row)->getFont()->setBold(true);
        $sheet->getStyle('I'.$row)->getFont()->getColor()->setRGB('dc3545'); // Rojo

        // Estatus Pago
        $estatusPago = $logistica->estatusPago->nombre ?? 'Pendiente';
        $sheet->setCellValue('J'.$row, $estatusPago);
        $colorPago = strtolower($estatusPago) === 'liquidado' ? '28a745' : 'ffc107';
        $sheet->getStyle('J'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colorPago);
        $sheet->getStyle('J'.$row)->getFont()->getColor()->setRGB('FFFFFF');

        // Estatus Envío
        $estatusEnvio = $logistica->envio->nombre ?? 'Pendiente';
        $sheet->setCellValue('K'.$row, $estatusEnvio);
        $colorEnvio = strtolower($estatusEnvio) === 'enviado' ? '28a745' : 'dc3545';
        $sheet->getStyle('K'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colorEnvio);
        $sheet->getStyle('K'.$row)->getFont()->getColor()->setRGB('FFFFFF');

        $row++;
    }

    // Auto-ajustar columnas
    foreach(range('A','K') as $colID){
        $sheet->getColumnDimension($colID)->setAutoSize(true);
    }

    $writer = new Xlsx($spreadsheet);
    $fileName = "logistica_$mes.xlsx";

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment;filename=\"$fileName\"");
    header('Cache-Control: max-age=0');

    $writer->save('php://output');
    exit;
}
public function actionEmpaquetado()
{
    $searchModel = new LogisticaSearch();

    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

    $dataProvider->query->joinWith('estatusPago'); // alias: estatusPago
    $dataProvider->query->andWhere(['estatusPago.nombre' => 'Liquidado']); // filtrar por nombre

    return $this->render('empaquetado', [
        'dataProvider' => $dataProvider,
        'searchModel' => $searchModel,
    ]);
}


public function actionToggleEmpaquetado()
{
    $id = Yii::$app->request->post('id');
    $field = Yii::$app->request->post('field');

    $model = Logistica::findOne($id);
    if (!$model) {
        return $this->asJson(['success' => false, 'error' => 'Registro no encontrado']);
    }

    // Campos permitidos para toggles - agrega los campos que necesites
    $allowedFields = ['revision_calidad']; // Agrega otros campos si los necesitas
    if (!in_array($field, $allowedFields)) {
        return $this->asJson(['success' => false, 'error' => 'Campo inválido']);
    }

    // Inicializar si NULL
    if ($model->$field === null) {
        $model->$field = 0;
    }

    // Alternar valor
    $model->$field = $model->$field ? 0 : 1;

    if ($model->save(false)) {
        $response = [
            'success' => true, 
            'value' => (int)$model->$field
        ];

        // Si necesitas regenerar el badge HTML de días de empaquetado
        if ($field === 'revision_calidad') {
            $response['badgeHtml'] = $this->generateDiasEmpaquetadoBadge($model);
        }

        return $this->asJson($response);
    } else {
        return $this->asJson(['success' => false, 'error' => 'No se pudo guardar']);
    }
}

// Método auxiliar para generar el badge de días
private function generateDiasEmpaquetadoBadge($model)
{
    $hoy = new \DateTime();
    $fechaLiquidado = $model->fecha_pago_liquidado ? new \DateTime($model->fecha_pago_liquidado) : null;

    if(!$fechaLiquidado){
        return '<span class="badge bg-secondary">Fecha de pago no definida</span>';
    }
    
    $fechaLimite = clone $fechaLiquidado;
    $fechaLimite->modify('+1 day');

    if($model->revision_calidad == 1){
        $textoBadge = $hoy <= $fechaLimite ? 'Empaquetado a tiempo' : 'Empaquetado con retraso';
        $colorBadge = $hoy <= $fechaLimite ? '#28a745' : '#dc3545';
        return "<span class='badge' style='background-color:{$colorBadge};color:white;'>{$textoBadge}</span>";
    } else {
        $diff = $hoy <= $fechaLimite ? $hoy->diff($fechaLimite)->days + 1 : $hoy->diff($fechaLimite)->days + 1;
        $textoBadge = $hoy <= $fechaLimite ? "Falta {$diff} día(s) para empaquetar" : "{$diff} día(s) de retraso";
        $colorBadge = $hoy <= $fechaLimite ? '#28a745' : '#dc3545';
        return "<span class='badge' style='background-color:{$colorBadge};color:white;'>{$textoBadge}</span>";
    }
}
/**
 * Acción para registrar retrabajo - LogisticaController
 */
/**
 * Acción para registrar retrabajo - LogisticaController
 */
/**
 * Cambia la entrega a "Retrabajo Urgente"
 */
private function cambiarEntregaARetrabajoUrgente($venta)
{
    if (!$venta) return;
    
    // Buscar o crear el tipo de entrega "Retrabajo Urgente"
    $entregaRetrabajo = $this->obtenerEntregaRetrabajoUrgente();
    
    if ($entregaRetrabajo && $venta->hasAttribute('entrega_id')) {
        $venta->entrega_id = $entregaRetrabajo->id;
    }
}

/**
 * Obtiene o crea el tipo de entrega "Retrabajo Urgente"
 */
private function obtenerEntregaRetrabajoUrgente()
{
    try {
        // Buscar usando el modelo Catalogos
        $entregaRetrabajo = Catalogos::find()
            ->where([
                'tipo' => 'entrega',
                'nombre' => 'Urgente'
            ])
            ->one();
        
        if (!$entregaRetrabajo) {
            // Crear nuevo registro usando el modelo
            $entregaRetrabajo = new Catalogos();
            $entregaRetrabajo->tipo = 'entrega';
            $entregaRetrabajo->nombre = 'Urgente';
            $entregaRetrabajo->descripcion = 'Entrega urgente por retrabajo - Máxima prioridad';
            
            // Agregar campos adicionales si existen en tu tabla
            if ($entregaRetrabajo->hasAttribute('activo')) {
                $entregaRetrabajo->activo = 1;
            }
            if ($entregaRetrabajo->hasAttribute('created_at')) {
                $entregaRetrabajo->created_at = date('Y-m-d H:i:s');
            }
            
            if (!$entregaRetrabajo->save(false)) {
                \Yii::error("Error guardando entrega de retrabajo", 'retrabajo');
                return null;
            }
        }
        
        return $entregaRetrabajo;
        
    } catch (\Exception $e) {
        \Yii::error("Error obteniendo/creando entrega de retrabajo: " . $e->getMessage(), 'retrabajo');
        return null;
    }
}

/**
 * Acción para registrar retrabajo - LogisticaController
 */
public function actionRegistrarRetrabajo()
{
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    
    if (!\Yii::$app->request->isPost) {
        return ['success' => false, 'error' => 'Método no permitido'];
    }
    
    try {
        $id = \Yii::$app->request->post('id');
        $areaError = \Yii::$app->request->post('area_error');
        $observaciones = \Yii::$app->request->post('observaciones');
        
        // Validar datos requeridos
        if (empty($id) || empty($areaError)) {
            return ['success' => false, 'error' => 'Datos incompletos'];
        }
        
        // Validar área de error
        $areasPermitidas = ['impresion', 'corte', 'fabricacion'];
        if (!in_array($areaError, $areasPermitidas)) {
            return ['success' => false, 'error' => 'Área de error inválida'];
        }
        
        // Buscar el modelo Logistica
        \Yii::info("Buscando logística con ID: $id", 'debug');
        
        $model = Logistica::findOne($id);
        
        if (!$model) {
            return ['success' => false, 'error' => 'Registro de logística no encontrado con ID: ' . $id];
        }
        
        // Obtener la Venta relacionada
        $venta = null;
        if ($model->hasAttribute('venta_id') && $model->venta_id) {
            $venta = Ventas::findOne($model->venta_id);
        } else if ($model->hasMethod('getVenta')) {
            $venta = $model->venta; // usando relación
        }
        
        if (!$venta) {
            return ['success' => false, 'error' => 'No se encontró la venta relacionada'];
        }
        
        // Obtener la Producción a través de la Venta
        $produccion = null;
        if ($venta->hasMethod('getProduccion')) {
            $produccion = $venta->produccion; // usando relación
        } else {
            // Si no hay relación definida, buscar por venta_id
            $produccion = Produccion::find()->where(['venta_id' => $venta->id])->one();
        }
        
        if (!$produccion) {
            return ['success' => false, 'error' => 'No se encontró la producción relacionada a la venta ID: ' . $venta->id];
        }
        
        // Iniciar transacción
        $transaction = \Yii::$app->db->beginTransaction();
        
        try {
            // Guardar información del retrabajo en Logistica
            if ($model->hasAttribute('area_retrabajo')) {
                $model->area_retrabajo = $areaError;
            }
            if ($model->hasAttribute('observaciones_retrabajo')) {
                $model->observaciones_retrabajo = $observaciones;
            }
            if ($model->hasAttribute('fecha_retrabajo')) {
                $model->fecha_retrabajo = date('Y-m-d H:i:s');
            }
            
            // Desactivar toggles en el modelo de Producción
            $this->desactivarTogglesPorArea($produccion, $areaError);
            
            // Cambiar la entrega a "Retrabajo Urgente"
            $this->cambiarEntregaARetrabajoUrgente($venta);
            
            // Guardar ambos modelos
            $logisticaGuardado = $model->save(false);
            $produccionGuardado = $produccion->save(false);
            $ventaGuardada = $venta->save(false);
            
            if ($logisticaGuardado && $produccionGuardado && $ventaGuardada) {
                $transaction->commit();
                
                return [
                    'success' => true,
                    'message' => 'Retrabajo registrado correctamente',
                    'area' => $areaError,
                    'toggles_desactivados' => $this->getToggleDesactivados($areaError)
                ];
            } else {
                $transaction->rollBack();
                return ['success' => false, 'error' => 'Error al guardar los cambios'];
            }
            
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
        
    } catch (\Exception $e) {
        \Yii::error("Error en retrabajo: " . $e->getMessage(), 'retrabajo');
        return ['success' => false, 'error' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Desactiva los toggles según el área del error
 * Trabaja sobre el modelo de Producción
 */
private function desactivarTogglesPorArea($produccion, $areaError)
{
    if (!$produccion) return;
    
    switch ($areaError) {
        case 'impresion':
            // Si el error es en impresión, desmarcar TODOS los procesos
            if ($produccion->hasAttribute('diseno_impresion')) $produccion->diseno_impresion = 0;
            if ($produccion->hasAttribute('corte_listo')) $produccion->corte_listo = 0;
            if ($produccion->hasAttribute('fabricacion_listo')) $produccion->fabricacion_listo = 0;
            break;
            
        case 'corte':
            // Si el error es en corte, desmarcar corte y fabricación
            if ($produccion->hasAttribute('corte_listo')) $produccion->corte_listo = 0;
            if ($produccion->hasAttribute('fabricacion_listo')) $produccion->fabricacion_listo = 0;
            break;
            
        case 'fabricacion':
            // Si el error es en fabricación, solo desmarcar fabricación
            if ($produccion->hasAttribute('fabricacion_listo')) $produccion->fabricacion_listo = 0;
            break;
    }
}

/**
 * Obtiene la lista de toggles desactivados
 */
private function getToggleDesactivados($areaError)
{
    switch ($areaError) {
        case 'impresion':
            return ['diseno_impresion', 'corte_listo', 'fabricacion_listo'];
        case 'corte':
            return ['corte_listo', 'fabricacion_listo'];
        case 'fabricacion':
            return ['fabricacion_listo'];
        default:
            return [];
    }
}

public function actionDashboard()
{
    // Métricas básicas (estas deberían funcionar siempre)
    $metrics = [
        'total_anticipos' => Logistica::find()->sum('anticipo') ?? 0,
        'total_restantes' => Logistica::find()->sum('restante') ?? 0,
    ];
    
    // Obtener todos los registros de una vez para procesar
    $logisticas = Logistica::find()->with(['estatusPago', 'envio'])->all();
    
    $liquidados = 0;
    $pendientes_pago = 0;
    $enviados = 0;
    $pendientes_envio = 0;
    $aTiempo = 0;
    $conRetraso = 0;
    
    foreach ($logisticas as $logistica) {
        // Contar estatus de pago
        if ($logistica->estatusPago && strtolower($logistica->estatusPago->nombre) === 'liquidado') {
            $liquidados++;
        } else {
            $pendientes_pago++;
        }
        
        // Contar estatus de envío
        if ($logistica->envio && strtolower($logistica->envio->nombre) === 'enviado') {
            $enviados++;
            
            // Calcular tiempos solo para enviados
            if ($logistica->venta && $logistica->venta->fecha_entrega) {
                $fechaEntrega = new \DateTime($logistica->venta->fecha_entrega);
                $fechaActual = new \DateTime();
                
                if ($fechaEntrega >= $fechaActual) {
                    $aTiempo++;
                } else {
                    $conRetraso++;
                }
            }
        } else {
            $pendientes_envio++;
        }
    }
    
    $totalEnviados = $aTiempo + $conRetraso;
    
    $metrics = array_merge($metrics, [
        'liquidados' => $liquidados,
        'pendientes_pago' => $pendientes_pago,
        'enviados' => $enviados,
        'pendientes_envio' => $pendientes_envio,
        'enviados_a_tiempo' => $aTiempo,
        'enviados_con_retraso' => $conRetraso,
        'porcentaje_a_tiempo' => $totalEnviados > 0 ? ($aTiempo / $totalEnviados) * 100 : 0,
        'porcentaje_retraso' => $totalEnviados > 0 ? ($conRetraso / $totalEnviados) * 100 : 0,
    ]);
    
    return $this->render('dashboard', [
        'metrics' => $metrics,
    ]);
}

private function calculateMetrics()
{
    // Métricas básicas (usando tu mismo enfoque)
    $metrics = [
        'total_anticipos' => Logistica::find()->sum('anticipo') ?? 0,
        'total_restantes' => Logistica::find()->sum('restante') ?? 0,
    ];
    
    // Obtener todos los registros de una vez para procesar (igual que tu dashboard)
    $logisticas = Logistica::find()->with(['estatusPago', 'envio', 'venta'])->all();
    
    $liquidados = 0;
    $pendientes_pago = 0;
    $enviados = 0;
    $pendientes_envio = 0;
    $aTiempo = 0;
    $conRetraso = 0;
    $totalLiquidado = 0; // Nueva variable para sumar cantidades liquidadas
    
    foreach ($logisticas as $logistica) {
        // Contar estatus de pago
        if ($logistica->estatusPago && strtolower($logistica->estatusPago->nombre) === 'liquidado') {
            $liquidados++;
            
            // Sumar solo el campo 'restante' cuando está liquidado
            $totalLiquidado += $logistica->restante ?? 0;
        } else {
            $pendientes_pago++;
        }
        
        // Contar estatus de envío
        if ($logistica->envio && strtolower($logistica->envio->nombre) === 'enviado') {
            $enviados++;
            
            // Calcular tiempos solo para enviados
            if ($logistica->venta && $logistica->venta->fecha_entrega) {
                $fechaEntrega = new \DateTime($logistica->venta->fecha_entrega);
                $fechaActual = new \DateTime();
                
                if ($fechaEntrega >= $fechaActual) {
                    $aTiempo++;
                } else {
                    $conRetraso++;
                }
            }
        } else {
            $pendientes_envio++;
        }
    }
    
    $totalEnviados = $aTiempo + $conRetraso;
    
    $metrics = array_merge($metrics, [
        'liquidados' => $liquidados,
        'pendientes_pago' => $pendientes_pago,
        'enviados' => $enviados,
        'pendientes_envio' => $pendientes_envio,
        'enviados_a_tiempo' => $aTiempo,
        'enviados_con_retraso' => $conRetraso,
        'porcentaje_a_tiempo' => $totalEnviados > 0 ? ($aTiempo / $totalEnviados) * 100 : 0,
        'porcentaje_retraso' => $totalEnviados > 0 ? ($conRetraso / $totalEnviados) * 100 : 0,
        'total_liquidado' => $totalLiquidado, // Nueva métrica
    ]);
    
    return $metrics;
}
}


