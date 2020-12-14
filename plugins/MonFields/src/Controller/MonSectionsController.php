<?php
namespace MonFields\Controller;

use App\Controller\PageController;
use Cake\Event\Event;

class MonSectionsController extends PageController
{
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('MonFields.MonFields');
    }

    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        $action = $this->request->action;
        $page = $this->Page;
        
        $page->addCrumb('MonSections', ['plugin' => 'MonFields', 'controller' => 'MonSections', 'action' => 'index']);
        if ($action) {
            $page->addCrumb($action);
        }
        
        $page->setHeader(__('MonSections'));
    }

    public function beforeRender(Event $event)
    {
        parent::beforeRender($event);

        $session = $this->request->session();

        if ($this->request->data('submit') === 'reload' && $session->check('alert')) {
            $session->delete('alert');
        }
    }
}