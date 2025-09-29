<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * @property int $id
 * @property string $username
 * @property string|null $email
 * @property string $password_hash
 * @property string|null $nombre_completo
 * @property int $role_id
 * @property int $activo
 * @property string|null $ultimo_login
 * @property int $intentos_fallidos
 * @property string|null $bloqueado_hasta
 *
 * @property Role $role
 */
class Usuario extends ActiveRecord implements IdentityInterface
{
    public $new_password; // para formularios (opcional)

    public static function tableName()
    {
        return 'usuarios';
    }

    public function rules()
    {
        return [
            [['username', 'role_id'], 'required'],
            [['email'], 'email'],
            [['role_id', 'intentos_fallidos'], 'integer'],
            [['activo'], 'boolean'],
            [['username'], 'string', 'max' => 50],
            [['email', 'nombre_completo'], 'string', 'max' => 100],
            [['username'], 'unique'],
            [['email'], 'unique'],
            [['new_password'], 'string', 'min' => 6],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Usuario',
            'email' => 'Email',
            'password_hash' => 'Password Hash',
            'nombre_completo' => 'Nombre completo',
            'role_id' => 'Rol',
            'activo' => 'Activo',
            'new_password' => 'Contraseña (nueva)',
        ];
    }

    public function getRole()
    {
        return $this->hasOne(Role::class, ['id' => 'role_id']);
    }

    // ----------------- IdentityInterface -----------------
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'activo' => 1]);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return null;
    }

    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'activo' => 1]);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        return null;
    }

    public function validateAuthKey($authKey)
    {
        return true;
    }

    public function validatePassword($password)
    {
        if (empty($this->password_hash)) {
            return false;
        }
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public function setPassword(string $password): void
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    // ----------------- Autorización -----------------
    /**
     * Comprueba si el usuario tiene permiso para $modulo::$accion
     * - Normaliza inputs (lowercase + trim)
     * - Devuelve true si el role tiene es_admin = 1
     * - Cachea permisos por role_id con key "role_perms_{roleId}" (TTL 3600s)
     * - Soporta permiso global admin::all si existe
     *
     * @param string $modulo
     * @param string $accion
     * @return bool
     */
    public function can($modulo, $accion = 'index'): bool
    {
        // Normalizar inputs
        $modulo = mb_strtolower(trim((string)$modulo));
        $accion = mb_strtolower(trim((string)$accion));

        // role_id valido?
        $roleId = (int)$this->role_id;
        if ($roleId <= 0) {
            Yii::info("can(): user {$this->id} sin role_id válido", __METHOD__);
            return false;
        }

        // Fast-path: rol administrador
        if ($this->role && (int)$this->role->es_admin === 1) {
            Yii::info("can(): user {$this->id} es_admin => ALLOW", __METHOD__);
            return true;
        }

        $cacheKey = "role_perms_{$roleId}";
        $perms = null;
        try {
            $perms = Yii::$app->cache->get($cacheKey);
        } catch (\Throwable $e) {
            // Si la cache falla no queremos bloquear la comprobación: seguiremos consultando BD
            Yii::warning("can(): fallo al leer cache {$cacheKey}: " . $e->getMessage(), __METHOD__);
            $perms = false;
        }

        if ($perms === false) {
            // Cargar permisos desde BD
            $rows = (new \yii\db\Query())
                ->select(['p.modulo', 'p.accion'])
                ->from('permisos p')
                ->innerJoin('roles_permisos rp', 'rp.permiso_id = p.id')
                ->where(['rp.role_id' => $roleId, 'p.activo' => 1])
                ->all();

            $perms = [];
            foreach ($rows as $r) {
                $k = mb_strtolower(trim($r['modulo'])) . '::' . mb_strtolower(trim($r['accion']));
                $perms[$k] = true;
            }

            try {
                Yii::$app->cache->set($cacheKey, $perms, 3600);
            } catch (\Throwable $e) {
                Yii::warning("can(): fallo al escribir cache {$cacheKey}: " . $e->getMessage(), __METHOD__);
            }

            Yii::info("can(): cache cargado para role {$roleId} con " . count($perms) . " permisos", __METHOD__);
        }

        // Soporte para permiso global admin::all
        if (!empty($perms['admin::all'])) {
            Yii::info("can(): user {$this->id} tiene admin::all => ALLOW", __METHOD__);
            return true;
        }

        $key = $modulo . '::' . $accion;
        $result = !empty($perms[$key]);

        Yii::info("can(): user {$this->id} check {$key} => " . ($result ? 'ALLOW' : 'DENY'), __METHOD__);

        return $result;
    }
}