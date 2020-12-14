<?php
namespace MonAPI\Controller;

use App\Controller\RestfulController as BaseController;

class RestfulController extends BaseController
{
    public $multi;

    public function initialize()
    {
        parent::initialize();
        if (isset($this->request->action) && $this->request->action === 'generateToken') {
            $this->eventManager()->off($this->Csrf);
        }
        $this->Auth->allow('generateToken');
        $this->multi = $this->request->query('multi') === 'true' ? true : false;
        $this->loadComponent('MonAPI.Restful');

        $action = $this->request->action;
        switch ($action) {
            case 'getInstitution':
            case 'addInstitution':
            case 'updateInstitution':
                $this->loadComponents(['MonAPI.Institution']);
                break;
            case 'getInstitutionOpenData':
                $this->loadComponents(['MonAPI.InstitutionOpenData']);
                break;
            case 'getStaff':
            case 'addStaff':
            case 'updateStaff':
                $this->loadComponents(['MonAPI.Institution', 'MonAPI.User', 'MonAPI.Staff']);
                break;
            case 'getStudent':
            case 'getStudentStudying':
            case 'addStudent':
            case 'updateStudent':
                $this->loadComponents(['MonAPI.Institution', 'MonAPI.Class', 'MonAPI.User', 'MonAPI.Student']);
                break;
            case 'getDashboardData':
                $this->loadComponents(['MonAPI.Dashboard']);
                break;
        }
    }

    public function isAuthorized($user = null)
    {
        return $this->Restful->isAuthorized($user);
    }

    public function loadComponents(array $components)
    {
        foreach ($components as $component) {
            $this->loadComponent($component);
        }

        return true;
    }

    public function generateToken()
    {
        $this->Restful->generateToken();
    }


    /**
     * MonAPI methods
     */

    public function getInstitution()
    {
        $this->request->query['_all'] = false;
        $this->Institution->get($this->request->query, false);
    }

    public function getInstitutionOpenData()
    {
        $this->request->query['_all'] = false;
        $this->InstitutionOpenData->get($this->request->query, false);
    }

    public function addInstitution()
    {
        if ($this->multi)
            $this->Institution->addMulti();
        else
            $this->Institution->add();
    }

    public function updateInstitution()
    {
        if ($this->multi)
            $this->Institution->updateMulti();
        else
            $this->Institution->update();
    }

    public function getStaff()
    {
        $this->request->query['_all'] = false;
        $this->Staff->get($this->request->query, false);
    }

    public function addStaff()
    {
        if ($this->multi)
            $this->Staff->addMulti();
        else
            $this->Staff->add();
    }

    public function updateStaff()
    {
        if ($this->multi)
            $this->Staff->updateMulti();
        else
            $this->Staff->update();
    }

    public function getStudent()
    {
        $this->request->query['_all'] = false;
        $this->Student->get($this->request->query, false);
    }

    public function getStudentStudying()
    {
        $this->request->query['_all'] = false;
        $this->Student->getStudying($this->request->query);
    }

    public function addStudent()
    {
        if ($this->multi)
            $this->Student->addMulti();
        else
            $this->Student->add();
    }

    public function updateStudent()
    {
        if ($this->multi)
            $this->Student->updateMulti();
        else
            $this->Student->update();
    }

    public function getDashboardData()
    {
        $this->Dashboard->get();
    }

    public function updateOpenSSLKeys()
    {
        $this->Restful->updateOpenSSLKeys();
    }

    /*public function parseJkitep() {
        $this->JKitep->parse();
    }*/


}
