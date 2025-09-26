<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%notificacion}}`.
 */
class m250926_104000_create_notificacion_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%notificacion}}', [
            'id' => $this->primaryKey(),
            'usuario_id' => $this->integer()->notNull(),
            'mensaje' => $this->text()->notNull(),
            'leido' => $this->boolean()->notNull()->defaultValue(false),
            'enlace' => $this->string(255),
            'fecha_creacion' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        // Creates index for column `usuario_id`
        $this->createIndex(
            '{{%idx-notificacion-usuario_id}}',
            '{{%notificacion}}',
            'usuario_id'
        );

        // Adds foreign key for table `{{%usuarios}}`
        // IMPORTANTE: Asegúrate de que tu tabla de usuarios se llama 'usuarios'
        $this->addForeignKey(
            '{{%fk-notificacion-usuario_id}}',
            '{{%notificacion}}',
            'usuario_id',
            '{{%usuarios}}', // Revisa que este sea el nombre correcto de tu tabla de usuarios
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drops foreign key for table `{{%usuarios}}`
        $this->dropForeignKey(
            '{{%fk-notificacion-usuario_id}}',
            '{{%notificacion}}'
        );

        // Drops index for column `usuario_id`
        $this->dropIndex(
            '{{%idx-notificacion-usuario_id}}',
            '{{%notificacion}}'
        );

        $this->dropTable('{{%notificacion}}');
    }
}
