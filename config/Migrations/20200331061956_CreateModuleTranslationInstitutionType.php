<?php
use Migrations\AbstractMigration;

class CreateModuleTranslationInstitutionType extends AbstractMigration
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
        $table = $this->table('module_translation_institution_type');
        $table->addColumn('institution_type_id', 'integer');
        $table->addColumn('module_translation_id', 'integer');
        $table->create();
    }
}
