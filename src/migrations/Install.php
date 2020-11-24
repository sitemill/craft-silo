<?php

namespace sitemill\silo\migrations;

use craft\db\Migration;
use craft\db\Table;
use craft\elements\Asset;

class Install extends Migration
{
    public function safeUp()
    {
        $this->createTables();
        $this->createIndexes();
        $this->addForeignKeys();
    }

    public function safeDown()
    {
        $this->dropTableIfExists('{{%silo_assets}}');
        //    TODO: create a delete migration
//        $this->deleteElementData();
    }

    /**
     * Creates the tables.
     */
    protected function createTables()
    {
        $this->createTable('{{%silo_assets}}', [
            'id' => $this->integer()->notNull(),
            'uploaderId' => $this->integer(),
            'assetId' => $this->integer(),
            'filename' => $this->string()->notNull(),
            'kind' => $this->string(50)->notNull()->defaultValue(Asset::KIND_UNKNOWN),
            'width' => $this->integer()->unsigned(),
            'height' => $this->integer()->unsigned(),
            'size' => $this->bigInteger()->unsigned(),
            'focalPoint' => $this->string(13)->null(),
            'approved' => $this->tinyInteger()->unsigned(),
            'downloads' => $this->integer()->defaultValue(0),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'dateDeleted' => $this->dateTime()->null(),
            'uid' => $this->uid(),
            'PRIMARY KEY([[id]])',
        ]);
    }

    /**
     * Creates the indexes.
     */
    protected function createIndexes()
    {
        $this->createIndex(null, '{{%silo_assets}}', ['id']);
    }

    /**
     * Adds the foreign keys.
     */
    protected function addForeignKeys()
    {
        $this->addForeignKey(null, '{{%silo_assets}}', ['id'], Table::ELEMENTS, ['id'], 'CASCADE');
        $this->addForeignKey(null, '{{%silo_assets}}', ['assetId'], Table::ASSETS, ['id'], 'CASCADE');
        $this->addForeignKey(null, '{{%silo_assets}}', ['uploaderId'], Table::USERS, ['id'], 'CASCADE');
    }
}