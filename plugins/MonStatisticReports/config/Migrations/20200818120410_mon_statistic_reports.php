<?php

use Phinx\Migration\AbstractMigration;

class MonStatisticReports extends AbstractMigration
{
    // migrate
    public function up()
    {
        if (!$this->hasTable('mon_statistic_reports')) {
            $this->table('mon_statistic_reports')
                ->addColumn('name', 'string', [
                    'default' => null,
                    'limit' => 100,
                    'null' => false
                ])
                ->addColumn('full_name', 'string', [
                    'default' => null,
                    'limit' => 250,
                    'null' => false
                ])
                ->addColumn('code', 'string', [
                    'default' => null,
                    'limit' => 100,
                    'null' => false
                ])
                ->addColumn('for_date', 'date', [
                    'default' => null,
                    'null' => false
                ])
                ->addColumn('template_id', 'integer', [
                    'default' => null,
                    'limit' => 11,
                    'null' => false
                ])->addIndex('template_id')
                ->addColumn('params', 'text', [
                    'default' => null,
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
        if ($this->hasTable('mon_statistic_reports')) {
            $this->dropTable('mon_statistic_reports');
        }
    }
}
