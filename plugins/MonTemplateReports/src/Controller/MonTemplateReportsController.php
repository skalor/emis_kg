<?php
namespace MonTemplateReports\Controller;

use App\Controller\PageController;
use Cake\Event\Event;

class MonTemplateReportsController extends PageController
{
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        $action = $this->request->action;
        $page = $this->Page;
        $page->addCrumb('MonTemplateReports', ['plugin' => 'MonTemplateReports', 'controller' => 'MonTemplateReports', 'action' => 'index']);
        if ($action) {
            $page->addCrumb($action);
        }

        $page->setHeader(__('MonTemplateReports'));
        $page->get('type_id')->setControlType('select')->setSortable(true);
        $action === 'index' ? $page->exclude(['content']) : null;
        if (in_array($action, ['add', 'edit'])) {
            $this->viewBuilder()->template('Page/addEdit');
        }
    }
}
