<?php

use Phinx\Migration\AbstractMigration;

class MonLicensing extends AbstractMigration
{
    // 20200417045921
    // migrate
    public function up()
    {
        if (!$this->hasTable('mon_licensing')) {
            $this->table('mon_licensing')
                ->addColumn('number', 'string', [
                    'default' => null,
                    'limit' => 250,
                    'null' => false
                ])
                ->addColumn('issue_date', 'date', [
                    'default' => null,
                    'null' => false
                ])
                ->addColumn('term_date', 'date', [
                    'default' => null,
                    'null' => false
                ])
                ->addColumn('base', 'string', [
                    'default' => null,
                    'limit' => 250,
                    'null' => false
                ])
                ->addColumn('file_name', 'string', [
                    'default' => null,
                    'limit' => 250,
                    'null' => true
                ])
                ->addColumn('file_content', 'blob', [
                    'default' => null,
                    'limit' => 16777215,
                    'null' => true
                ])
                ->addColumn('issuing_authority_id', 'integer', [
                    'default' => null,
                    'limit' => 11,
                    'null' => false
                ])
                ->addColumn('type_id', 'integer', [
                    'default' => null,
                    'limit' => 11,
                    'null' => false
                ])
                ->addColumn('institution_id', 'integer', [
                    'default' => null,
                    'limit' => 11,
                    'null' => false
                ])
                ->addColumn('academic_period_id', 'integer', [
                    'default' => null,
                    'limit' => 11,
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
                ->addIndex('issuing_authority_id')
                ->addIndex('type_id')
                ->addIndex('institution_id')
                ->addIndex('academic_period_id')
                ->addIndex('modified_user_id')
                ->addIndex('created_user_id')
                ->save();
        }
        
        if (!$this->hasTable('mon_licensing_issuing_authority')) {
            $this->table('mon_licensing_issuing_authority')
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
        
        if (!$this->hasTable('mon_licensing_type')) {
            $this->table('mon_licensing_type')
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

        $this->insert('security_functions', [
            'name' => 'Licensing',
            'controller' => 'Institutions',
            'module' => 'Institutions',
            'category' => 'General',
            'parent_id' => -1,
            '_view' => 'Licensing.index|Licensing.view',
            '_edit' => 'Licensing.edit',
            '_add' => 'Licensing.add',
            '_delete' => 'Licensing.remove',
            '_execute' => 'Licensing.download',
            'order' => 1,
            'visible' => 1,
            'created_user_id' => 1,
            'created' => date('Y-m-d H:i:s')
        ]);
    }

    // rollback
    public function down()
    {
        if ($this->hasTable('mon_licensing')) {
            $this->dropTable('mon_licensing');
        }
        
        if ($this->hasTable('mon_licensing_issuing_authority')) {
            $this->dropTable('mon_licensing_issuing_authority');
        }
        
        if ($this->hasTable('mon_licensing_type')) {
            $this->dropTable('mon_licensing_type');
        }

        $this->execute("DELETE FROM security_functions WHERE name = 'Licensing' AND controller = 'Institutions' AND category = 'General'");
    }
}
