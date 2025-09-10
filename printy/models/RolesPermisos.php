<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "roles_permisos".
 *
 * @property int $id
 * @property int $role_id
 * @property int $permiso_id
 * @property string $created_at
 *
 * @property Permisos $permiso
 * @property Roles $role
 */
class RolesPermisos extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'roles_permisos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['role_id', 'permiso_id'], 'required'],
            [['role_id', 'permiso_id'], 'integer'],
            [['created_at'], 'safe'],
            [['role_id', 'permiso_id'], 'unique', 'targetAttribute' => ['role_id', 'permiso_id']],
            [['role_id'], 'exist', 'skipOnError' => true, 'targetClass' => Roles::class, 'targetAttribute' => ['role_id' => 'id']],
            [['permiso_id'], 'exist', 'skipOnError' => true, 'targetClass' => Permisos::class, 'targetAttribute' => ['permiso_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'role_id' => 'Role ID',
            'permiso_id' => 'Permiso ID',
            'created_at' => 'Created At',
        ];
    }

    /**
     * Gets query for [[Permiso]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPermiso()
    {
        return $this->hasOne(Permisos::class, ['id' => 'permiso_id']);
    }

    /**
     * Gets query for [[Role]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRole()
    {
        return $this->hasOne(Roles::class, ['id' => 'role_id']);
    }

}
