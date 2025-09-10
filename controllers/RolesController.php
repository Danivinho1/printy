<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use app\models\Role;
use app\models\Permiso;
use app\models\RolePermiso;

class RolesController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'create', 'update', 'delete', 'assign'],
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
            'query' => Role::find()->orderBy(['id' => SORT_DESC]),
            'pagination' => ['pageSize' => 20],
        ]);

        return $this->render('index', compact('dataProvider'));
    }

    public function actionCreate()
    {
        $model = new Role();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        }
        return $this->render('_form', compact('model'));
    }

    public function actionUpdate($id)
    {
        $model = $this->findRole($id);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        }
        return $this->render('_form', compact('model'));
    }

    public function actionDelete($id)
    {
        $model = $this->findRole($id);
        $model->activo = 0;
        $model->save(false, ['activo']);
        return $this->redirect(['index']);
    }

    public function actionAssign($id)
    {
        $role = $this->findRole($id);
        $allPerms = Permiso::find()->where(['activo' => 1])->orderBy(['modulo' => SORT_ASC, 'accion' => SORT_ASC])->all();
        $assigned = RolePermiso::find()->select('permiso_id')->where(['role_id' => $role->id])->column();

        if (Yii::$app->request->isPost) {
            $perms = Yii::$app->request->post('permisos', []);

            RolePermiso::deleteAll(['role_id' => $role->id]);
            if (!empty($perms)) {
                $rows = [];
                $now = date('Y-m-d H:i:s');
                foreach ($perms as $pid) {
                    $rows[] = [(int)$role->id, (int)$pid, $now];
                }
                if (!empty($rows)) {
                    Yii::$app->db->createCommand()
                        ->batchInsert('roles_permisos', ['role_id', 'permiso_id', 'created_at'], $rows)
                        ->execute();
                }
            }

            Yii::$app->session->setFlash('success', 'Permisos del rol actualizados.');
            return $this->redirect(['index']);
        }

        return $this->render('assign', [
            'role' => $role,
            'allPerms' => $allPerms,
            'assigned' => $assigned,
        ]);
    }

    protected function findRole($id): Role
    {
        $role = Role::findOne($id);
        if (!$role) {
            throw new NotFoundHttpException('Rol no encontrado.');
        }
        return $role;
    }
}