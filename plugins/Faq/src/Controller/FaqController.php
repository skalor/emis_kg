<?php
namespace Faq\Controller;

use App\Controller\PageController;
use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
//use http\Client\Response;

use Faq\Controller\AppController;

/**
 * Faq Controller
 *
 * @property \Faq\Model\Table\FaqTable $Faq
 */
class FaqController extends PageController
{


//    public function initialize()
//    {
//        parent::initialize();
//
//        $this->loadComponent('Flash');
//        $this->loadComponent('Paginator');
//    }

    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        $action = $this->request->action;
        $page = $this->Page;

        $page->addCrumb('Faq', ['plugin' => 'Faq', 'controller' => 'Faq', 'action' => 'index']);
        if ($action) {
            $page->addCrumb($action);
        }
        $page->enable(['download']);
        $page->setHeader(__('Faq'));

        if (in_array($action, ['add', 'edit'])) {
            $page->get('question')->setControlType('textarea');
            $page->get('answer')->setControlType('textarea');


            $page->get('lang')->setControlType('select')->setOptions( $this->Faq->getLocation(),false);
//            $page->get('audit')->setControlType('select')->setOptions(
//                [
//                    'All'=>__('All'),
//                    'Super Role'=>__('Super Role'),
//                    'Group Administration'=>__('Group Administration'),
//                    'Admin'=>__('Admin'),
//                    'Operator'=>__('Operator'),
//                    'District officer'=>__('District officer'),
//                    'Principal'=>__('Principal'),
//                    'Deputy Principal'=>__('Deputy Principal'),
//                    'Class Teacher'=>__('Class Teacher'),
//                    'Teacher'=>__('Teacher'),
//                    'Parent'=>__('Parent'),
//                    'School'=>__('School'),
//                ],false);
            $page->get('category')->setControlType('select')->setOptions(
                [
                    'general'=>__('general'),
                    'Academic'=>__('Academic'),
                    'Students'=>__('Students'),
                    'Staff'=>__('Staff'),
                    'Other'=>__('Other Section'),
                    'file'=>__('File'),
                    'video'=> __('Video'),
                    'outer'=> __('Outer'),
                ],false);


            $page->get('file_name')->setControlType('link');
            $page->get('audit')->setControlType('link');
        }
    }

    /**
     * Index method
     *
     * @return \Cake\Network\Response|null
     */
    public function index()
    {
        parent::index();
        $this->Page->exclude(['params', 'audit']);

        $this->paginate = [
            'contain' => ['ModifiedUsers', 'CreatedUsers']
        ];
        $faq = $this->paginate($this->Faq);

        $this->set(compact('faq'));
        $this->set('_serialize', ['faq']);

    }

    public function faq( ) {
//        $faq = $this->Faq->getFAQ();
//        $this->set('faq_question',  $faq['inner']);
//        $this->set('faq_video',     $faq['video']);
//        $this->set('faq_education', $faq['education']);
        $userId = $this->Auth->user('id');
        $User = TableRegistry::get('User.Users')->get($userId);


        $userInfo = $this->AccessControl->getRolesByUser($this->Auth->user('id'));

        if ($userInfo->toArray()[0]){
            $userRoleId = $userInfo->toArray()[0]->security_role_id;
        }else {
            $userRoleId = 2;
        }




        $roles = TableRegistry::get("security_roles")->get($userRoleId);

        $user_role =$roles->name;

        $this->set('user_name', $User->first_name.' '.$User->last_name);
        $this->set('user_email', $User->email);
        $this->set('user_phone', $User->mobile_phone);
        $this->set('user_address',  $User->address);
        $this->set('user_position',$user_role);


    }

    public function test( ) {
//        $faq = $this->Faq->getFAQ();
//        $this->set('faq_question',  $faq['inner']);
//        $this->set('faq_video',     $faq['video']);
//        $this->set('faq_education', $faq['education']);
        $userId = $this->Auth->user('id');
        $User = TableRegistry::get('User.Users')->get($userId);


        $userInfo = $this->AccessControl->getRolesByUser($this->Auth->user('id'));

        if ($userInfo->toArray()[0]){
            $userRoleId = $userInfo->toArray()[0]->security_role_id;
        }else {
            $userRoleId = 2;
        }




        $roles = TableRegistry::get("security_roles")->get($userRoleId);

        $user_role =$roles->name;

        $this->set('user_name', $User->first_name.' '.$User->last_name);
        $this->set('user_email', $User->email);
        $this->set('user_phone', $User->mobile_phone);
        $this->set('user_address',  $User->address);
        $this->set('user_position',$user_role);


    }

    public function guest( ) {

    }

    public function getFileReader( ) {
        $this->render('Faq/Viewer/index');
    }

    public function api() {

        header('Content-type: application/json');
        echo json_encode($this->Faq->getInnerFAQ(),JSON_PARTIAL_OUTPUT_ON_ERROR);
        die;
    }
    public function apiOuter() {
        header('Content-type: application/json');
        echo json_encode($this->Faq->getOuterFAQ(),JSON_PARTIAL_OUTPUT_ON_ERROR);
        die;
    }




    function getFile($id) {
        $file = $this->Faq->getFile($id);
        header('Content-type: application/pdf');
        echo $file;
        die;
    }

    function download($id,$fileColumn) {
        Configure::write('debug', 0);
        $file = $this->Faq->getFile($id);
        $type= $file['type'];

        $nam = $file['name'];
//
        header("Content-type: $type");
        header('Content-length: $length');
        header('Content-Disposition: attachment; filename=$nam');
        echo $file['data'];
        exit();
    }

    public function downloadFile($filecontent, $filename, $filesize)
    {
        header("Pragma: public", true);
        header("Expires: 0"); // set expiration time
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: " . $filesize);
        echo $filecontent;
    }
}
