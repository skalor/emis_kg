<?php

namespace App\Controller;

use App\Services\PersonalCreator\Creator;
use App\Services\PersonalCreator\ExcelParser;
use Cake\Network\Response;
use Institution\Model\Table\InstitutionPositionsTable;
use Security\Model\Table\UsersTable;
use Staff\Model\Table\StaffTable;
use User\Model\Table\UserNationalitiesTable;

/**
 * Class StaffCreatorController
 * @package App\Controller
 *
 * @property StaffTable $Staff
 * @property UsersTable $Users
 * @property UserNationalitiesTable UserNationalities
 * @property InstitutionPositionsTable InstitutionPositions
 */
class StaffCreatorController extends AppController
{
    public function initialize()
    {

        parent::initialize();


        $this->loadModel('Institution.Staff');

        $this->loadModel('Institution.InstitutionPositions');
        $this->loadModel('User.Users');

        $this->loadModel('User.UserNationalities');
    }

    public function create()
    {

        /*$parser = new ExcelParser('../src/Services/PersonalCreator/orgs.xlsx');
        $creator = new Creator($this->Staff, $this->Users, $this->UserNationalities, $this->InstitutionPositions);
        //print_r($parser->getCodes());die;
        foreach($parser->getCodes() as $code) {
            $creator->create($code);
        }*/

        return new Response();
    }
}