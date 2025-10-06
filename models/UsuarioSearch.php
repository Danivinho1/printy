<?php
namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class UsuarioSearch extends Usuario
{
    public function rules()
    {
        return [
            [['id', 'role_id', 'activo', 'intentos_fallidos'], 'integer'],
            [['username', 'email', 'nombre_completo', 'ultimo_login', 'created_at', 'updated_at'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Usuario::find()->with('role');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'role_id' => $this->role_id,
            'activo' => $this->activo,
        ]);

        $query
            ->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'nombre_completo', $this->nombre_completo]);

        return $dataProvider;
    }
}