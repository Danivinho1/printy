<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;

/**
 * Controller de prueba para verificar Usuario::can()
 */
class TestController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'create'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // usuarios autenticados
                        'matchCallback' => function ($rule, $action) {
                            $controllerId = $action->controller->id; // 'test' o reemplaza por el controller real
                            $actionId = $action->id; // 'index' o 'create'
                            // Llamamos a can() del usuario autenticado
                            return Yii::$app->user && !Yii::$app->user->isGuest
                                && Yii::$app->user->identity->can($controllerId, $actionId);
                        },
                    ],
                ],
                'denyCallback' => function ($rule, $action) {
                    throw new \yii\web\ForbiddenHttpException('No tienes permiso para acceder a esta página.');
                },
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->renderContent("<h1>Test Index</h1><p>Usuario ID: " . (Yii::$app->user->id ?? 'guest') . "</p>");
    }

    public function actionCreate()
    {
        return $this->renderContent("<h1>Test Create</h1><p>Acción protegida.</p>");
    }


    // En controllers/TestController.php (solo para pruebas locales)
    public function actionLoginAs($id)
    {
        if (!YII_ENV_DEV) {
            throw new \yii\web\ForbiddenHttpException('Solo desarrollo.');
        }
        $user = \app\models\Usuario::findOne((int) $id);
        if (!$user) {
            return $this->renderContent("Usuario $id no encontrado");
        }
        Yii::$app->user->login($user);
        return $this->redirect(['test/index']);
    }
}