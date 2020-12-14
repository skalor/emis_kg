<?php
namespace Institution\Model\Table;
use ArrayObject;

use App\Model\Table\ControllerActionTable;
use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\Validation\Validator;

class InstitutionFoundersTable extends ControllerActionTable
{
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->belongsTo('Institutions', ['className' => 'Institution.Institutions', 'foreignKey' => 'institution_id']);
        $this->belongsTo('TypesOfFounders', ['className' => 'FieldOption.TypesOfFounders', 'foreignKey' => 'types_of_founder_id']);
    }

    public function addEditAfterAction(Event $event, Entity $entity){
        $this->field('types_of_founder_id', ['type' => 'select']);
    }

    public function afterAction(Event $event, ArrayObject $extra){
        $this->setFieldOrder([
            'types_of_founder_id', 'contact_person'
        ]);
    }
}
