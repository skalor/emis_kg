<?php

use Phinx\Migration\AbstractMigration;

class StatisticReportHistoryMod extends AbstractMigration
{
    // migrate
    public function up()
    {
        if ($this->hasTable('statistic_report_history')) {
            $table = $this->table('statistic_report_history');
            if (!$table->hasColumn('is_signed')) {
                $table->addColumn('is_signed', 'boolean', [
                    'default' => null,
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

            $table->save();
        }
    }

    // rollback
    public function down()
    {
        if ($this->hasTable('statistic_report_history')) {
            $table = $this->table('statistic_report_history');
            if ($table->hasColumn('is_signed')) {
                $table->removeColumn('is_signed');
            }
            if ($table->hasColumn('signed_by_id')) {
                $table->removeColumn('signed_by_id');
            }

            $table->save();
        }
    }
}
