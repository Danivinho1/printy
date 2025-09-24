<?php
namespace app\components;

use Yii;
use yii\base\Behavior;
use yii\base\ActionEvent;
use yii\base\Module;
use yii\web\ForbiddenHttpException;

class PermissionChecker extends Behavior
{
    public function events()
    {
        return [
            Module::EVENT_BEFORE_ACTION => 'beforeAction',
        ];
    }

    public function beforeAction($event): bool
    {
        if (!$event instanceof ActionEvent || $event->action === null) {
            return true;
        }

        $action = $event->action;
        $controller = $action->controller;
        $actionId = $action->id;
        $route = $action->uniqueId;

        // Rutas públicas (agregado site/logout)
        $publicRoutes = [
            'site/login',
            'site/error',
            'site/captcha',
            'site/logout',
            'debug/default/toolbar',
            'debug/default/view',
            'gii/default/index',
        ];
        if (in_array($route, $publicRoutes, true)) {
            return true;
        }

        if (Yii::$app->user->isGuest) {
            Yii::$app->user->loginRequired();
            return false;
        }

        $controllerId = $controller->id;
        $modulo = $this->mapModulo($controllerId);
        $accion = $this->mapAccion($actionId);

        $user = Yii::$app->user->identity;
        if (method_exists($user, 'can') && $user->can($modulo, $accion)) {
            return true;
        }

        throw new ForbiddenHttpException('No tienes permiso para acceder a esta acción.');
    }

    private function mapModulo(string $controllerId): string
    {
        $adminControllers = ['admin', 'usuario', 'role', 'permiso'];
        if (in_array($controllerId, $adminControllers, true)) {
            return 'admin';
        }
        return $controllerId;
    }

    private function mapAccion(string $accion): string
    {
        $map = [
            'view'   => 'index',
            'list'   => 'index',
            'create' => 'create',
            'update' => 'update',
            'delete' => 'delete',
        ];
        return $map[$accion] ?? $accion;
    }
}