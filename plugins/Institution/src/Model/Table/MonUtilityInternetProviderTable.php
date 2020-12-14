<?php
namespace Institution\Model\Table;

use App\Model\Table\ControllerActionTable;

class MonUtilityInternetProviderTable extends ControllerActionTable
{
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->hasMany('InfrastructureUtilityInternets', ['className' => 'Institution.InfrastructureUtilityInternets', 'foreignKey' => 'internet_provider_id']);
        $this->addBehavior('FieldOption.FieldOption');
    }

}
