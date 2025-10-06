<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "retorno".
 *
 * @property int $id
 * @property int $campaña_id
 * @property int|null $asesor_id
 * @property float|null $cantidad
 * @property string $created_at
 *
 * @property Catalogos $asesor
 * @property Campanas $campaña
 */
class Retorno extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'retorno';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['asesor_id'], 'default', 'value' => null],
            [['cantidad'], 'default', 'value' => 0.00],
            [['campaña_id'], 'required'],
            [['campaña_id', 'asesor_id'], 'integer'],
            [['cantidad'], 'number'],
            [['created_at'], 'safe'],
            [['asesor_id'], 'exist', 'skipOnError' => true, 'targetClass' => Catalogos::class, 'targetAttribute' => ['asesor_id' => 'id']],
            [['campaña_id'], 'exist', 'skipOnError' => true, 'targetClass' => Campanas::class, 'targetAttribute' => ['campaña_id' => 'id']],
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
            'cantidad' => 'Cantidad',
            'created_at' => 'Created At',
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
        return $this->hasOne(Campanas::class, ['id' => 'campaña_id']);
    }

    public function getCampana()
{
    return $this->hasOne(Campanas::class, ['id' => 'campaña_id']);
}



}
