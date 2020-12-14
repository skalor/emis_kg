<?php
namespace Security\Model\Table;

use ArrayObject;
use Cake\ORM\Query;
use Cake\ORM\Entity;
use Cake\Event\Event;
use Cake\Network\Request;
use App\Model\Table\ControllerActionTable;
use App\Model\Traits\MessagesTrait;
use Cake\ORM\TableRegistry;
use Cake\Log\Log;
use Cake\ORM\ResultSet;
use Cake\Datasource\Exception\RecordNotFoundException;

class PermissionsTable extends ControllerActionTable
{
    private $operations = ['_view', '_edit', '_add', '_delete', '_execute'];

    public function initialize(array $config)
    {
        $this->table('security_role_functions');
        parent::initialize($config);

        $this->belongsTo('SecurityRoles', ['className' => 'Security.SecurityRoles']);
        $this->belongsTo('SecurityFunctions', ['className' => 'Security.SecurityFunctions']);
        $this->toggle('add', false);
        $this->toggle('view', false);
        $this->toggle('search', false);
    }

    private function check($entity, $operation)
    {
        $flag = 0;
        if (empty($entity->{$operation})) {
            $flag = -1;
        } else if (!empty($entity->Permissions)) {
            if (!is_null($entity->Permissions[$operation])) {
                $flag = $entity->Permissions[$operation];
            }
        }
        return $flag;
    }

    public function afterAction(Event $event, ArrayObject $options)
    {
        $plugin = __($this->controller->plugin);
        $id = $this->request->pass[1];
        try {
            $name = $this->SecurityRoles->get($this->paramsDecode($id))->name;
            $this->controller->set('contentHeader', $plugin.' - '.$name);
        } catch (RecordNotFoundException $e) {
            Log::write('error', $e->getMessage());
        }
    }


    public function beforeAction(Event $event, ArrayObject $extra)
    {
        $controller = $this->controller;

        $this->field('function');
        $this->field('security_role_id', ['visible' => false]);
        $this->field('security_function_id', ['visible' => false]);

        $checkboxOptions = ['tableColumnClass' => 'permission-column'];
        $this->field('_view', $checkboxOptions);
        $this->field('_edit', $checkboxOptions);
        $this->field('_add', $checkboxOptions);
        $this->field('_delete', $checkboxOptions);
        $this->field('_execute', $checkboxOptions);

        $modules = ['Institutions', 'Directory', 'Reports', 'Administration'];
        $this->setupTabElements($modules);

        $module = $this->request->query('module');
        if (empty($module)) {
            $module = current($modules);
            $this->request->query['module'] = $module;
        }

        $controller->set('selectedAction', $module);
        $controller->set('operations', $this->operations);
    }

    // Event: ControllerAction.Model.index.beforeAction
    public function indexBeforeAction(Event $event, ArrayObject $extra)
    {
        $query = $extra['query'];
        $controller = $this->controller;

        if (count($this->request->pass) != 2) {
            $event->stopPropagation();
            return $this->controller->redirect(['action' => 'Roles']);
        }
        $roleId = $this->paramsDecode($this->request->pass[1])['id'];
        if (! $this->checkRolesHierarchy($roleId)) {
            $action = array_merge(['plugin' => 'Security', 'controller' => 'Securities', 'action' => $this->alias(), '0' => 'index']);
            $event->stopPropagation();
            return $this->controller->redirect($action);
        }
        $module = $this->request->query('module');
        $extra['pagination'] = false;
        $extra['auto_contain'] = false;

        $id = $this->request->pass[1];
        $attr = [
            'escape' => false,
            'data-placement' => 'bottom',
            'data-toggle' => 'tooltip',
            'class' => 'btn btn-xs btn-default'
        ];
        $toolbarButtons = $extra['toolbarButtons'];
        $toolbarButtons['back']['type'] = 'button';
        $toolbarButtons['back']['label'] = '<svg class="backSvg" width="27" height="27" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M22.404 11.1517H7.18845L14.1773 4.16285L12.402 2.39999L2.39999 12.402L12.402 22.404L14.1648 20.6411L7.18845 13.6522H22.404V11.1517Z" fill="#004A51"></path></svg>';
        $toolbarButtons['back']['attr'] = $attr;
        $toolbarButtons['back']['attr']['title'] = __('Back');
        $toolbarButtons['back']['url']['action'] = 'Roles';

        $toolbarButtons['edit']['url'] = $this->url('edit');
        $toolbarButtons['edit']['type'] = 'button';
        $toolbarButtons['edit']['label'] = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0)"><path d="M2.4 16.5601L0 24.0001L7.44 21.6001L2.4 16.5601Z" fill="#009966"></path><path d="M15.7952 3.12597L4.08569 14.8354L9.17678 19.9265L20.8863 8.21706L15.7952 3.12597Z" fill="#009966"></path><path d="M23.64 3.72L20.28 0.36C19.8 -0.12 19.08 -0.12 18.6 0.36L17.52 1.44L22.56 6.48L23.64 5.4C24.12 4.92 24.12 4.2 23.64 3.72Z" fill="#009966"></path></g><defs><clipPath id="clip0"><rect width="24" height="24" fill="white"></rect></clipPath></defs></svg>';
        $toolbarButtons['edit']['attr'] = $attr;
        $toolbarButtons['edit']['attr']['title'] = __('Edit');

