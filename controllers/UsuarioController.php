<?php

namespace app\controllers;

use app\models\Usuario;
use app\models\Role;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class UsuarioController extends Controller
{
    public function actionIndex()
{
    $searchModel = new \app\models\UsuarioSearch();
    $dataProvider = $searchModel->search(\Yii::$app->request->queryParams);

    // Para evitar N+1 y ordenar como lo tenías
    $dataProvider->query->with('role')->orderBy(['id' => SORT_DESC]);
    $dataProvider->pagination = ['pageSize' => 20];

    return $this->render('index', [
        'searchModel'  => $searchModel,
        'dataProvider' => $dataProvider,
    ]);
}

    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->render('view', compact('model'));
    }

    public function actionCreate()
    {
        $model = new Usuario();
        if ($model->load(Yii::$app->request->post())) {
            if (!empty($model->new_password)) {
                $model->setPassword($model->new_password);
            }
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Usuario creado.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }
        $roles = Role::find()->select(['nombre', 'id'])->indexBy('id')->column();
        return $this->render('create', compact('model', 'roles'));
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            if (!empty($model->new_password)) {
                $model->setPassword($model->new_password);
            }
            if ($model->save(false)) {
                Yii::$app->session->setFlash('success', 'Usuario actualizado.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }
        $roles = Role::find()->select(['nombre', 'id'])->indexBy('id')->column();
        return $this->render('update', compact('model', 'roles'));
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();
        Yii::$app->session->setFlash('success', 'Usuario eliminado.');
        return $this->redirect(['index']);
    }

    protected function findModel($id): Usuario
    {
        $model = Usuario::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('El usuario no existe.');
        }
        return $model;
    }
}