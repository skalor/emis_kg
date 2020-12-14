<?php

namespace Institution\Controller;

use ArrayObject;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use App\Controller\PageController;

class MonMtbController extends PageController
{

    public function initialize()
    {
        parent::initialize();

        $this->loadModel('AcademicPeriod.AcademicPeriods');
        // to disable actions if institution is not active
        $this->loadComponent('Institution.InstitutionInactive');
    }

    public function beforeFilter(Event $event)
    {
        $session = $this->request->session();
        $institutionId = $session->read('Institution.Institutions.id');
        $institutionName = $session->read('Institution.Institutions.name');

        parent::beforeFilter($event);

        $encodedInstitutionId = $this->paramsEncode(['id' => $institutionId]);
        $page = $this->Page;

        $page->addCrumb('Institutions', ['plugin' => 'Institution', 'controller' => 'Institutions', 'action' => 'Institutions', 'index']);
        $page->addCrumb($institutionName, ['plugin' => 'Institution', 'controller' => 'Institutions', 'action' => 'dashboard', 'institutionId' => $encodedInstitutionId, $encodedInstitutionId]);
        $page->addCrumb('MTB');

        $page->setHeader($institutionName . ' - ' . __('MTB'));
        $page->get('institution_id')->setControlType('hidden')->setValue($institutionId);
        $page->setQueryString('institution_id', $institutionId);
        
        $periods = TableRegistry::get('AcademicPeriod.AcademicPeriods')->getYearList();
        $page->get('academic_period_id')->setControlType('select')->setOptions($periods);
        $page->move('academic_period_id')->after('id');
        $page->move('number_of_projectors')->after('scanner');
        $page->move('number_of_interactive_whiteboards_units')->after('number_of_projectors');

    }

    public function index()
    {
        parent::index();
        $this->Page->exclude(['institution_id']);
    }
    public function add()
    {
        parent::add();
        $page = $this->Page;

        if ($this->request->is(['get'])) {
            $academicPeriodId = !is_null($page->getQueryString('academic_period_id')) ? $page->getQueryString('academic_period_id') : $this->AcademicPeriods->getCurrent();
            $page->get('academic_period_id')->setValue($academicPeriodId);
        }
    }
}
