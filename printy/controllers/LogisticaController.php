<?php

namespace app\controllers;

use app\models\Logistica;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
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

    $model = \app\models\Logistica::findOne($id);
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
            $venta = \app\models\Ventas::findOne($model->venta_id);
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


}
