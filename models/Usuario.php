<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "usuarios".
 */
class Usuario extends ActiveRecord implements IdentityInterface
{
    public $password;
    public $password_repeat;

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
            [['username', 'role_id'], 'required'],
            ['password', 'required', 'on' => 'create'],
            ['password', 'string', 'min' => 6],
            ['password_repeat', 'compare', 'compareAttribute' => 'password', 'skipOnEmpty' => false, 'on' => 'create'],
            [['role_id', 'activo', 'intentos_fallidos'], 'integer'],
            [['ultimo_login', 'bloqueado_hasta'], 'safe'],
            [['username'], 'string', 'max' => 50],
            [['email'], 'string', 'max' => 100],
            [['email'], 'email'],
            [['password_hash'], 'string', 'max' => 255],
            [['nombre_completo'], 'string', 'max' => 100],
            [['username'], 'unique'],
            [['email'], 'unique'],
            [['role_id'], 'exist', 'skipOnError' => true, 'targetClass' => Role::class, 'targetAttribute' => ['role_id' => 'id']],
            ['activo', 'default', 'value' => 1],
            ['intentos_fallidos', 'default', 'value' => 0],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Usuario',
            'email' => 'Email',
            'password' => 'Contraseña',
            'password_repeat' => 'Repetir Contraseña',
            'password_hash' => 'Password Hash',
            'nombre_completo' => 'Nombre Completo',
            'role_id' => 'Rol',
            'activo' => 'Activo',
            'ultimo_login' => 'Último Login',
            'intentos_fallidos' => 'Intentos Fallidos',
            'bloqueado_hasta' => 'Bloqueado Hasta',
            'created_at' => 'Creado',
            'updated_at' => 'Actualizado',
        ];
    }

    /**
     * Gets query for related role.
     */
    public function getRole()
    {
        return $this->hasOne(Role::class, ['id' => 'role_id']);
    }

    /**
     * Gets query for related auditoria accesos.
     */
    public function getAuditoriaAccesos()
    {
        return $this->hasMany(AuditoriaAcceso::class, ['usuario_id' => 'id']);
    }

    // IdentityInterface methods
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'activo' => 1]);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return null; // Not implemented for basic auth
    }

    public function getId()
    {
        return $this->getPrimaryKey();
    }

    public function getAuthKey()
    {
        return $this->password_hash;
    }

    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'activo' => 1]);
    }

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    // Before save event
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord || !empty($this->password)) {
                $this->setPassword($this->password);
            }
            return true;
        }
        return false;
    }

    // Scenarios
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['create'] = ['username', 'email', 'password', 'password_repeat', 'nombre_completo', 'role_id', 'activo'];
        $scenarios['update'] = ['username', 'email', 'password', 'password_repeat', 'nombre_completo', 'role_id', 'activo'];
        return $scenarios;
    }

    // Check if user has permission
    public function hasPermission($modulo, $accion)
    {
        if (!$this->role) {
            return false;
        }

        // If is admin, allow all
        if ($this->role->es_admin) {
            return true;
        }

        // Check specific permission
        $permiso = Permiso::find()
            ->joinWith('rolesPermisos')
            ->where(['permisos.modulo' => $modulo, 'permisos.accion' => $accion])
            ->andWhere(['roles_permisos.role_id' => $this->role_id])
            ->one();

        return $permiso !== null;
    }

    // Get status label
    public function getStatusLabel()
    {
        return $this->activo ? 'Activo' : 'Inactivo';
    }
}