<?php

namespace app\components;

use Yii;
use yii\base\Component;

/**
 * Uso: Yii::$app->perm->can('ventas.crear')
 */
class PermissionChecker extends Component
{
    public function can(string $permKey): bool
    {
        $user = Yii::$app->user->identity;
        if (!$user) {
            return false;
        }

        // Acceso total si el rol es admin
        if ($user->role && (int)$user->role->es_admin === 1) {
            return true;
        }

        [$modulo, $accion] = array_pad(explode('.', $permKey, 2), 2, null);
        if (!$modulo || !$accion) {
            return false;
        }

        return (new \yii\db\Query())
            ->from('permisos p')
            ->innerJoin('roles_permisos rp', 'rp.permiso_id = p.id')
            ->where([
                'rp.role_id' => $user->role_id,
                'p.modulo' => $modulo,
                'p.accion' => $accion,
                'p.activo' => 1,
            ])
            ->exists();
    }
}