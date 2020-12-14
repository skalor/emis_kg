<?php
use Migrations\AbstractMigration;

class CreateModuleTranslations extends AbstractMigration
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
        $table = $this->table('module_translations');

        $table->addColumn('translation', 'string');
        $table->addColumn('locale_content_id', 'integer');
        $table->addColumn('locale_id', 'integer');
        $table->addColumn('plugin', 'string');

        $table->create();
    }
}
