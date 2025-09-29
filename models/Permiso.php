<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * Permiso ActiveRecord
 */
class Permiso extends ActiveRecord
{
    public static function tableName()
    {
        return 'permisos';
    }

    public function rules()
    {
        return [
            [['modulo', 'accion', 'nombre'], 'required'],
            [['descripcion'], 'string'],
            [['modulo', 'accion', 'nombre'], 'string', 'max' => 100],
            [['activo'], 'boolean'],
        ];
    }
}