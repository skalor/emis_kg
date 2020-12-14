<?php
namespace Institution\Controller;

use App\Model\Table\AppTable;
use Cake\Core\Configure;
use Cake\Event\Event;
use App\Controller\PageController;
use Cake\ORM\TableRegistry;

class InstitutionHistoriesController extends PageController
{
    public $helpers = ['Institution.Signing'];
    public $models = [
        'Institution.InstitutionHistories' => 'Institutions',
        'Institution.StudentHistories' => 'StudentUser',
        'Institution.StaffHistories' => 'StaffUser'
    ];

	public function initialize()
    {
        parent::initialize();

        $page = $this->Page = $this->loadComponent('Institution.InstitutionPage');
        $this->loadModel('Institution.Institutions');

        Configure::write('debug', false);
        $page->addFilter('history_type')->setOptions([__('Model') => $this->models]);
        $result = $this->getBetweenDateList(date('Y-m-d', strtotime('-5 years')), true);
        $page->addFilter('created_from')->setOptions([__('From') => $result['from']]);
        $page->addFilter('created_to')->setOptions([__('To') => $result['to']]);

        $queryString = $page->getQueryString();
        $historyType = isset($queryString['history_type']) ? $queryString['history_type'] : $this->modelClass;
        $createdFrom = isset($queryString['created_from']) ? $queryString['created_from'] : '2015-01-01';
        $createdTo = isset($queryString['created_to']) ? $queryString['created_to'] : date('Y-m-d');

        $page->setQueryString('model', $this->models[$historyType], true);
        $page->setQueryString('createdFrom', $createdFrom, true);
        $page->setQueryString('createdTo', $createdTo, true);
        $this->set('signHistoryType', $historyType);

        $table = TableRegistry::get($historyType);
        if ($table instanceof AppTable) {
            $page->loadElementsFromTable($table);
        }
        $this->loadComponent('Institution.Sign');
        if ($this->Auth->user('id')) {
            $this->Auth->allow('sign');
            $this->Security->config('unlockedActions', ['sign']);
        }

        $page->disable(['add', 'edit', 'view', 'delete']);
    }

    public function beforeFilter(Event $event)
    {
        $institutionId = $this->paramsDecode($this->request->params['pass'][1])['id'];
        $institutionName = $this->Institutions->get($institutionId)->name;

        parent::beforeFilter($event);

        $page = $this->Page;

        $encodedInstitutionId = $this->paramsEncode(['id' => $institutionId]);

        // set breadcrumb
        $page->addCrumb('Institutions', ['plugin' => 'Institution', 'controller' => 'Institutions', 'action' => 'Institutions']);
        $page->addCrumb($institutionName, ['plugin' => 'Institution', 'controller' => 'Institutions', 'action' => 'dashboard', 'institutionId' => $encodedInstitutionId, $encodedInstitutionId]);
        $page->addCrumb(__('History'));

        // set header
        $header = $page->getHeader();
        $page->setHeader($institutionName . ' - ' . __('History'));

        // set queryString
        $page->setQueryString('institution_id', $institutionId);

        // set field
        $page->exclude(['model_reference', 'field_type', 'institution_id']);

        // set field order
        $page->move('model')->first();
        $page->move('field')->after('model');
        $page->move('old_value')->after('field');
        $page->move('new_value')->after('old_value');
    }

    public function index()
    {
        $page = $this->Page;

        // modified_by
        $page->addNew('modified_by');
        $page->get('modified_by')->setDisplayFrom('created_user.name');

        // modified_on
        $page->addNew('modified_on');
        $page->get('modified_on')->setDisplayFrom('created');

        // addtotoolbar addtoolbar
        parent::index();

        // MonPib
        $page->exclude(['signed_document_id', 'security_user_id']);
        $page->move('name')->after('model');
        $page->move('signed_by_id')->after('modified_on');
        $page->move('signed_link')->after('signed_by_id');
        $page->move('is_signed')->after('signed_link');
        $data = $page->getVar('data');
        foreach ($data as $key => $item) {
            if ($item->security_user_id) {
                $item->name = $item->user->name;
            } else {
                $item->name = $item->institution->name;
            }
        }
        if ($this->request->action === 'index') {
            $this->viewBuilder()->template('Institution.Institutions/signing_index');
        }//*/
    }

    // MonPib
    public function sign()
    {
        $this->Sign->sign();
    }

    public function setInstitutionIdForUsers()
    {
        set_time_limit(3600);
        $session = $this->request->session();

        $userType = 'Student';
        if ($session->check(__FUNCTION__ . 'FromUsers')) {
            $userType = $session->read(__FUNCTION__ . 'FromUsers');
        } else {
            $session->write(__FUNCTION__ . 'FromUsers', $userType);
        }

        $usersHistoriesTable = TableRegistry::get('Institution.' . $userType . 'Histories');
        if ($session->check(__FUNCTION__)) {
            $usersHistories = $session->read(__FUNCTION__);
        } else {
            $usersHistories = $usersHistoriesTable->find()->where(['model' => $userType . 'User'])->all()->toArray();
            $session->write(__FUNCTION__, $usersHistories);
        }

        if (!$usersHistories && $userType === 'Student') {
            $session->write(__FUNCTION__ . 'FromUsers', 'Staff');
        }

        foreach ($usersHistories as $key => $usersHistory) {
            $institutionUser = null;
            if ($userType === 'Student') {
                $institutionUser = TableRegistry::get('Institution.Students')->find()
                    ->where(['student_id' => $usersHistory->get('security_user_id')])->first();
            }
            if ($userType === 'Staff') {
                $institutionUser = TableRegistry::get('Institution.Staff')->find()
                    ->where(['staff_id' => $usersHistory->get('security_user_id')])->first();
            }

            if ($institutionUser) {
                $usersHistory->institution_id = $institutionUser->get('institution_id');
                $usersHistoriesTable->save($usersHistory);
            }

            unset($usersHistories[$key]);
        }

        $session->write(__FUNCTION__, $usersHistories);
    }

    private function getBetweenDateList(string $beginDate = '2015-01-01', bool $reversed = false, bool $crossReversed = true)
    {
        $result = ['from' => [], 'to' => []];
        $period = new \DatePeriod(
            new \DateTime($beginDate),
            new \DateInterval('P1M'),
            new \DateTime(date('Y-m-d'))
        );

        foreach ($period as $key => $value) {
            $result['from'][$value->format('Y-m-d')] = $value->format('Y-m-d');
            $result['to'][$value->format('Y-m-d')] = $value->format('Y-m-d');
        }

        if (!isset($result['from'][date('Y-m-d')])) {
            $result['from'][date('Y-m-d')] = date('Y-m-d');
        }
        if (!isset($result['to'][date('Y-m-d')])) {
            $result['to'][date('Y-m-d')] = date('Y-m-d');
        }

        if ($reversed) {
            if (!$crossReversed) {
                $result['from'] = array_reverse($result['from'], true);
            }
            $result['to'] = array_reverse($result['to'], true);
        }

        return $result;
    }
}
