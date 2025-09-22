<?php

namespace app\controllers;

use app\models\Logistica;
use app\models\Ventas;
use app\models\Diseno;
use app\models\Produccion;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\ArrayDataProvider;
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
        $dataProvider = new ActiveDataProvider([
            'query' => Logistica::find(),
            /*
            'pagination' => [
                'pageSize' => 50
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ],
            */
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
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
            'restante' => $venta->restante ?? 0,
            // Datos de envío
            'estatus_envio_id' => $logistica->estatus_envio_id ?? null,
            'envio_nombre' => $envioNombre,
            // Datos de pago (también de logística)
            'estatus_pago_id' => $logistica->estatus_pago_id ?? null,
            'pago_nombre' => $pagoNombre,
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
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    $ventaId = Yii::$app->request->post('id');
    $field = Yii::$app->request->post('field');
    $value = Yii::$app->request->post('value');

    // Buscar logística por venta_id
    $logistica = Logistica::find()->where(['venta_id' => $ventaId])->one();
    
    if (!$logistica) {
        // Crear nuevo registro de logística si no existe
        $logistica = new Logistica();
        $logistica->venta_id = $ventaId;
    }

    if ($field == 'estatus_envio_id') {
        $logistica->estatus_envio_id = $value;
        
        if ($logistica->save()) {
            // Obtener el nuevo nombre para la respuesta
            $catalogoEnvio = \app\models\Catalogos::findOne($value);
            $nuevoNombre = $catalogoEnvio ? $catalogoEnvio->nombre : 'Desconocido';
            
            return [
                'success' => true,
                'nombre' => $nuevoNombre
            ];
        }
    }

    return [
        'success' => false,
        'message' => 'Error al actualizar',
        'errors' => $logistica->getErrors()
    ];
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
        
        if (!$logistica) {
            // Si no existe, crear uno nuevo
            $logistica = new Logistica();
            $logistica->venta_id = $id;
        }

        if ($field == 'estatus_pago_id') {
            $logistica->estatus_pago_id = $value;
            
            if ($logistica->save()) {
                // Obtener el nuevo nombre para la respuesta
                $catalogo = \app\models\Catalogos::findOne($value);
                $nuevoNombre = $catalogo ? $catalogo->nombre : 'Desconocido';
                
                return [
                    'success' => true,
                    'nombre' => $nuevoNombre
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al guardar',
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
}


