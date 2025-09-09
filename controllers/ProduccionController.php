<?php

namespace app\controllers;

use app\models\Produccion;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii;


/**
 * ProduccionController implements the CRUD actions for Produccion model.
 */
class ProduccionController extends Controller
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
     * Lists all Produccion models.
     *
     * @return string
     */
    public function actionIndex()
{
    $dataProvider = new \yii\data\ActiveDataProvider([
        'query' => Produccion::find()->with('venta'),
        'pagination' => ['pageSize' => 20],
        'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
    ]);

    return $this->render('index', [
        'dataProvider' => $dataProvider,
    ]);
}



    /**
     * Displays a single Produccion model.
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
     * Creates a new Produccion model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Produccion();

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
     * Updates an existing Produccion model.
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
     * Deletes an existing Produccion model.
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
     * Finds the Produccion model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Produccion the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Produccion::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
    
    public function actionToggle()
{
    $id = Yii::$app->request->post('id');
    $field = Yii::$app->request->post('field');

    $model = Produccion::findOne($id);
    if (!$model) {
        return $this->asJson(['success' => false, 'error' => 'Producción no encontrada']);
    }

    // Campos permitidos para toggles
    $allowedFields = ['diseno_impresion', 'corte_listo', 'fabricacion_listo'];
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
        return $this->asJson(['success' => true, 'value' => (int)$model->$field]);
    } else {
        return $this->asJson(['success' => false, 'error' => 'No se pudo guardar']);
    }
}

public function actionToggleField()
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    $id = Yii::$app->request->post('id');
    $field = Yii::$app->request->post('field');
    $value = Yii::$app->request->post('value');

    $model = \app\models\Produccion::findOne($id);
    if($model && $field == 'envio_id'){
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
    $id = Yii::$app->request->post('id');
    $field = Yii::$app->request->post('field');
    $value = Yii::$app->request->post('value');

    $model = Produccion::findOne($id);
    if ($model) {
        $model->$field = $value;
        if ($model->save(false)) {
            return 'ok';
        }
    }
    return 'error';
}

}
