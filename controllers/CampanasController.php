<?php

namespace app\controllers;

use Yii;
use app\models\Campanas;
use app\models\CampanasSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * Controlador para el módulo de Campañas (Marketing)
 */
class CampanasController extends Controller
{
    /** ===============================
     *  CONFIGURACIÓN DE BEHAVIORS
     * =============================== */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /** ===============================
     *  LISTAR CAMPAÑAS
     * =============================== */
    public function actionIndex()
    {
        $searchModel = new CampanasSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
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
     *  CREAR NUEVA CAMPAÑA
     * =============================== */
    public function actionCreate()
    {
        $model = new Campanas();

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
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
     *  GUARDADO EN TIEMPO REAL (AJAX)
     * =============================== */
    public function actionUpdateInline()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = Yii::$app->request->post('id');
        $attr = Yii::$app->request->post('attr');
        $value = Yii::$app->request->post('value');

        $model = Campanas::findOne($id);
        if ($model && $model->hasAttribute($attr)) {
            $model->$attr = $value;
            if ($model->save(false)) {
                return ['success' => true];
            }
        }
        return ['success' => false];
    }

    /** ===============================
     *  FUNCIÓN AUXILIAR
     * =============================== */
    protected function findModel($id)
    {
        if (($model = Campanas::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('La campaña solicitada no existe.');
    }
}
