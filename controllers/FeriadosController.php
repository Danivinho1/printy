<?php

namespace app\controllers;

use app\models\Feriados;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use Yii;

/**
 * FeriadosController implements the CRUD actions for Feriados model.
 */
class FeriadosController extends Controller
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
     * Lists all Feriados models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Feriados::find(),
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
     * Displays a single Feriados model.
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
     * Creates a new Feriados model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Feriados();

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
     * Updates an existing Feriados model.
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
     * Deletes an existing Feriados model.
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
     * Finds the Feriados model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Feriados the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Feriados::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }


    // Devuelve los feriados en formato JSON
    public function actionEventos()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $feriados = Feriados::find()->all();
        $eventos = [];

        foreach ($feriados as $feriado) {
            $eventos[] = [
                'id' => $feriado->id,
                'title' => $feriado->descripcion,
                'start' => $feriado->fecha,
                'allDay' => true,
            ];
        }

        return $eventos;
    }

    // Agrega un feriado desde el calendario
    public function actionAgregar()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $fecha = Yii::$app->request->post('fecha');
        $descripcion = Yii::$app->request->post('descripcion', 'Feriado');

        if ($fecha) {
            $feriado = new Feriados();
            $feriado->fecha = $fecha;
            $feriado->descripcion = $descripcion;

            if ($feriado->save()) {
                return [
                    'status' => 'success',
                    'feriado' => [
                        'id' => $feriado->id,
                        'title' => $feriado->descripcion,
                        'start' => $feriado->fecha,
                        'allDay' => true,
                    ]
                ];
            }
        }

        return ['status' => 'error'];
    }

    // Elimina un feriado con clic derecho
    public function actionEliminar()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $id = Yii::$app->request->post('id');
        $feriado = Feriados::findOne($id);

        if ($feriado && $feriado->delete()) {
            return ['status' => 'success'];
        }

        return ['status' => 'error'];
    }


}
