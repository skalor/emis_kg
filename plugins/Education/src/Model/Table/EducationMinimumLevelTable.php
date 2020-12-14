<?php
namespace Education\Model\Table;

use ArrayObject;

use Cake\ORM\Query;
use Cake\Network\Request;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use App\Model\Table\ControllerActionTable;
use Cake\Validation\Validator;

class EducationMinimumLevelTable extends ControllerActionTable
{
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->addBehavior('Education.Setup');
        $this->hasMany('EducationProgrammes', ['className' => 'Education.EducationProgrammes']);

        $this->setDeleteStrategy('restrict');
    }

    public function validationDefault(Validator $validator)
    {
        $validator = parent::validationDefault($validator);
        return $validator
            ->add('code', 'ruleUnique', [
                'rule' => 'validateUnique',
                'provider' => 'table'
            ])
            ;
    }

    public function getMinimumLevelOptions(){
        $list = $this
            ->find('list', ['keyField' => 'id', 'valueField' => 'name'])
            ->find('visible')
            ->order([
                $this->aliasField('order')
            ])
            ->toArray();

        return $list;
    }
}
