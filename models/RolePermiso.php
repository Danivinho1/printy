<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $role_id
 * @property int $permiso_id
 *
 * @property Role $role
 * @property Permiso $permiso
 */
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
            [['role_id'], 'exist', 'targetClass' => Role::class, 'targetAttribute' => ['role_id' => 'id']],
            [['permiso_id'], 'exist', 'targetClass' => Permiso::class, 'targetAttribute' => ['permiso_id' => 'id']],
        ];
    }

    public function getRole()
    {
        return $this->hasOne(Role::class, ['id' => 'role_id']);
    }

    public function getPermiso()
    {
        return $this->hasOne(Permiso::class, ['id' => 'permiso_id']);
    }
}