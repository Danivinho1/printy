<?php

use yii\db\Migration;

class m231001_000001_create_notificacion_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%notificacion}}', [
            'id' => $this->primaryKey(),
            'usuario_id' => $this->integer()->notNull(),
            'mensaje' => $this->string(255)->notNull(),
            'tipo' => $this->string(30)->defaultValue('info'),
            'leida' => $this->boolean()->defaultValue(false),
            'created_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);
        $this->addForeignKey(
            'fk-notificacion-usuario_id',
            '{{%notificacion}}',
            'usuario_id',
            '{{%usuarios}}',
            'id',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-notificacion-usuario_id', '{{%notificacion}}');
        $this->dropTable('{{%notificacion}}');
    }
}