<?php
namespace FieldOption\Model\Table;

use ArrayObject;
use App\Model\Table\ControllerActionTable;
use Cake\Event\Event;
use Cake\Validation\Validator;

class DooAppointmentTable extends ControllerActionTable {
	public function initialize(array $config)
	{
		parent::initialize($config);
        $this->hasMany('Institutions', ['className' => 'Institution.Institutions', 'foreignKey' => 'institution_doo_purpose']);
        $this->addBehavior('FieldOption.FieldOption');
	}

	public function indexBeforeAction(Event $event, ArrayObject $extra)
	{
		$this->field('code', ['after' => 'name']);
		$this->field('editable', ['visible' => 'false']);
	}

	public function validationUpdate($validator)
	{
        $validator
            ->add('name', [
                    'ruleUnique' => [
                        'rule' => 'validateUnique',
                        'provider' => 'table',
                        'message' => __('This field has to be unique')
                    ]
                ])
            ->add('code', [
                    'ruleUnique' => [
                        'rule' => 'validateUnique',
                        'provider' => 'table',
                        'message' => __('This field has to be unique')
                    ]
                ]);

        return $validator;
    }
}