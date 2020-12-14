<?php
namespace Health\Model\Table;

use App\Model\Table\ControllerActionTable;

class DisabilityGroupsTable extends ControllerActionTable
{
    public function initialize(array $config)
    {
        $this->table('health_disability_groups');
        parent::initialize($config);

        $this->hasMany('PeopleDisabilities', ['className' => 'Health.PeopleDisabilities', 'foreignKey' => 'health_disability_group_id']);

        $this->addBehavior('FieldOption.FieldOption');
    }
}
