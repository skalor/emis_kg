<?php
namespace ApiLogs\Controller;

use Cake\Event\Event;
use App\Controller\PageController;


class ApiLogsController extends PageController
{

    public function initialize()
    {
        parent::initialize();
        $this->Page = $this->loadComponent('Institution.InstitutionPage');
        $this->Page->disable(['add', 'edit', 'delete']);
        $this->Page->loadElementsFromTable($this->ApiLogs);
    }

    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        $page = $this->Page;

        // set Breadcrumb
        $page->addCrumb('Api Logs', ['plugin' => 'ApiLogs', 'controller' => 'ApiLogs', 'log']);

        $header = $page->getHeader();
        $page->setHeader($header);
    }

    public function index()
    {
        $page = $this->Page;

        // Controllers
        $controllers = [
            'MonGeneratedStatisticReports' => 'MonGeneratedStatisticReports',
            'InstitutionHistories' => 'InstitutionHistories',
            'MtsrTubdukApi' => 'MtsrTubdukApi'
        ];
        $controllerOptions = [null => __('-- Select --')] + $controllers;
        $page->addFilter('controller')
            ->setOptions($controllerOptions);
        // end controllers

        // to filter by controllers
        if (!is_null($page->getQueryString('controller'))) {
            $page->setQueryString('controller', $page->getQueryString('controller'));
        }
        // end controllers


        $page->addNew('created_user')->setDisplayFrom('created_user.name');
        $page->move('created_user')->after('status');

        $page->addNew('created_on')->setDisplayFrom('created');
        $page->move('created_on')->after('created_user');

        $page->exclude(['callback', 'callback_param']);

        parent::index();
    }

    public function view($id)
    {
        $page = $this->Page;
        parent::view($id);
    }

}
