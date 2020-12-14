<?php
namespace ApiLogs\Model\Table;

use App\Model\Table\ControllerActionTable;

class ApiCreatedUsersTable extends ControllerActionTable
{
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->belongsTo('Users', ['className' => 'Security.Users', 'foreignKey' => 'core_user_id']);
    }
}