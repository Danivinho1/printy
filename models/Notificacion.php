<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Notificacion extends ActiveRecord
{
    public static function tableName()
    {
        return 'notificacion';
    }

    public function rules()
    {
        return [
            [['usuario_id', 'mensaje'], 'required'],
            [['usuario_id'], 'integer'],
            [['leida'], 'boolean'],
            [['created_at'], 'safe'],
            [['mensaje'], 'string', 'max' => 255],
            [['tipo'], 'string', 'max' => 30],
        ];
    }

    public function getUsuario()
    {
        return $this->hasOne(Usuario::class, ['id' => 'usuario_id']);
    }

    // Evita notificaciones duplicadas
    public static function crearSiNoExiste($usuario_id, $mensaje, $tipo = 'info')
    {
        $existe = self::find()->where([
            'usuario_id' => $usuario_id,
            'mensaje' => $mensaje,
            'leida' => false
        ])->one();

        if (!$existe) {
            $n = new self();
            $n->usuario_id = $usuario_id;
            $n->mensaje = $mensaje;
            $n->tipo = $tipo;
            $n->save();
        }
    }
}