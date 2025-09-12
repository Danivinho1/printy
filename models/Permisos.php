<?php

namespace app\models;

use Yii;

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
 * @property Roles[] $roles
 * @property RolesPermisos[] $rolesPermisos
 */
class Permisos extends \yii\db\ActiveRecord
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
            [['descripcion'], 'default', 'value' => null],
            [['activo'], 'default', 'value' => 1],
            [['modulo', 'accion', 'nombre'], 'required'],
            [['activo'], 'integer'],
            [['modulo', 'accion'], 'string', 'max' => 50],
            [['nombre'], 'string', 'max' => 100],
            [['descripcion'], 'string', 'max' => 200],
            [['modulo', 'accion'], 'unique', 'targetAttribute' => ['modulo', 'accion']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'modulo' => 'Modulo',
            'accion' => 'Accion',
            'nombre' => 'Nombre',
            'descripcion' => 'Descripcion',
            'activo' => 'Activo',
        ];
    }

    /**
     * Gets query for [[Roles]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRoles()
    {
        return $this->hasMany(Roles::class, ['id' => 'role_id'])->viaTable('roles_permisos', ['permiso_id' => 'id']);
    }

    /**
     * Gets query for [[RolesPermisos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRolesPermisos()
    {
        return $this->hasMany(RolesPermisos::class, ['permiso_id' => 'id']);
    }

}
