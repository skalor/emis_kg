<?php

namespace Institution\Model\Table;

use ArrayObject;
use Cake\ORM\Query;
use App\Model\Table\ControllerActionTable;
use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;
use Cake\I18n\Time;
use Cake\I18n\Date;

class PartnersTable extends ControllerActionTable{
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->belongsTo('Institutions', ['className' => 'Institution.Institutions', 'foreignKey' => 'institution_id']);
        $this->setDeleteStrategy('restrict');
    }
}