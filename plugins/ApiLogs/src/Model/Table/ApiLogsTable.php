<?php
namespace ApiLogs\Model\Table;

use ArrayObject;
use App\Model\Table\ControllerActionTable;
use Cake\Event\Event;
use Cake\ORM\Entity;

class ApiLogsTable extends ControllerActionTable
{
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->belongsTo('Users', ['className' => 'User.Users', 'foreignKey' => 'created_user_id']);
    }

    public function beforeSave(Event $event, Entity $entity, ArrayObject $options)
    {
        if ($entity->isNew()) {
            $params=json_decode($entity->params, true);
            if (array_key_exists('byPin',$params)){
                unset($params['byPin']);
                $entity->params=json_encode($params);
            }
        }
    }

}