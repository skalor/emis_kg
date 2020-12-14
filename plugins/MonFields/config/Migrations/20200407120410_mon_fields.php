<?php

use Phinx\Migration\AbstractMigration;

class MonFields extends AbstractMigration
{
    // migrate
    public function up()
    {
        // mon_fields
        $table = $this->table('mon_fields', [
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => 'Custom fields for all models'
            ]);
        $table->addColumn('name', 'string', [
                'default' => null,
                'limit' => 250,
                'null' => false
            ])
            ->addColumn('length', 'integer', [
                'default' => 0,
                'limit' => 11,
                'null' => false,
                'signed' => false
            ])
            ->addColumn('field_type', 'string', [
                'default' => null,
                'limit' => 250,
                'null' => false
            ])
            ->addColumn('is_mandatory', 'integer', [
                'default' => 0,
                'limit' => 1,
                'null' => false,
                'signed' => false
            ])
            ->addColumn('model', 'string', [
                'default' => null,
                'limit' => 250,
                'null' => false
            ])
            ->addColumn('after_field', 'string', [
                'default' => null,
                'limit' => 250,
                'null' => true
            ])
            ->addColumn('params', 'text', [
                'default' => null,
                'null' => true
            ])
            ->addColumn('description', 'text', [
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
            ->addIndex('modified_user_id')
            ->addIndex('created_user_id')
            ->save();

        // mon_dropdowns
        $table = $this->table('mon_dropdowns', [
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => 'Dropdowns for all models'
            ]);
        $table->addColumn('values', 'text', [
                'default' => null,
                'null' => true
            ])
            ->addColumn('model', 'string', [
                'default' => null,
                'limit' => 250,
                'null' => true
            ])
            ->addColumn('field_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true
            ])
            ->addIndex('field_id')
            ->save();
    }

    // rollback
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS mon_fields, mon_dropdowns');
    }
}
