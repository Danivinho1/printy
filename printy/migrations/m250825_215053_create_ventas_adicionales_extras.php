<?php

use yii\db\Migration;

class m250825_215053_create_ventas_adicionales_extras extends Migration
{
    public function safeUp()
    {
        // Tabla pivot para adicionales
        $this->createTable('ventas_adicionales', [
            'id' => $this->primaryKey(),
            'venta_id' => $this->integer()->notNull(),
            'adicional_id' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-ventas_adicionales-venta',
            'ventas_adicionales',
            'venta_id',
            'ventas',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-ventas_adicionales-adicional',
            'ventas_adicionales',
            'adicional_id',
            'catalogos',
            'id',
            'CASCADE'
        );

        // Tabla pivot para extras
        $this->createTable('ventas_extras', [
            'id' => $this->primaryKey(),
            'venta_id' => $this->integer()->notNull(),
            'extra_id' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-ventas_extras-venta',
            'ventas_extras',
            'venta_id',
            'ventas',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-ventas_extras-extra',
            'ventas_extras',
            'extra_id',
            'catalogos',
            'id',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable('ventas_extras');
        $this->dropTable('ventas_adicionales');
    }
}
