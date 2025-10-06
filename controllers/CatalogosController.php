<?php

namespace app\controllers;

use app\models\Catalogos;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\helpers\Json;
use yii;

/**
 * CatalogosController implements the CRUD actions for Catalogos model.
 */
class CatalogosController extends Controller
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
     * Lists all Catalogos models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Catalogos::find(),
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
     * Displays a single Catalogos model.
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
     * Creates a new Catalogos model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Catalogos();

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
     * Updates an existing Catalogos model.
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
     * Deletes an existing Catalogos model.
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
     * Finds the Catalogos model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Catalogos the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Catalogos::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionCreateAjax()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!Yii::$app->request->isPost) {
            return ['success' => false, 'error' => 'Método no permitido'];
        }

        $nombre = Yii::$app->request->post('nombre');
        $tipo = Yii::$app->request->post('tipo');

        if (empty($nombre) || empty($tipo)) {
            return ['success' => false, 'error' => 'Nombre y tipo son requeridos'];
        }

        // Verificar si ya existe
        $existe = Catalogos::find()
            ->where(['nombre' => $nombre, 'tipo' => $tipo])
            ->exists();

        if ($existe) {
            return ['success' => false, 'error' => 'Ya existe un elemento con ese nombre'];
        }

        $model = new Catalogos();
        $model->nombre = $nombre;
        $model->tipo = $tipo;
        $model->activo = 1; // o el campo que uses para activar

        if ($model->save()) {
            return [
                'success' => true,
                'id' => $model->id,
                'nombre' => $model->nombre
            ];
        } else {
            return ['success' => false, 'error' => 'Error al guardar: ' . implode(', ', $model->getFirstErrors())];
        }
    }

    /**
     * Actualizar elemento via AJAX
     */
    public function actionUpdateAjax()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!Yii::$app->request->isPost) {
            return ['success' => false, 'error' => 'Método no permitido'];
        }

        $id = Yii::$app->request->post('id');
        $nombre = Yii::$app->request->post('nombre');
        $tipo = Yii::$app->request->post('tipo');

        if (empty($id) || empty($nombre) || empty($tipo)) {
            return ['success' => false, 'error' => 'ID, nombre y tipo son requeridos'];
        }

        $model = Catalogos::findOne($id);
        if (!$model) {
            return ['success' => false, 'error' => 'Elemento no encontrado'];
        }

        // Verificar si ya existe otro con el mismo nombre (excluyendo el actual)
        $existe = Catalogos::find()
            ->where(['nombre' => $nombre, 'tipo' => $tipo])
            ->andWhere(['!=', 'id', $id])
            ->exists();

        if ($existe) {
            return ['success' => false, 'error' => 'Ya existe otro elemento con ese nombre'];
        }

        $model->nombre = $nombre;

        if ($model->save()) {
            return [
                'success' => true,
                'id' => $model->id,
                'nombre' => $model->nombre
            ];
        } else {
            return ['success' => false, 'error' => 'Error al actualizar: ' . implode(', ', $model->getFirstErrors())];
        }
    }

    /**
     * Eliminar elemento via AJAX
     */
    public function actionDeleteAjax()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $ids = Yii::$app->request->post('id');
        $tipo = Yii::$app->request->post('tipo');

        if (empty($ids)) {
            return ['success' => false, 'error' => 'No se recibieron elementos a eliminar'];
        }

        if (!is_array($ids)) {
            $ids = [$ids]; // si viene 1 solo
        }

        try {
            foreach ($ids as $id) {
                $model = Catalogos::findOne(['id' => $id, 'tipo' => $tipo]);
                if ($model) {
                    $model->delete();
                }
            }
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }


}
