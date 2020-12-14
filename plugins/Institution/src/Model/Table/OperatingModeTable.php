<?php
namespace Institution\Model\Table;

use App\Model\Table\ControllerActionTable;

class OperatingModeTable extends ControllerActionTable
{
    public function initialize(array $config)
    {
        $this->table('institution_operating_mode');
        parent::initialize($config);

        $this->hasMany('Institutions', ['className' => 'Institution.Institutions', 'foreignKey' => 'institution_working_hours_id']);

        $this->addBehavior('FieldOption.FieldOption');
    }
}
