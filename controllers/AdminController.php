<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use app\models\User;
use app\models\Role;
use app\models\forms\UserForm;


class AdminController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'users', 'create-user', 'update-user', 'delete-user', 'assign-role'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function () {
                            return Yii::$app->user->identity?->role?->es_admin == 1;
                        },
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete-user' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionUsers()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => User::find()->orderBy(['id' => SORT_DESC]),
            'pagination' => ['pageSize' => 20],
        ]);

        return $this->render('users', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCreateUser()
    {
        $model = new UserForm();
        $roles = ArrayHelper::map(Role::find()->where(['activo' => 1])->all(), 'id', 'nombre');

        if ($model->load(Yii::$app->request->post()) && ($user = $model->save())) {
            Yii::$app->session->setFlash('success', 'Usuario creado.');
            return $this->redirect(['users']);
        }

        return $this->render('_form', ['model' => $model, 'roles' => $roles, 'title' => 'Crear usuario']);
    }

    public function actionUpdateUser($id)
    {
        $user = $this->findUser($id);
        $model = new UserForm();
        $model->loadFromUser($user);
        $roles = ArrayHelper::map(Role::find()->where(['activo' => 1])->all(), 'id', 'nombre');

        if ($model->load(Yii::$app->request->post()) && ($saved = $model->save($user))) {
            Yii::$app->session->setFlash('success', 'Usuario actualizado.');
            return $this->redirect(['users']);
        }

        return $this->render('_form', ['model' => $model, 'roles' => $roles, 'title' => 'Editar usuario']);
    }

    public function actionDeleteUser($id)
    {
        $user = $this->findUser($id);
        $user->activo = 0;
        $user->save(false, ['activo']);
        Yii::$app->session->setFlash('success', 'Usuario desactivado.');
        return $this->redirect(['users']);
    }

    public function actionAssignRole($id)
    {
        $user = $this->findUser($id);
$roles = ArrayHelper::map(Role::find()->where(['activo' => 1])->all(), 'id', 'nombre');
        if (Yii::$app->request->isPost) {
            $user->role_id = (int)Yii::$app->request->post('role_id');
            if ($user->save(false, ['role_id'])) {
                Yii::$app->session->setFlash('success', 'Rol asignado.');
                return $this->redirect(['users']);
            }
        }

        return $this->render('assign-role', [
            'user' => $user,
            'roles' => $roles,
        ]);
    }

    protected function findUser($id): User
    {
        $user = User::findOne($id);
        if (!$user) {
            throw new NotFoundHttpException('Usuario no encontrado.');
        }
        return $user;
    }
}