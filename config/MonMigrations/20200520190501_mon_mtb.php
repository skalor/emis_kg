<?php

use Phinx\Migration\AbstractMigration;

class MonMtb extends AbstractMigration
{
    // 20200520190501
    // migrate
    public function up()
    {
        if (!$this->hasTable('mon_mtb')) {
            $this->table('mon_mtb')
                ->addColumn('number_of_computer_classes', 'integer', [
                    'default' => null,
                    'limit' => 11,
                    'null' => false
                ])
                ->addColumn('number_of_computers', 'integer', [
                    'default' => null,
                    'limit' => 11,
                    'null' => false
                ])
                ->addColumn('number_of_computers_connected_to_the_internet', 'integer', [
                    'default' => null,
                    'limit' => 11,
                    'null' => false
                ])
                ->addColumn('number_of_computers_connected_to_the_internet_for_education', 'integer', [
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
                ->addIndex('institution_id')
                ->addIndex('academic_period_id')
                ->addIndex('modified_user_id')
                ->addIndex('created_user_id')
                ->save();

            $this->table('infrastructure_utility_internets')
                ->addColumn('internet_availability', 'integer', [
                    'default' => null,
                    'limit' => 1,
                    'null' => false
                ])
                ->addColumn('internet_provider_id', 'integer', [
                    'default' => null,
                    'limit' => 11,
                    'null' => false
                ])
                ->addIndex('internet_provider_id')
                ->save();
        }
        
        if (!$this->hasTable('mon_utility_internet_provider')) {
            $this->table('mon_utility_internet_provider')
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
            'name' => 'MTB',
            'controller' => 'MonMtb',
            'module' => 'Institutions',
            'category' => 'Details',
            'parent_id' => 8,
            '_view' => 'index|view',
            '_edit' => 'edit',
            '_add' => 'add',
            '_delete' => 'delete',
            'order' => 1,
            'visible' => 1,
            'created_user_id' => 1,
            'created' => date('Y-m-d H:i:s')
        ]);
    }

    // rollback
    public function down()
    {
        if ($this->hasTable('mon_mtb')) {
            $this->dropTable('mon_mtb');
            $this->dropTable('mon_utility_internet_provider');
            $this->table('infrastructure_utility_internets')
                ->removeColumn('internet_availability')
                ->removeColumn('internet_provider_id')
                ->save();
        }

        $this->execute("DELETE FROM security_functions WHERE name = 'MTB' AND controller = 'MonMtb' AND category = 'Details'");
    }
}
