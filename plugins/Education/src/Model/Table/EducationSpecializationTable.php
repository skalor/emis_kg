<?php
namespace Education\Model\Table;

use ArrayObject;

use Cake\ORM\Query;
use Cake\Network\Request;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use App\Model\Table\ControllerActionTable;
use Cake\Validation\Validator;

class EducationSpecializationTable extends ControllerActionTable
{
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->addBehavior('Education.Setup');
        $this->belongsTo('FieldOfStudies', ['className' => 'Education.EducationFieldOfStudies', 'foreignKey' => 'education_field_of_studies_id']);
        $this->hasMany('EducationProgrammes', ['className' => 'Education.EducationProgrammes']);

        $this->setDeleteStrategy('restrict');
    }

    public function validationDefault(Validator $validator)
    {
        $validator = parent::validationDefault($validator);
        return $validator
            ->add('specialization_code', 'ruleUnique', [
                'rule' => 'validateUnique',
                'provider' => 'table'
            ])
            ;
    }

    public function addEditBeforeAction(Event $event) {
        $this->fields['education_field_of_studies_id']['type'] = 'select';
    }

    public function indexBeforeAction(Event $event, ArrayObject $extra)
    {
        $this->fields['education_field_of_studies_id']['sort'] = ['field' => 'EducationFieldOfStudies.name'];
    }

}
