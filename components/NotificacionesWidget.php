<?php
namespace app\components;

use Yii;
use yii\base\Widget;
use app\models\Notificacion;

/**
 * Widget de notificaciones (seguro: busca la vista components/views/notificaciones.php).
 * Muestra las últimas notificaciones del usuario y el conteo de no leídas.
 */
class NotificacionesWidget extends Widget
{
    /**
     * Ejecuta el widget.
     * Retorna render('notificaciones', ...) — la vista debe estar en components/views/notificaciones.php
     */
    public function run()
    {
        $usuarioId = Yii::$app->user->id ?? null;
        $notificaciones = [];
        $conteoNuevas = 0;

        if ($usuarioId) {
            try {
                $notificaciones = Notificacion::find()
                    ->where(['usuario_id' => $usuarioId])
                    ->orderBy(['created_at' => SORT_DESC])
                    ->limit(10)
                    ->all();

                $conteoNuevas = (int) Notificacion::find()
                    ->where(['usuario_id' => $usuarioId, 'leido' => false])
                    ->count();
            } catch (\Throwable $e) {
                Yii::error("NotificacionesWidget error: " . $e->getMessage());
                // En caso de error devolvemos array vacío y 0 para no romper la vista
                $notificaciones = [];
                $conteoNuevas = 0;
            }
        }

        return $this->render('notificaciones', [
            'notificaciones' => $notificaciones,
            'conteoNuevas' => $conteoNuevas,
        ]);
    }
}