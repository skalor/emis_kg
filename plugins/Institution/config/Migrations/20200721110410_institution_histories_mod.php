<?php

use Phinx\Migration\AbstractMigration;

class InstitutionHistoriesMod extends AbstractMigration
{
    public $tables = [
        'institution_activities',
        'user_activities'
    ];

    // migrate
    public function up()
    {
        foreach($this->tables as $table) {
            if ($this->hasTable($table)) {
                $table = $this->table($table);

                if (!$table->hasColumn('institution_id')) {
                    $table->addColumn('institution_id', 'integer', [
                        'default' => null,
                        'limit' => 11,
                        'null' => true
                    ])->addIndex('institution_id');
                }
                if (!$table->hasColumn('name')) {
                    $table->addColumn('name', 'string', [
                        'default' => null,
                        'limit' => 255,
                        'null' => true
                    ]);
                }
                if (!$table->hasColumn('is_signed')) {
                    $table->addColumn('is_signed', 'boolean', [
                        'default' => null,
                        'null' => true
                    ]);
                }
                if (!$table->hasColumn('signed_link')) {
                    $table->addColumn('signed_link', 'string', [
                        'default' => null,
                        'limit' => 255,
                        'null' => true
                    ]);
                }
                if (!$table->hasColumn('signed_by_id')) {
                    $table->addColumn('signed_by_id', 'integer', [
                        'default' => null,
                        'limit' => 11,
                        'null' => true
                    ])->addIndex('signed_by_id');
                }
                if (!$table->hasColumn('signed_document_id')) {
                    $table->addColumn('signed_document_id', 'integer', [
                        'default' => null,
                        'limit' => 11,
                        'null' => true
                    ])->addIndex('signed_document_id');
                }

                $table->save();
            }
        }
    }

    // rollback
    public function down()
    {
        foreach($this->tables as $table) {
            if ($this->hasTable($table)) {
                $table = $this->table($table);

                if ($table->hasColumn('name')) {
                    $table->removeColumn('name');
                }
                if ($table->hasColumn('is_signed')) {
                    $table->removeColumn('is_signed');
                }
                if ($table->hasColumn('signed_link')) {
                    $table->removeColumn('signed_link');
                }
                if ($table->hasColumn('signed_by_id')) {
                    $table->removeColumn('signed_by_id');
                }
                if ($table->hasColumn('signed_document_id')) {
                    $table->removeColumn('signed_document_id');
                }

                $table->save();
            }
        }
    }
}
