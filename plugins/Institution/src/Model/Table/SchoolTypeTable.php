<?php
namespace Institution\Model\Table;

use App\Model\Table\ControllerActionTable;

class SchoolTypeTable extends ControllerActionTable
{
    public function initialize(array $config)
    {
        $this->table('institution_school_type');
        parent::initialize($config);

        $this->hasMany('Institutions', ['className' => 'Institution.Institutions', 'foreignKey' => 'SchoolType']);

        $this->addBehavior('FieldOption.FieldOption');
    }
}
