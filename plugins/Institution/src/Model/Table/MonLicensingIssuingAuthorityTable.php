<?php
namespace Institution\Model\Table;

use App\Model\Table\ControllerActionTable;

class MonLicensingIssuingAuthorityTable extends ControllerActionTable
{
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->hasMany('MonLicensing', ['className' => 'Institution.MonLicensing', 'foreignKey' => 'issuing_authority_id']);
        $this->addBehavior('FieldOption.FieldOption');
    }

}
