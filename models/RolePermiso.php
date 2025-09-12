<?php

namespace app\models;

use yii\db\ActiveRecord;

class RolePermiso extends ActiveRecord
{
    public static function tableName()
    {
        return 'roles_permisos';
    }

    public function rules()
    {
        return [
            [['role_id', 'permiso_id'], 'required'],
            [['role_id', 'permiso_id'], 'integer'],
            [['role_id', 'permiso_id'], 'unique', 'targetAttribute' => ['role_id', 'permiso_id']],
        ];
    }
}