<?php

namespace app\models;

use Yii;

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
 * @property Permisos[] $permisos
 * @property RolesPermisos[] $rolesPermisos
 * @property Usuarios[] $usuarios
 */
class Roles extends \yii\db\ActiveRecord
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
            'descripcion' => 'Descripcion',
            'es_admin' => 'Es Admin',
            'activo' => 'Activo',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Permisos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPermisos()
    {
        return $this->hasMany(Permisos::class, ['id' => 'permiso_id'])->viaTable('roles_permisos', ['role_id' => 'id']);
    }

    /**
     * Gets query for [[RolesPermisos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRolesPermisos()
    {
        return $this->hasMany(RolesPermisos::class, ['role_id' => 'id']);
    }

    /**
     * Gets query for [[Usuarios]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsuarios()
    {
        return $this->hasMany(Usuarios::class, ['role_id' => 'id']);
    }

}
