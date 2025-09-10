<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use app\models\Permiso;

class PermisosController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'create', 'update', 'delete'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => fn() => Yii::$app->user->identity?->role?->es_admin == 1,
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Permiso::find()->orderBy(['modulo' => SORT_ASC, 'accion' => SORT_ASC]),
            'pagination' => ['pageSize' => 50],
        ]);

        return $this->render('index', compact('dataProvider'));
    }

    public function actionCreate()
    {
        $model = new Permiso();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        }
        return $this->render('_form', compact('model'));
    }

    public function actionUpdate($id)
    {
        $model = $this->findPermiso($id);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        }
        return $this->render('_form', compact('model'));
    }

    public function actionDelete($id)
    {
        $model = $this->findPermiso($id);
        $model->activo = 0;
        $model->save(false, ['activo']);
        return $this->redirect(['index']);
    }

    protected function findPermiso($id): Permiso
    {
        $model = Permiso::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('Permiso no encontrado.');
        }
        return $model;
    }
}