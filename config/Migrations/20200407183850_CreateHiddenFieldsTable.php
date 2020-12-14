<?php
use Migrations\AbstractMigration;

class CreateHiddenFieldsTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     * @return void
     */
    public function change()
    {
        $this->table('hidden_fields')
            ->addColumn('model', 'string')
            ->addColumn('field', 'string')
            ->addColumn('security_role_id', 'integer')
            ->addIndex('security_role_id')
            ->addColumn('action', 'string')
            ->addColumn('institution_type_id', 'integer')
            ->addIndex('institution_type_id')
            ->addColumn('controller', 'string')
            ->create();
    }
}
