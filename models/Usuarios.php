<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "usuarios".
 *
 * @property int $id
 * @property string $username
 * @property string|null $email
 * @property string $password_hash
 * @property string|null $nombre_completo
 * @property int $role_id
 * @property int|null $activo
 * @property string|null $ultimo_login
 * @property int|null $intentos_fallidos
 * @property string|null $bloqueado_hasta
 * @property string $created_at
 * @property string $updated_at
 *
 * @property AuditoriaAcceso[] $auditoriaAccesos
 * @property Diseno[] $disenos
 * @property Produccion[] $produccions
 * @property Roles $role
 * @property Ventas[] $ventas
 */
class Usuarios extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'usuarios';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['email', 'nombre_completo', 'ultimo_login', 'bloqueado_hasta'], 'default', 'value' => null],
            [['activo'], 'default', 'value' => 1],
            [['intentos_fallidos'], 'default', 'value' => 0],
            [['username', 'password_hash', 'role_id'], 'required'],
            [['role_id', 'activo', 'intentos_fallidos'], 'integer'],
            [['ultimo_login', 'bloqueado_hasta', 'created_at', 'updated_at'], 'safe'],
            [['username'], 'string', 'max' => 50],
            [['email', 'nombre_completo'], 'string', 'max' => 100],
            [['password_hash'], 'string', 'max' => 255],
            [['username'], 'unique'],
            [['email'], 'unique'],
            [['role_id'], 'exist', 'skipOnError' => true, 'targetClass' => Roles::class, 'targetAttribute' => ['role_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'email' => 'Email',
            'password_hash' => 'Password Hash',
            'nombre_completo' => 'Nombre Completo',
            'role_id' => 'Role ID',
            'activo' => 'Activo',
            'ultimo_login' => 'Ultimo Login',
            'intentos_fallidos' => 'Intentos Fallidos',
            'bloqueado_hasta' => 'Bloqueado Hasta',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[AuditoriaAccesos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAuditoriaAccesos()
    {
        return $this->hasMany(AuditoriaAcceso::class, ['usuario_id' => 'id']);
    }

    /**
     * Gets query for [[Disenos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDisenos()
    {
        return $this->hasMany(Diseno::class, ['responsable_id' => 'id']);
    }

    /**
     * Gets query for [[Produccions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProduccions()
    {
        return $this->hasMany(Produccion::class, ['disenador_id' => 'id']);
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

    /**
     * Gets query for [[Ventas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVentas()
    {
        return $this->hasMany(Ventas::class, ['created_by' => 'id']);
    }

}
