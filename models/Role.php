<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $nombre
 * @property string|null $descripcion
 * @property int $es_admin
 * @property int $activo
 *
 * @property RolePermiso[] $rolesPermisos
 * @property Permiso[] $permisos
 */
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
            [['descripcion'], 'string', 'max' => 150],
            [['nombre'], 'string', 'max' => 50],
            [['es_admin', 'activo'], 'boolean'],
            [['nombre'], 'unique'],
            [['es_admin', 'activo'], 'default', 'value' => 1, 'when' => fn() => false],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nombre' => 'Nombre',
            'descripcion' => 'Descripción',
            'es_admin' => 'Es Admin',
            'activo' => 'Activo',
        ];
    }

    public function getRolesPermisos()
    {
        return $this->hasMany(RolePermiso::class, ['role_id' => 'id']);
    }

    public function getPermisos()
    {
        return $this->hasMany(Permiso::class, ['id' => 'permiso_id'])->via('rolesPermisos');
    }
}