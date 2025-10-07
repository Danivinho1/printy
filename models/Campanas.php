<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "campanas".
 *
 * @property int $id
 * @property int|null $campaña_id Tipo campaña (catalogos.tipo = campaña)
 * @property int|null $asesor_id
 * @property float|null $inversion
 * @property int|null $mensajes
 * @property int|null $retorno
 * @property string|null $analisis
 * @property string|null $mensaje_predeterminado
 * @property float|null $presupuesto
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Catalogos $asesor
 * @property Catalogos $campaña
 * @property Retorno[] $retornos
 */
class Campanas extends ActiveRecord
{
    /**
     * ENUM field values
     */
    const ANALISIS_ANALIZAR   = 'Analizar';
    const ANALISIS_PAUSAR     = 'Pausar';
    const ANALISIS_DETENER    = 'Detener';
    const ANALISIS_CONTINUAR  = 'Continuar';
    const ANALISIS_EXPERIMENTO= 'Experimento';

    public static function tableName()
    {
        return 'campanas';
    }

    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                // Si tus columnas son DATETIME/TIMESTAMP:
                'value' => new Expression('NOW()'),
                // Si fueran enteros (UNIX time), usar: 'value' => time(),
            ],
        ];
    }

    public function rules()
    {
        return [
            [['nombre'], 'string', 'max' => 255],
            [['campaña_id', 'asesor_id', 'mensaje_predeterminado'], 'default', 'value' => null],
            [['presupuesto'], 'default', 'value' => 0.00],
            [['retorno'], 'default', 'value' => 0],
            [['analisis'], 'default', 'value' => self::ANALISIS_ANALIZAR],
            

            [['campaña_id', 'asesor_id', 'mensajes', 'retorno'], 'integer'],
            [['inversion', 'presupuesto'], 'number'],
            [['analisis', 'mensaje_predeterminado'], 'string'],

            // created_at / updated_at los maneja el behavior; permitir carga pero se sobrescriben
            [['created_at', 'updated_at'], 'safe'],

            ['analisis', 'in', 'range' => array_keys(self::optsAnalisis())],

            [['asesor_id'], 'exist', 'skipOnError' => true, 'targetClass' => Catalogos::class, 'targetAttribute' => ['asesor_id' => 'id']],
            [['campaña_id'], 'exist', 'skipOnError' => true, 'targetClass' => Catalogos::class, 'targetAttribute' => ['campaña_id' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'nombre' => 'Nombre',
            'id' => 'ID',
            'campaña_id' => 'Campaña',
            'asesor_id' => 'Asesor',
            'inversion' => 'Inversión',
            'mensajes' => 'Mensajes',
            'retorno' => 'Retorno',
            'analisis' => 'Análisis',
            'mensaje_predeterminado' => 'Mensaje Predeterminado',
            'presupuesto' => 'Presupuesto',
            'created_at' => 'Creado',
            'updated_at' => 'Actualizado',
        ];
    }

    /**
     * Relación con Asesor (Catalogos)
     * @return \yii\db\ActiveQuery
     */
    public function getAsesor()
    {
        return $this->hasOne(Catalogos::class, ['id' => 'asesor_id']);
    }

    /**
     * Relación con Campaña (Catalogos)
     * @return \yii\db\ActiveQuery
     */
    public function getCampaña()
    {
        return $this->hasOne(Catalogos::class, ['id' => 'campaña_id']);
    }

    /**
     * Relación con Retornos
     * @return \yii\db\ActiveQuery
     */
    public function getRetornos()
    {
        return $this->hasMany(Retorno::class, ['campaña_id' => 'id']);
    }

    /**
     * Opciones para el ENUM de análisis
     * @return string[]
     */
    public static function optsAnalisis()
    {
        return [
            self::ANALISIS_ANALIZAR    => 'Analizar',
            self::ANALISIS_PAUSAR      => 'Pausar',
            self::ANALISIS_DETENER     => 'Detener',
            self::ANALISIS_CONTINUAR   => 'Continuar',
            self::ANALISIS_EXPERIMENTO => 'Experimento',
        ];
    }

    /**
     * Etiqueta legible del análisis actual
     */
    public function displayAnalisis(): string
    {
        $opts = self::optsAnalisis();
        return $opts[$this->analisis] ?? (string)$this->analisis;
    }

    public function isAnalisisAnalizar(): bool { return $this->analisis === self::ANALISIS_ANALIZAR; }
    public function setAnalisisToAnalizar(): void { $this->analisis = self::ANALISIS_ANALIZAR; }

    public function isAnalisisPausar(): bool { return $this->analisis === self::ANALISIS_PAUSAR; }
    public function setAnalisisToPausar(): void { $this->analisis = self::ANALISIS_PAUSAR; }

    public function isAnalisisDetener(): bool { return $this->analisis === self::ANALISIS_DETENER; }
    public function setAnalisisToDetener(): void { $this->analisis = self::ANALISIS_DETENER; }

    public function isAnalisisContinuar(): bool { return $this->analisis === self::ANALISIS_CONTINUAR; }
    public function setAnalisisToContinuar(): void { $this->analisis = self::ANALISIS_CONTINUAR; }

    public function isAnalisisExperimento(): bool { return $this->analisis === self::ANALISIS_EXPERIMENTO; }
    public function setAnalisisToExperimento(): void { $this->analisis = self::ANALISIS_EXPERIMENTO; }
}