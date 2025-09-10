<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "permisos".
 *
 * @property int $id
 * @property string $modulo
 * @property string $accion
 * @property string $nombre
 * @property string|null $descripcion
 * @property int|null $activo
 *
 * @property RolePermiso[] $rolesPermisos
 * @property Role[] $roles
 */
class Permiso extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'permisos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['modulo', 'accion', 'nombre'], 'required'],
            [['activo'], 'integer'],
            [['descripcion'], 'string'],
            [['modulo', 'accion'], 'string', 'max' => 50],
            [['nombre'], 'string', 'max' => 100],
            [['descripcion'], 'string', 'max' => 200],
            [['modulo', 'accion'], 'unique', 'targetAttribute' => ['modulo', 'accion']],
            ['activo', 'default', 'value' => 1],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'modulo' => 'Módulo',
            'accion' => 'Acción',
            'nombre' => 'Nombre',
            'descripcion' => 'Descripción',
            'activo' => 'Activo',
        ];
    }

    /**
     * Gets query for roles_permisos records.
     */
    public function getRolesPermisos()
    {
        return $this->hasMany(RolePermiso::class, ['permiso_id' => 'id']);
    }

    /**
     * Gets query for related roles.
     */
    public function getRoles()
    {
        return $this->hasMany(Role::class, ['id' => 'role_id'])
            ->viaTable('roles_permisos', ['permiso_id' => 'id']);
    }

    /**
     * Get status label
     */
    public function getStatusLabel()
    {
        return $this->activo ? 'Activo' : 'Inactivo';
    }

    /**
     * Get full permission name
     */
    public function getFullName()
    {
        return $this->modulo . '.' . $this->accion;
    }
}