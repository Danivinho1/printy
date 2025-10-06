<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Modelo simple para metas de ventas
 * 
 * @property int $id
 * @property string $tipo
 * @property string $periodo
 * @property float $valor
 * @property int $usuario_id
 * @property string $fecha_creado
 * @property string $fecha_actualizado
 */
class Metas extends ActiveRecord
{
    // Constantes para tipos de meta
    const TIPO_UNIDADES = 'unidades';
    const TIPO_DINERO = 'dinero';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%metas}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tipo', 'periodo', 'valor'], 'required'],
            [['tipo'], 'in', 'range' => [self::TIPO_UNIDADES, self::TIPO_DINERO]],
            [['periodo'], 'string', 'max' => 7],
            [['periodo'], 'match', 'pattern' => '/^\d{4}-\d{2}$/', 'message' => 'El período debe tener formato YYYY-MM'],
            [['valor'], 'number', 'min' => 0],
            [['usuario_id'], 'integer'],
            [['fecha_creado', 'fecha_actualizado'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tipo' => 'Tipo de Meta',
            'periodo' => 'Período',
            'valor' => 'Valor',
            'usuario_id' => 'Usuario',
            'fecha_creado' => 'Fecha Creado',
            'fecha_actualizado' => 'Fecha Actualizado',
        ];
    }

    /**
     * Obtiene la meta de unidades para un período específico
     * @param string $periodo Formato YYYY-MM
     * @return float
     */
    public static function getMetaUnidades($periodo = null)
    {
        $periodo = $periodo ?: date('Y-m');
        
        $meta = self::find()
            ->where(['tipo' => self::TIPO_UNIDADES, 'periodo' => $periodo])
            ->one();
            
        return $meta ? $meta->valor : 1000; // valor por defecto
    }

    /**
     * Obtiene la meta de dinero para un período específico  
     * @param string $periodo Formato YYYY-MM
     * @return float
     */
    public static function getMetaDinero($periodo = null)
    {
        $periodo = $periodo ?: date('Y-m');
        
        $meta = self::find()
            ->where(['tipo' => self::TIPO_DINERO, 'periodo' => $periodo])
            ->one();
            
        return $meta ? $meta->valor : 50000.00; // valor por defecto
    }

    /**
     * Establece la meta de unidades para un período
     * @param int $valor
     * @param string $periodo Formato YYYY-MM
     * @return bool
     */
    public static function setMetaUnidades($valor, $periodo = null)
    {
        $periodo = $periodo ?: date('Y-m');
        
        $meta = self::find()
            ->where(['tipo' => self::TIPO_UNIDADES, 'periodo' => $periodo])
            ->one();
            
        if (!$meta) {
            $meta = new self();
            $meta->tipo = self::TIPO_UNIDADES;
            $meta->periodo = $periodo;
        }
        
        $meta->valor = $valor;
        $meta->usuario_id = Yii::$app->user->id ?? null;
        
        return $meta->save();
    }

    /**
     * Establece la meta de dinero para un período
     * @param float $valor
     * @param string $periodo Formato YYYY-MM  
     * @return bool
     */
    public static function setMetaDinero($valor, $periodo = null)
    {
        $periodo = $periodo ?: date('Y-m');
        
        $meta = self::find()
            ->where(['tipo' => self::TIPO_DINERO, 'periodo' => $periodo])
            ->one();
            
        if (!$meta) {
            $meta = new self();
            $meta->tipo = self::TIPO_DINERO;
            $meta->periodo = $periodo;
        }
        
        $meta->valor = $valor;
        $meta->usuario_id = Yii::$app->user->id ?? null;
        
        return $meta->save();
    }

    /**
     * Obtiene todas las metas de un período
     * @param string $periodo Formato YYYY-MM
     * @return array
     */
    public static function getMetasPeriodo($periodo = null)
    {
        $periodo = $periodo ?: date('Y-m');
        
        return [
            'unidades' => self::getMetaUnidades($periodo),
            'dinero' => self::getMetaDinero($periodo),
            'periodo' => $periodo
        ];
    }

    /**
     * Obtiene historial de metas
     * @param int $limit
     * @return array
     */
    public static function getHistorial($limit = 12)
    {
        return self::find()
            ->orderBy(['periodo' => SORT_DESC, 'fecha_actualizado' => SORT_DESC])
            ->limit($limit)
            ->all();
    }

    /**
     * Verifica si existe una meta para el período actual
     * @param string $tipo
     * @param string $periodo
     * @return bool
     */
    public static function existeMeta($tipo, $periodo = null)
    {
        $periodo = $periodo ?: date('Y-m');
        
        return self::find()
            ->where(['tipo' => $tipo, 'periodo' => $periodo])
            ->exists();
    }

    /**
     * Crea las metas por defecto si no existen
     * @param string $periodo
     * @return bool
     */
    public static function crearMetasPorDefecto($periodo = null)
    {
        $periodo = $periodo ?: date('Y-m');
        
        $success = true;
        
        // Crear meta de unidades si no existe
        if (!self::existeMeta(self::TIPO_UNIDADES, $periodo)) {
            $success = $success && self::setMetaUnidades(1000, $periodo);
        }
        
        // Crear meta de dinero si no existe  
        if (!self::existeMeta(self::TIPO_DINERO, $periodo)) {
            $success = $success && self::setMetaDinero(50000.00, $periodo);
        }
        
        return $success;
    }

    /**
     * Formatea el valor según el tipo
     * @return string
     */
    public function getValorFormateado()
    {
        if ($this->tipo === self::TIPO_DINERO) {
            return '$' . number_format($this->valor, 2);
        } else {
            return number_format($this->valor);
        }
    }

    /**
     * Obtiene el nombre del mes para el período
     * @return string
     */
    public function getNombrePeriodo()
    {
        $fecha = $this->periodo . '-01';
        return date('F Y', strtotime($fecha));
    }
}

?>