<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Role ActiveRecord
 */
class Role extends ActiveRecord
{
    public $permisos = []; // Permisos seleccionados en el form (virtual)

    public static function tableName()
    {
        return 'roles';
    }

    public function rules()
    {
        return [
            [['nombre'], 'required'],
            [['descripcion'], 'string'],
            [['es_admin', 'activo'], 'integer'],
            [['nombre'], 'string', 'max' => 50],
            [['permisos'], 'safe'], // ¡Agregar esto!
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nombre' => 'Nombre',
            'descripcion' => 'Descripción',
            'es_admin' => 'Es admin',
            'activo' => 'Activo',
            'created_at' => 'Creado',
            'updated_at' => 'Actualizado',
            'permisos' => 'Permisos', // Opcional, para el form
        ];
    }

    // Relación para traer permisos asignados
    public function getPermisos()
    {
        return $this->hasMany(Permiso::class, ['id' => 'permiso_id'])
            ->viaTable('roles_permisos', ['role_id' => 'id']);
    }

    public function getPermisosList(): array
    {
        return ArrayHelper::map($this->permisos, 'id', 'nombre');
    }

    // Cargar permisos actuales al abrir el form
    public function afterFind()
    {
        parent::afterFind();
        $this->permisos = ArrayHelper::getColumn($this->getPermisos()->asArray()->all(), 'id');
    }

    // Guardar permisos al guardar el rol (desde el form)
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        $this->assignPermisos($this->permisos); // Usa tu método batch robusto
    }

    public function assignPermisos(?array $permisoIds): bool
    {
        $permisoIds = $permisoIds === null ? [] : array_values(array_filter($permisoIds, 'strlen'));
        $db = static::getDb();
        $tx = $db->beginTransaction();
        try {
            $db->createCommand()->delete('roles_permisos', ['role_id' => $this->id])->execute();
            if (!empty($permisoIds)) {
                $rows = [];
                $now = date('Y-m-d H:i:s');
                foreach ($permisoIds as $pid) {
                    $rows[] = [$this->id, (int)$pid, $now];
                }
                $db->createCommand()->batchInsert('roles_permisos', ['role_id', 'permiso_id', 'created_at'], $rows)->execute();
            }
            $tx->commit();
            $this->clearPermsCache();
            return true;
        } catch (\Throwable $e) {
            $tx->rollBack();
            Yii::error("Error assignPermisos for role {$this->id}: " . $e->getMessage(), __METHOD__);
            throw $e;
        }
    }

    public function clearPermsCache(): void
    {
        if ($this->id) {
            try {
                Yii::$app->cache->delete("role_perms_{$this->id}");
            } catch (\Throwable $e) {
                Yii::warning("No se pudo borrar cache role_perms_{$this->id}: " . $e->getMessage(), __METHOD__);
            }
        }
    }
}