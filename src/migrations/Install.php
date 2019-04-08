<?php

namespace nilsenpaul\cookieconsent\migrations;

use craft\db\Migration;

class Install extends Migration
{
    public function safeUp()
    {
        // Create the table
        $this->createTable('{{%completecookieconsent_settings}}', [
            'id' => $this->primaryKey(),
            'siteId' => $this->integer()->notNull(),
            'settings' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);   
    }

    public function safeDown()
    {

    }
}
