<?php

use yii\db\Schema;

class m180927_000101_key_storage_item extends \yii\db\Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }
        
        $this->createTable('key_storage_item', [
            'key' => $this->string(128)->notNull(),
            'value' => $this->text()->notNull(),
            'comment' => $this->text(),
            'updated_at' => $this->integer(11),
            'created_at' => $this->integer(11),
            'PRIMARY KEY ([[key]])',
            ], $tableOptions);
                
    }

    public function down()
    {
        $this->dropTable('key_storage_item');
    }
}
