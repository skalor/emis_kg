<?php
namespace Institution\Model\Entity;

use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;

class InstitutionEntrance extends Entity
{
    protected $_virtual = ['name'];

    protected function _getName()
    {
        $reasonEntrance = TableRegistry::get('institution_reason_for_entrance');
        return __($reasonEntrance->find()->where(['id' => $this->institution_reason_for_entrance_id])->first()->name). ' - ' . $this->date;
    }

}