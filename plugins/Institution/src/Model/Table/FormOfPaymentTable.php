<?php
namespace Institution\Model\Table;

use App\Model\Table\ControllerActionTable;
use Cake\Validation\Validator;

class FormOfPaymentTable extends ControllerActionTable
{
    public function initialize(array $config)
    {
        parent::initialize($config);


        $this->hasMany('StudentsUser', ['className' => 'Institution.StudentUser', 'foreignKey' => 'form_of_payment_id']);


        $this->addBehavior('FieldOption.FieldOption');


        $this->addBehavior('Restful.RestfulAccessControl', [
            'Students' => ['index', 'add']
        ]);
    }


}