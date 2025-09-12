<?php

namespace app\models;

use yii\db\ActiveRecord;

class Role extends ActiveRecord
{
    public static function tableName()
    {
        return 'roles';
    }

    public function rules()
    {
        return [
            [['nombre'], 'required'],
            [['nombre'], 'string', 'max' => 50],
            [['descripcion'], 'string', 'max' => 150],
            [['es_admin', 'activo'], 'boolean'],
            [['nombre'], 'unique'],
        ];
    }

    public function getPermisos()
    {
        return $this->hasMany(Permiso::class, ['id' => 'permiso_id'])
            ->viaTable('roles_permisos', ['role_id' => 'id']);
    }
}