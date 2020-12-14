<?php
namespace Education\Model\Table;

use Cake\Event\Event;
use Cake\Validation\Validator;
use App\Model\Table\ControllerActionTable;
use ArrayObject;

class EducationStagesTable extends ControllerActionTable
{
	public function initialize(array $config)
    {
		parent::initialize($config);
		$this->addBehavior('Education.Setup');
		$this->hasMany('EducationGrades', ['className' => 'Institution.EducationGrades', 'dependent' => true, 'cascadeCallbacks' => true]);

        $this->setDeleteStrategy('restrict');
	}

    public function validationDefault(Validator $validator)
    {
        $validator = parent::validationDefault($validator);
        $validator
            ->add('code', 'ruleUnique', [
                'rule' => 'validateUnique',
                'provider' => 'table'
            ]);
        return $validator;
    }
    public function beforeAction(Event $event, ArrayObject $extra){
       $this->field('code',['type' => ($this->action == 'edit') ? 'readonly' : 'string', 'after' => 'name']);
    }
}