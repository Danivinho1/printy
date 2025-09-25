<?php

namespace app\components;

use Yii;
use yii\base\Widget;
use app\models\Notificacion;

class NotificacionesWidget extends Widget
{
    public function run()
    {
        if (Yii::$app->user->isGuest) {
            return '';
        }

        $usuarioId = Yii::$app->user->id;
        $notificaciones = Notificacion::find()
            ->where(['usuario_id' => $usuarioId])
            ->orderBy(['leida' => SORT_ASC, 'created_at' => SORT_DESC])
            ->limit(10)
            ->all();
        $noLeidas = Notificacion::find()
            ->where(['usuario_id' => $usuarioId, 'leida' => 0])
            ->count();

        return $this->render('notificaciones', [
            'notificaciones' => $notificaciones,
            'noLeidas' => $noLeidas,
        ]);
    }
}