<?php

use Phinx\Migration\AbstractMigration;

class MonApi extends AbstractMigration
{
    // migrate
    public function up()
    {
        if (!$this->hasTable('mon_api')) {
            $this->table('mon_api', [
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => 'MonAPI access control'
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 250,
                'null' => false
            ])
            ->addColumn('user_id', 'string', [
                'default' => null,
                'limit' => 11,
                'null' => false
            ])
            ->addColumn('description', 'text', [
                'default' => null,
                'null' => true
            ])
            ->addColumn('params', 'text', [
                'default' => null,
                'null' => true
            ])
            ->addColumn('modified_user_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'null' => true
            ])
            ->addColumn('created_user_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'null' => false
            ])
            ->addIndex('user_id')
            ->addIndex('modified_user_id')
            ->addIndex('created_user_id')
            ->save();
        }
    }

    // rollback
    public function down()
    {
        if ($this->hasTable('mon_api')) {
            $this->dropTable('mon_api');
        }
    }
}