        $query = $this->SecurityFunctions->find('permissions', ['roleId' => $roleId, 'module' => $module]);
        return $query;
    }

    public function indexAfterAction(Event $event, Query $query, ResultSet $data, ArrayObject $extra)
    {
        $list = [];
        $icons = [
            -1 => '<i class="fa fa-minus grey"></i>',
            0 => '<i class="fa kd-cross red"></i>',
            1 => '<i class="fa kd-check green"></i>'
        ];

        foreach ($data as $obj) {
            if (!array_key_exists($obj->category, $list)) {
                $list[$obj->category] = [];
            }
            foreach ($this->operations as $op) {
                $flag = $this->check($obj, $op);
                $obj->Permissions[$op] = $icons[$flag];
            }

            $obj->name = __($obj->name);

            // if the permission have description, it will display the description tooltip next to the permission name.
            if (!empty($obj['description'])) {
                $message = $obj['description'];
                $obj->name = $obj->name . $this->tooltipMessage($message);
            }

            $list[$obj->category][] = $obj;
        }

        return $list;
    }

    public function checkRolesHierarchy($roleId)
    {
        $user = $this->Auth->user();
        $userId = $user['id'];
        if ($user['super_admin'] == 1) { // super admin will show all roles
            $userId = null;
        }
        $GroupRoles = TableRegistry::get('Security.SecurityGroupUsers');
        $userRole = $GroupRoles
            ->find()
            ->contain('SecurityRoles')
            ->order(['SecurityRoles.order'])
            ->where([
                $GroupRoles->aliasField('security_user_id') => $userId
            ])
            ->first();

        $SecurityRolesTable = $this->SecurityRoles;

        $roleEntity = $SecurityRolesTable->get($roleId);

        $roleOrder = $roleEntity->order;

        //this is to check if user have role higher that the one user try to edit.  e.g. teacher(4) and principal(2)
        //also for super admin where redirect not necessary
        //OR user is creator of the user role.
        return (($roleOrder > $userRole['security_role']['order']) ||  ($roleEntity->created_user_id == $userId));
    }

    private function setupTabElements($modules)
    {
        $controller = $this->controller;
        $tabElements = [];
        $url = ['plugin' => $controller->plugin, 'controller' => $controller->name, 'action' => $this->alias()];
        if (!empty($this->request->pass)) {
            $url = array_merge($url, $this->request->pass);
        }
        if (!empty($this->request->query)) {
            $url = array_merge($url, $this->request->query);
        }

        foreach ($modules as $module) {
            $tabElements[$module] = [
                'url' => array_merge($url, ['module' => $module]),
                'text' => __($module)
            ];
        }
        $tabElements = $controller->TabPermission->checkTabPermission($tabElements);
        $controller->set('tabElements', $tabElements);
    }

    private function tooltipMessage($message)
    {
        $tooltipMessage = '&nbsp&nbsp;<i class="fa fa-info-circle fa-lg table-tooltip icon-blue" data-placement="right" data-toggle="tooltip" data-animation="false" data-container="body" title="" data-html="true" data-original-title="' . $message . '"></i>';

        return $tooltipMessage;
    }
}
