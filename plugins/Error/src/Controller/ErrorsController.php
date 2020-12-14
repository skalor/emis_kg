<?php
namespace Error\Controller;

use App\Controller\AppController;
use Cake\Event\Event;

class ErrorsController extends AppController{
	public $name = 'Errors';

    public function beforeFilter(Event $event) {
    	parent::beforeFilter($event);
        //$this->redirect('https://emis.edu.gov.kg/');
        $this->Auth->allow('error404');
    }

    public function error404() {
    	//$this->layout = 'default';
        //$this->redirect('/');
//        $this->controller->redirect('/');
    }

    public function error403(){
    	//$this->layout = 'default';
    }
}