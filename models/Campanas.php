<?php

namespace app\models;

use Yii;

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
class Campanas extends \yii\db\ActiveRecord
{

    /**
     * ENUM field values
     */
    const ANALISIS_ANALIZAR = 'Analizar';
    const ANALISIS_PAUSAR = 'Pausar';
    const ANALISIS_DETENER = 'Detener';
    const ANALISIS_CONTINUAR = 'Continuar';
    const ANALISIS_EXPERIMENTO = 'Experimento';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'campanas';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['campaña_id', 'asesor_id', 'mensaje_predeterminado'], 'default', 'value' => null],
            [['presupuesto'], 'default', 'value' => 0.00],
            [['retorno'], 'default', 'value' => 0],
            [['analisis'], 'default', 'value' => 'Analizar'],
            [['campaña_id', 'asesor_id', 'mensajes', 'retorno'], 'integer'],
            [['inversion', 'presupuesto'], 'number'],
            [['analisis', 'mensaje_predeterminado'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            ['analisis', 'in', 'range' => array_keys(self::optsAnalisis())],
            [['asesor_id'], 'exist', 'skipOnError' => true, 'targetClass' => Catalogos::class, 'targetAttribute' => ['asesor_id' => 'id']],
            [['campaña_id'], 'exist', 'skipOnError' => true, 'targetClass' => Catalogos::class, 'targetAttribute' => ['campaña_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'campaña_id' => 'Campaña ID',
            'asesor_id' => 'Asesor ID',
            'inversion' => 'Inversion',
            'mensajes' => 'Mensajes',
            'retorno' => 'Retorno',
            'analisis' => 'Analisis',
            'mensaje_predeterminado' => 'Mensaje Predeterminado',
            'presupuesto' => 'Presupuesto',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Asesor]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAsesor()
    {
        return $this->hasOne(Catalogos::class, ['id' => 'asesor_id']);
    }

    /**
     * Gets query for [[Campaña]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCampaña()
    {
        return $this->hasOne(Catalogos::class, ['id' => 'campaña_id']);
    }

    /**
     * Gets query for [[Retornos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRetornos()
    {
        return $this->hasMany(Retorno::class, ['campaña_id' => 'id']);
    }


    /**
     * column analisis ENUM value labels
     * @return string[]
     */
    public static function optsAnalisis()
    {
        return [
            self::ANALISIS_ANALIZAR => 'Analizar',
            self::ANALISIS_PAUSAR => 'Pausar',
            self::ANALISIS_DETENER => 'Detener',
            self::ANALISIS_CONTINUAR => 'Continuar',
            self::ANALISIS_EXPERIMENTO => 'Experimento',
        ];
    }

    /**
     * @return string
     */
    public function displayAnalisis()
    {
        return self::optsAnalisis()[$this->analisis];
    }

    /**
     * @return bool
     */
    public function isAnalisisAnalizar()
    {
        return $this->analisis === self::ANALISIS_ANALIZAR;
    }

    public function setAnalisisToAnalizar()
    {
        $this->analisis = self::ANALISIS_ANALIZAR;
    }

    /**
     * @return bool
     */
    public function isAnalisisPausar()
    {
        return $this->analisis === self::ANALISIS_PAUSAR;
    }

    public function setAnalisisToPausar()
    {
        $this->analisis = self::ANALISIS_PAUSAR;
    }

    /**
     * @return bool
     */
    public function isAnalisisDetener()
    {
        return $this->analisis === self::ANALISIS_DETENER;
    }

    public function setAnalisisToDetener()
    {
        $this->analisis = self::ANALISIS_DETENER;
    }

    /**
     * @return bool
     */
    public function isAnalisisContinuar()
    {
        return $this->analisis === self::ANALISIS_CONTINUAR;
    }

    public function setAnalisisToContinuar()
    {
        $this->analisis = self::ANALISIS_CONTINUAR;
    }

    /**
     * @return bool
     */
    public function isAnalisisExperimento()
    {
        return $this->analisis === self::ANALISIS_EXPERIMENTO;
    }

    public function setAnalisisToExperimento()
    {
        $this->analisis = self::ANALISIS_EXPERIMENTO;
    }


public function getTipo()
{
    return $this->hasOne(Catalogos::class, ['id' => 'tipo']);
}

}
