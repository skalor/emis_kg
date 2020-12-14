<?php

namespace App\Listeners;

use App\Auth\AuthService;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use HiddenField\Model\Repository\Tables;
use HiddenField\Model\Table\HiddenFieldsTable;
use Page\Controller\PageController;

class HiddenFieldsListener implements EventListenerInterface
{
    public function implementedEvents()
    {
        return [
            'ControllerAction.Model.beforeRender' => 'controller',
            'Controller.beforeRender' => 'pageController',
        ];
    }
    
    public function controller(Event $event)
    {
        $data = $event->data();
    
        if(!isset($data[0]) || !$data[0] instanceof Table) {
            return;
        }
    
        $model = $data[0];
        $fields = $this->getFields($model->registryAlias());
    
        if(!empty($fields)) {
            $this->hide($model, $fields);
        }
    }
    
    public function pageController(Event $event)
    {
        /** @var PageController $controller */
        $controller = $event->subject();
        if(!$controller instanceof PageController || $controller->plugin === null) {
            return;
        }
        
        if($controller instanceof PageController) {
            $fields = $this->getFields($controller->modelClass);
    
            $this->hidePageController($controller, $fields);
        }
    
    }
    
    private function getFields(string $model): array
    {
        $request = Router::getRequest();
    
        $tables = new Tables();
        $id = $request->session()->read('Institution.Institutions.id');
    
        if(!$id || !$tables->hasModel($model)) {
            return [];
        }
        
        /** @var HiddenFieldsTable $hiddenFields */
        $hiddenFields = TableRegistry::get('HiddenField.HiddenFields');
    
        $auth = new AuthService();
        $roles = $auth->getRolesByUserId($request->session()->read('Auth.User.id'));
    
        $institutions = TableRegistry::get('Institution.Institutions');
        $institution = $institutions->get($id);
    
        $fields = $hiddenFields->findWith($request->controller, $institution->institution_type_id, $model, $roles);
        
        if(empty($fields)) {
            return [];
        }
        
        return $fields;
    }
    
    private function hidePageController(PageController $controller, array $fields)
    {
        $action = $controller->request->action;
        
        foreach($fields as $field) {
            if($field->action === 'index') {
                unset($controller->viewVars['elements'][$field->field]);
            }
            
            if($field->action === 'view' && $action === 'edit') {
                $controller->viewVars['elements'][$field->field]['attributes']['disabled'] = 'disabled';
            }
        }
    }
    
    public function hide($toHide, array $fields)
    {
        foreach($fields as $field) {
            if($field->action === 'index') {
                $toHide->fields[$field->field]['visible'] = false;
            }
            
            if($field->action === 'view' && $toHide->action === 'edit') {
                $toHide->fields[$field->field]['visible'] = true;
                $toHide->fields[$field->field]['type'] = 'disabled';
            }
        }
    }
}