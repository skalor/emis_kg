<?php

use Phinx\Migration\AbstractMigration;

class MonSections extends AbstractMigration
{
    // migrate
    public function up()
    {
        $table = $this->table('mon_sections', [
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => 'Sections for all models'
            ]);
        $table->addColumn('name', 'string', [
                'default' => null,
                'limit' => 250,
                'null' => false
            ])
            ->addColumn('model', 'string', [
                'default' => null,
                'limit' => 250,
                'null' => false
            ])
            ->addColumn('before_field', 'string', [
                'default' => null,
                'limit' => 250,
                'null' => false
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
            ->addIndex('modified_user_id')
            ->addIndex('created_user_id')
            ->save();
    }

    // rollback
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS mon_sections');
    }
}
