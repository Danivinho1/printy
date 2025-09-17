<?php
namespace app\controllers;

use yii\web\Controller;
use app\models\Usuario;
use app\models\Role;
use app\models\Permiso;

class AdminController extends Controller
{
    public function actionIndex()
    {
        // KPIs rápidos
        $totalUsuarios = (int) Usuario::find()->count();
        $totalRoles    = (int) Role::find()->count();
        $totalPerms    = (int) Permiso::find()->count();

        // Últimos usuarios actualizados/creados
        $ultimosUsuarios = Usuario::find()
            ->with('role')
            ->orderBy(['updated_at' => SORT_DESC])
            ->limit(5)
            ->all();

        return $this->render('index', [
            'totalUsuarios'   => $totalUsuarios,
            'totalRoles'      => $totalRoles,
            'totalPerms'      => $totalPerms,
            'ultimosUsuarios' => $ultimosUsuarios,
        ]);
    }
}