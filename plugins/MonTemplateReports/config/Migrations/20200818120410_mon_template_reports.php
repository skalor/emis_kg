<?php
use Phinx\Migration\AbstractMigration;

class MonTemplateReports extends AbstractMigration
{
    // migrate
    public function up()
    {
        if (!$this->hasTable('mon_template_reports')) {
            $this->table('mon_template_reports')
                ->addColumn('name', 'string', [
                    'default' => null,
                    'limit' => 250,
                    'null' => false
                ])
                ->addColumn('code', 'string', [
                    'default' => null,
                    'limit' => 100,
                    'null' => false
                ])
                ->addColumn('type_id', 'integer', [
                    'default' => null,
                    'limit' => 11,
                    'null' => false
                ])->addIndex('type_id')
                ->addColumn('content', 'text', [
                    'default' => null,
                    'limit' => 16777215,
                    'null' => true
                ])
                ->addColumn('modified_user_id', 'integer', [
                    'default' => null,
                    'limit' => 11,
                    'null' => true
                ])->addIndex('modified_user_id')
                ->addColumn('modified', 'datetime', [
                    'default' => null,
                    'null' => true
                ])
                ->addColumn('created_user_id', 'integer', [
                    'default' => null,
                    'limit' => 11,
                    'null' => false
                ])->addIndex('created_user_id')
                ->addColumn('created', 'datetime', [
                    'default' => null,
                    'null' => false
                ])
                ->save();
        }

        if (!$this->hasTable('mon_template_types')) {
            $this->table('mon_template_types')
                ->addColumn('name', 'string', [
                    'default' => null,
                    'limit' => 50,
                    'null' => false
                ])
                ->addColumn('order', 'integer', [
                    'default' => null,
                    'limit' => 3,
                    'null' => false
                ])
                ->addColumn('visible', 'integer', [
                    'default' => 1,
                    'limit' => 1,
                    'null' => false
                ])
                ->addColumn('editable', 'integer', [
                    'default' => 1,
                    'limit' => 1,
                    'null' => false
                ])
                ->addColumn('default', 'integer', [
                    'default' => 0,
                    'limit' => 1,
                    'null' => false
                ])
                ->addColumn('international_code', 'string', [
                    'default' => null,
                    'limit' => 50,
                    'null' => true
                ])
                ->addColumn('national_code', 'string', [
                    'default' => null,
                    'limit' => 50,
                    'null' => true
                ])
                ->addColumn('modified_user_id', 'integer', [
                    'default' => null,
                    'limit' => 11,
                    'null' => true
                ])->addIndex('modified_user_id')
                ->addColumn('modified', 'datetime', [
                    'default' => null,
                    'null' => true
                ])
                ->addColumn('created_user_id', 'integer', [
                    'default' => null,
                    'limit' => 11,
                    'null' => false
                ])->addIndex('created_user_id')
                ->addColumn('created', 'datetime', [
                    'default' => null,
                    'null' => false
                ])
                ->save();
        }
    }

    // rollback
    public function down()
    {
        if ($this->hasTable('mon_template_reports')) {
            $this->dropTable('mon_template_reports');
        }

        if ($this->hasTable('mon_template_types')) {
            $this->dropTable('mon_template_types');
        }
    }
}
