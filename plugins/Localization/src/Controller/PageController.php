<?php

namespace Localization\Controller;

use App\Controller\PageController as BaseController;
use Cake\Event\Event;
use Page\Model\Entity\PageStatus;

class PageController extends BaseController
{
    /** @var array */
    protected $hiddenFields = [];
    
    /** @var SelectableCollection */
    protected $selectable;

    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event); // TODO: Change the autogenerated stub

        $this->selectable = new SelectableCollection();
    }

    public function beforeRender(Event $event)
    {
        parent::beforeRender($event);

        $this->isEditable(function($self) {
            $this->selectable->apply($self->Page);
        });
    
        foreach($this->hiddenFields as $hidden) {
            $this->Page->get($hidden)->setControlType('hidden');
        }
    }

    protected function isEditable(callable $function): void
    {
        if(in_array($this->request->action, ['add', 'edit'])) {
            $function($this);
        }
    }
    
    protected function redirectWith(string $message, ?string $type = null): void
    {
        if($this->request->is('get')) {
            return;
        }
        
        $page = $this->Page;
    
    
        /** @var PageStatus $status */
        $status = $page->getStatus();
        
        if($type === 'error') {
            $status->setCode(PageStatus::VALIDATION_ERROR);
        }
        
        $status->setMessage($message);
        $page->setAlert($status->getMessage(), $type);
        
        if($type !== 'error') {
            $page->redirect(['action' => 'index']);
        }
    }
}