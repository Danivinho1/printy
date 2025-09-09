<?php

namespace app\controllers;

use Yii;
use app\models\Usuario;
use app\models\Role;
use app\models\Permiso;
use app\models\RolePermiso;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use yii\web\Response;

/**
 * AdminController implements the CRUD actions for administration.
 */
class AdminController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'usuarios', 'create-usuario', 'update-usuario', 'delete-usuario', 
                          'roles', 'create-role', 'update-role', 'delete-role', 'assign-permissions',
                          'permisos', 'create-permiso', 'update-permiso', 'delete-permiso'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->identity->hasPermission('admin', 'all') || 
                                   Yii::$app->user->identity->role->es_admin;
                        }
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete-usuario' => ['POST'],
                    'delete-role' => ['POST'],
                    'delete-permiso' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Dashboard de administración
     */
    public function actionIndex()
    {
        $totalUsuarios = Usuario::find()->count();
        $usuariosActivos = Usuario::find()->where(['activo' => 1])->count();
        $totalRoles = Role::find()->count();
        $totalPermisos = Permiso::find()->count();

        return $this->render('index', [
            'totalUsuarios' => $totalUsuarios,
            'usuariosActivos' => $usuariosActivos,
            'totalRoles' => $totalRoles,
            'totalPermisos' => $totalPermisos,
        ]);
    }

    // USUARIOS
    /**
     * Lista todos los usuarios
     */
    public function actionUsuarios()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Usuario::find()->with('role'),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('usuarios/index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Crear nuevo usuario
     */
    public function actionCreateUsuario()
    {
        $model = new Usuario();
        $model->scenario = 'create';

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Usuario creado exitosamente.');
            return $this->redirect(['usuarios']);
        }

        return $this->render('usuarios/create', [
            'model' => $model,
            'roles' => Role::find()->where(['activo' => 1])->all(),
        ]);
    }

    /**
     * Actualizar usuario existente
     */
    public function actionUpdateUsuario($id)
    {
        $model = $this->findUsuario($id);
        $model->scenario = 'update';

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Usuario actualizado exitosamente.');
            return $this->redirect(['usuarios']);
        }

        return $this->render('usuarios/update', [
            'model' => $model,
            'roles' => Role::find()->where(['activo' => 1])->all(),
        ]);
    }

    /**
     * Eliminar usuario
     */
    public function actionDeleteUsuario($id)
    {
        $model = $this->findUsuario($id);
        
        // No permitir eliminar el usuario actual
        if ($model->id == Yii::$app->user->id) {
            Yii::$app->session->setFlash('error', 'No puedes eliminar tu propio usuario.');
            return $this->redirect(['usuarios']);
        }

        $model->delete();
        Yii::$app->session->setFlash('success', 'Usuario eliminado exitosamente.');

        return $this->redirect(['usuarios']);
    }

    // ROLES
    /**
     * Lista todos los roles
     */
    public function actionRoles()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Role::find(),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('roles/index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Crear nuevo rol
     */
    public function actionCreateRole()
    {
        $model = new Role();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Rol creado exitosamente.');
            return $this->redirect(['roles']);
        }

        return $this->render('roles/create', [
            'model' => $model,
        ]);
    }

    /**
     * Actualizar rol existente
     */
    public function actionUpdateRole($id)
    {
        $model = $this->findRole($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Rol actualizado exitosamente.');
            return $this->redirect(['roles']);
        }

        return $this->render('roles/update', [
            'model' => $model,
        ]);
    }

    /**
     * Eliminar rol
     */
    public function actionDeleteRole($id)
    {
        $model = $this->findRole($id);
        
        // Verificar que no tenga usuarios asignados
        if ($model->getUsuarios()->count() > 0) {
            Yii::$app->session->setFlash('error', 'No se puede eliminar el rol porque tiene usuarios asignados.');
            return $this->redirect(['roles']);
        }

        $model->delete();
        Yii::$app->session->setFlash('success', 'Rol eliminado exitosamente.');

        return $this->redirect(['roles']);
    }

    /**
     * Asignar permisos a rol
     */
    public function actionAssignPermissions($id)
    {
        $model = $this->findRole($id);
        $permisos = Permiso::find()->where(['activo' => 1])->all();
        $permisosAsignados = $model->getPermissionIds();

        if (Yii::$app->request->isPost) {
            $selectedPermisos = Yii::$app->request->post('permisos', []);
            $model->assignPermissions($selectedPermisos);
            
            Yii::$app->session->setFlash('success', 'Permisos asignados exitosamente.');
            return $this->redirect(['roles']);
        }

        return $this->render('roles/assign-permissions', [
            'model' => $model,
            'permisos' => $permisos,
            'permisosAsignados' => $permisosAsignados,
        ]);
    }

    // PERMISOS
    /**
     * Lista todos los permisos
     */
    public function actionPermisos()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Permiso::find(),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('permisos/index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Crear nuevo permiso
     */
    public function actionCreatePermiso()
    {
        $model = new Permiso();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Permiso creado exitosamente.');
            return $this->redirect(['permisos']);
        }

        return $this->render('permisos/create', [
            'model' => $model,
        ]);
    }

    /**
     * Actualizar permiso existente
     */
    public function actionUpdatePermiso($id)
    {
        $model = $this->findPermiso($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Permiso actualizado exitosamente.');
            return $this->redirect(['permisos']);
        }

        return $this->render('permisos/update', [
            'model' => $model,
        ]);
    }

    /**
     * Eliminar permiso
     */
    public function actionDeletePermiso($id)
    {
        $model = $this->findPermiso($id);
        $model->delete();
        
        Yii::$app->session->setFlash('success', 'Permiso eliminado exitosamente.');
        return $this->redirect(['permisos']);
    }

    // HELPER METHODS
    /**
     * Finds the Usuario model based on its primary key value.
     */
    protected function findUsuario($id)
    {
        if (($model = Usuario::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('El usuario solicitado no existe.');
    }

    /**
     * Finds the Role model based on its primary key value.
     */
    protected function findRole($id)
    {
        if (($model = Role::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('El rol solicitado no existe.');
    }

    /**
     * Finds the Permiso model based on its primary key value.
     */
    protected function findPermiso($id)
    {
        if (($model = Permiso::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('El permiso solicitado no existe.');
    }
}