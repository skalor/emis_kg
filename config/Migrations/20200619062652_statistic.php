<?php

use Phinx\Migration\AbstractMigration;

class Statistic extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $this->table('employees_report')
            ->addColumn('region',               'string')
            ->addColumn('name',                 'string')
            ->addColumn('org_struct',           'string')
            ->addColumn('area_level_id',        'integer')
            ->addColumn('code',                 'string')
            ->addColumn('staff_male_count',     'integer')
            ->addColumn('staff_female_count',   'integer')
            ->addColumn('staff_total_count',    'integer')
            ->addColumn('student_male_count',   'integer')
            ->addColumn('student_female_count', 'integer')
            ->addColumn('student_total_count',  'integer')
            ->addColumn('all_total_count',      'integer')
            ->addColumn('create_at',            'datetime')
            ->create();
    }
}
