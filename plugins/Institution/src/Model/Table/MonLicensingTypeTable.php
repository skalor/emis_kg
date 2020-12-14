<?php
namespace Institution\Model\Table;

use App\Model\Table\ControllerActionTable;

class MonLicensingTypeTable extends ControllerActionTable
{
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->hasMany('MonLicensing', ['className' => 'Institution.MonLicensing', 'foreignKey' => 'type_id']);
        $this->addBehavior('FieldOption.FieldOption');
    }

}
