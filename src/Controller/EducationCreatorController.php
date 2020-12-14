<?php

namespace App\Controller;
use App\Services\EducationCreator\Creator;
use App\Services\EducationCreator\ExcelParser;
use Cake\Network\Response;
use Security\Model\Table\UsersTable;

/**
 * Class EducationCreatorController
 * @package App\Controller
 */
class EducationCreatorController extends AppController
{
    public function initialize()
    {
        parent::initialize();
        $this->loadModel('Education.EducationProgrammes');
        $this->loadModel('Education.EducationSpecialization');
    }

    public function create()
    {
        /*$parser = new ExcelParser('../src/Services/EducationCreator/education.xlsx');
        $educationFieldInfo = $parser->getInfo();

        $creator = new Creator($this->EducationProgrammes, $this->EducationSpecialization);
        foreach($educationFieldInfo as $keyProgramme=>$itemProgramme){
            if($keyProgramme == '0') continue;
            $creator->create($itemProgramme);
        }*/

        return new Response();
    }
}