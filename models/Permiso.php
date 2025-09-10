<?php

namespace app\models;

use yii\db\ActiveRecord;

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
            [['modulo', 'accion'], 'string', 'max' => 50],
            [['nombre'], 'string', 'max' => 100],
            [['descripcion'], 'string', 'max' => 200],
            [['activo'], 'boolean'],
            [['modulo', 'accion'], 'unique', 'targetAttribute' => ['modulo', 'accion']],
        ];
    }

    public function getKey(): string
    {
        return "{$this->modulo}.{$this->accion}";
    }
}