<?php

namespace App\Auth;

use App\Controller\Component\AccessControlComponent;
use Cake\Controller\Component\AuthComponent;
use Cake\ORM\TableRegistry;
use Security\Model\Table\SecurityGroupUsersTable;

class AuthService
{
    public function getRolesByUserId($id)
    {
        /** @var SecurityGroupUsersTable $groups */
        $groups = TableRegistry::get('Security.SecurityGroupUsers');
        return $groups->getRolesByUser($id);
    }
}