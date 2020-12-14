<?php
namespace Education\Model\Table;

use ArrayObject;

use Cake\ORM\Query;
use Cake\Network\Request;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use App\Model\Table\ControllerActionTable;

class EducationFormOfTrainingTable extends ControllerActionTable
{
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->addBehavior('Education.Setup');
        $this->hasMany('EducationProgrammes', ['className' => 'Education.EducationProgrammes']);
        $this->setDeleteStrategy('restrict');
    }

    function getListWithCode(){
        $options = $this
            ->find('list', ['keyField' => 'id', 'valueField' => 'code'])
            ->find('visible')
            ->toArray();

        return $options;
    }
}
