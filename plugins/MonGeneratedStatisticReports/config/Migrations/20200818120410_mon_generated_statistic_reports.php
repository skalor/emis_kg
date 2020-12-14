<?php

use Phinx\Migration\AbstractMigration;

class MonGeneratedStatisticReports extends AbstractMigration
{
    // migrate
    public function up()
    {
        if (!$this->hasTable('mon_generated_statistic_reports')) {
            $this->table('mon_generated_statistic_reports')
                ->addColumn('mon_statistic_report_id', 'integer', [
                    'default' => null,
                    'limit' => 11,
                    'null' => false
                ])->addIndex('mon_statistic_report_id')
                ->addColumn('institution_id', 'integer', [
                    'default' => null,
                    'limit' => 11,
                    'null' => true
                ])->addIndex('institution_id')
                ->addColumn('academic_period_id', 'integer', [
                    'default' => null,
                    'limit' => 11,
                    'null' => false
                ])->addIndex('academic_period_id')
                ->addColumn('file_type', 'string', [
                    'default' => null,
                    'limit' => 100,
                    'null' => false
                ])
                ->addColumn('upload', 'boolean', [
                    'default' => 0,
                    'null' => false
                ])
                ->addColumn('file_name', 'string', [
                    'default' => null,
                    'limit' => 250,
                    'null' => true
                ])
                ->addColumn('file_content', 'blob', [
                    'default' => null,
                    'limit' => 4294967295,
                    'null' => true
                ])
                ->addColumn('file_name_hash', 'string', [
                    'default' => null,
                    'limit' => 250,
                    'null' => true
                ])
                ->addColumn('file_content_hash', 'blob', [
                    'default' => null,
                    'limit' => 4294967295,
                    'null' => true
                ])
                ->addColumn('is_signed', 'boolean', [
                    'default' => 0,
                    'null' => true
                ])
                ->addColumn('signed_by_id', 'integer', [
                    'default' => null,
                    'limit' => 11,
                    'null' => true
                ])->addIndex('signed_by_id')
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

        $this->insert('security_functions', [
            'name' => 'MonGeneratedStatisticReports',
            'controller' => 'MonGeneratedStatisticReports',
            'module' => 'Reports',
            'category' => 'Reports',
            'parent_id' => -1,
            '_view' => 'index|view',
            '_edit' => 'edit',
            '_add' => 'add',
            '_delete' => 'delete',
            '_execute' => 'download|sign|checkGeneratingProgress',
            'order' => 1,
            'visible' => 1,
            'created_user_id' => 1,
            'created' => date('Y-m-d H:i:s')
        ]);
    }

    // rollback
    public function down()
    {
        if ($this->hasTable('mon_generated_statistic_reports')) {
            $this->dropTable('mon_generated_statistic_reports');
        }
        $this->execute("DELETE FROM security_functions WHERE name = 'MonGeneratedStatisticReports' AND controller = 'MonGeneratedStatisticReports'");
    }
}
