<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "roles".
 *
 * @property int $id
 * @property string $nombre
 * @property string|null $descripcion
 * @property int|null $es_admin
 * @property int|null $activo
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Permiso[] $permisos
 * @property RolePermiso[] $rolesPermisos
 * @property Usuario[] $usuarios
 */
class Role extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'roles';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['descripcion'], 'default', 'value' => null],
            [['es_admin'], 'default', 'value' => 0],
            [['activo'], 'default', 'value' => 1],
            [['nombre'], 'required'],
            [['es_admin', 'activo'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['nombre'], 'string', 'max' => 50],
            [['descripcion'], 'string', 'max' => 150],
            [['nombre'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nombre' => 'Nombre',
            'descripcion' => 'Descripción',
            'es_admin' => 'Es Administrador',
            'activo' => 'Activo',
            'created_at' => 'Creado',
            'updated_at' => 'Actualizado',
        ];
    }

    /**
     * Gets query for [[Permisos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPermisos()
    {
        return $this->hasMany(Permiso::class, ['id' => 'permiso_id'])->viaTable('roles_permisos', ['role_id' => 'id']);
    }

    /**
     * Gets query for [[RolesPermisos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRolesPermisos()
    {
        return $this->hasMany(RolePermiso::class, ['role_id' => 'id']);
    }

    /**
     * Gets query for [[Usuarios]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsuarios()
    {
        return $this->hasMany(Usuario::class, ['role_id' => 'id']);
    }

    /**
     * Check if role has specific permission
     */
    public function hasPermission($modulo, $accion)
    {
        if ($this->es_admin) {
            return true;
        }

        return $this->getPermisos()
            ->where(['modulo' => $modulo, 'accion' => $accion, 'activo' => 1])
            ->exists();
    }

    /**
     * Get all permission IDs for this role
     */
    public function getPermissionIds()
    {
        return $this->getPermisos()->select('id')->column();
    }

    /**
     * Assign permissions to this role
     */
    public function assignPermissions($permissionIds)
    {
        // Remove existing permissions
        RolePermiso::deleteAll(['role_id' => $this->id]);
        
        // Add new permissions
        foreach ($permissionIds as $permissionId) {
            $rolePermiso = new RolePermiso();
            $rolePermiso->role_id = $this->id;
            $rolePermiso->permiso_id = $permissionId;
            $rolePermiso->save();
        }
    }

    /**
     * Get status label
     */
    public function getStatusLabel()
    {
        return $this->activo ? 'Activo' : 'Inactivo';
    }

    /**
     * Get admin label
     */
    public function getAdminLabel()
    {
        return $this->es_admin ? 'Sí' : 'No';
    }
}