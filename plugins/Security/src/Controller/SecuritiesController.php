<?php

namespace Security\Controller;

use App\Controller\Component\AccessControlComponent;
use ArrayObject;
use App\Controller\AppController;
use Cake\Event\Event;
use Cake\Network\Exception\NotFoundException;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use HiddenField\Model\Repository\InstitutionTypeRepository;
use HiddenField\Services\HiddenFieldService;
use InvalidArgumentException;

/**
 * @property AccessControlComponent AccessControl
 */
class SecuritiesController extends AppController
{
    public function initialize()
    {
        parent::initialize();
        
        $this->ControllerAction->models = [
            'Accounts' => ['className' => 'Security.Accounts', 'actions' => ['view', 'edit']],
            'Users' => ['className' => 'Security.Users'],
            'SystemGroups' => ['className' => 'Security.SystemGroups', 'actions' => ['!add', '!edit', '!remove']]
        ];
        $this->attachAngularModules();
        
        $this->loadComponent('RequestHandler');
        $this->loadComponent('AccessControl');
    }
    
    // CAv4
    public function Roles()
    {
        $this->ControllerAction->process(['alias' => __FUNCTION__, 'className' => 'Security.SecurityRoles']);
    }
    
    // end
    
    public function Permissions($subaction = 'index', $roleId = null)
    {
        if($subaction == 'edit') {
            $indexUrl = [
                'plugin' => 'Security',
                'controller' => 'Securities',
                'action' => 'Permissions'
            ];
            $viewUrl = [
                'plugin' => 'Security',
                'controller' => 'Securities',
                'action' => 'Permissions',
                'index',
                $roleId
            ];
            
            $alertUrl = [
                'plugin' => 'Configuration',
                'controller' => 'Configurations',
                'action' => 'setAlert'
            ];
            $moduleKey = is_null($this->request->query('module')) ? '' : $this->request->query('module');
            $this->set('roleId', $this->ControllerAction->paramsDecode($roleId)['id']);
            $this->set('indexUrl', $indexUrl);
            $this->set('viewUrl', $viewUrl);
            $this->set('alertUrl', $alertUrl);
            $this->set('moduleKey', $moduleKey);
            $header = __('Security') . ' - ' . TableRegistry::get('Security.SecurityRoles')->get($this->ControllerAction->paramsDecode($roleId))->name;
            $this->set('contentHeader', __($header));
            $this->set('institutionTypes', (new InstitutionTypeRepository())->getTypes());
            $this->render('Permissions/permission_edit');
        } else {
            $this->ControllerAction->process(['alias' => __FUNCTION__, 'className' => 'Security.Permissions']);
        }
    }
    
    public function UserGroups()
    {
        $this->ControllerAction->process(['alias' => __FUNCTION__, 'className' => 'Security.UserGroups']);
    }
    
    public function RefreshToken()
    {
        $this->ControllerAction->process(['alias' => __FUNCTION__, 'className' => 'Security.RefreshTokens']);
    }
    
    private function attachAngularModules()
    {
        $action = $this->request->action;
        
        switch($action) {
            case 'Permissions':
                if(isset($this->request->pass[0])) {
                    if($this->request->param('pass')[0] == 'edit') {
                        $this->Angular->addModules([
                            'alert.svc',
                            'security.permission.edit.ctrl',
                            'security.permission.edit.svc'
                        ]);
                    }
                }
                break;
        }
    }
    
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        $header = 'Security';
        $this->Navigation->addCrumb($header, ['plugin' => 'Security', 'controller' => 'Securities', 'action' => 'index']);
        $this->Navigation->addCrumb($this->request->action);
        
        $this->set('contentHeader', __($header));
    }
    
    public function onInitialize(Event $event, Table $model, ArrayObject $extra)
    {
        $header = __('Security');
        $header .= ' - ' . __($model->getHeader($model->alias));
        $this->set('contentHeader', $header);
    }
    
    public function index()
    {
        return $this->redirect(['action' => 'Users']);
    }
    
    public function getUserTabElements($options = [])
    {
        $plugin = $this->plugin;
        $name = $this->name;
        
        $id = (array_key_exists('id', $options)) ? $options['id'] : $this->request->session()->read($name . '.id');
        
        $tabElements = [
            $this->name => [
                'url' => ['plugin' => $plugin, 'controller' => $name, 'action' => 'Users', 'view', $this->ControllerAction->paramsEncode(['id' => $id])],
                'text' => __('Details')
            ],
            'Accounts' => [
                'url' => ['plugin' => $plugin, 'controller' => $name, 'action' => 'Accounts', 'view', $this->ControllerAction->paramsEncode(['id' => $id])],
                'text' => __('Account')
            ]
        ];
        
        return $this->TabPermission->checkTabPermission($tabElements);
    }
    
    public function UnhiddableFunctions()
    {
        $sc = TableRegistry::get('Security.SecurityFunctions');
        $hf = new HiddenFieldService();
        
        $all = $sc->find()->where([
            'module' => 'Institutions'
        ])->all()->toArray();
        
        $notHiddables = $hf->getNotFillables($all);
    
        $this->RequestHandler->renderAs($this, 'json');
        $this->set(compact('notHiddables'));
        $this->set('_serialize', ['notHiddables']);
    }
    
    public function ModuleFields()
    {
        $this->RequestHandler->renderAs($this, 'json');
        
        $data = $this->request->data;
        $id = $this->request->query('model');
        
        if(empty($data['institutionType']) || empty($data['role']) || empty($id)) {
            throw new \InvalidArgumentException();
        }
        
        $hiddenFields = new HiddenFieldService();
        $function = $hiddenFields->getFunction($id);
        $data['controller'] = $function->controller;
        $tableName = $hiddenFields->getModel($id);
        $fields = $hiddenFields->getFields($tableName, $data);
        
        $this->set(compact('fields', 'tableName'));
        $this->set('_serialize', ['fields']);
    }
    
    public function saveModuleFields()
    {
        if(!$this->RequestHandler->requestedWith('json')) {
            throw new NotFoundException();
        }
        
        $this->RequestHandler->renderAs($this, 'json');
        
        $data = $this->request->data;
        
        if(empty($data)) {
            throw new InvalidArgumentException();
        }
    
    
        $id = $this->request->query('model');
    
        if(empty($data['fields']) || empty($data['role']) || empty($data['institutionType']) || empty($id)) {
            throw new InvalidArgumentException();
        }
    
    
        $hiddenFields = new HiddenFieldService();
        $model = $hiddenFields->getModel($id);
        
        if(empty($model)) {
            throw new InvalidArgumentException();
        }
        
        $fields = $data['fields'];
        $role = $data['role'];
        $institutionType = $data['institutionType'];
        
        $controller = $hiddenFields->getFunction($id)->controller;
        $hiddenFields->hide($institutionType, $controller, $model, $role, $fields);
        
        $this->set('success', ['message' => 'Everything is ok!']);
        $this->set('_serialize', ['success']);
    }
}
