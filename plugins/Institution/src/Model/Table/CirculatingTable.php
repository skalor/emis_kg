<?php
namespace Institution\Model\Table;

use App\Model\Table\ControllerActionTable;
use Cake\Validation\Validator;

class CirculatingTable extends ControllerActionTable
{
    public function initialize(array $config)
    {
        parent::initialize($config);


        $this->hasMany('Correspondence', ['className' => 'Institution.Correspondence', 'foreignKey' => 'circulating_id']);


        $this->addBehavior('FieldOption.FieldOption');


        $this->addBehavior('Restful.RestfulAccessControl', [
            'InstitutionCorrespondence' => ['index', 'add']
        ]);
    }


}