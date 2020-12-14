<?php

namespace App\Controller;

use App\Services\GradeCreator\Creator;
use Cake\Network\Response;
use Education\Model\Table\EducationProgrammesTable;
use Education\Model\Table\EducationGradesTable;
use Cake\Log\Log;

/**
 * Class StaffCreatorController
 * @package App\Controller
 *
 * @property StaffTable $Staff
 * @property UsersTable $Users
 * @property UserNationalitiesTable UserNationalities
 * @property InstitutionPositionsTable InstitutionPositions
 */
class GradeCreatorController extends AppController
{
    private $cyclesIds = array('25'=>array('23','24','25','26'), '38'=>array('23','24'), '41'=>array('23','24','25','26','27'));
    public function initialize()
    {
        parent::initialize();
        $this->loadModel('Education.EducationProgrammes');
        $this->loadModel('Education.EducationGrades');
    }

    public function create()
    {

        //print_r($this->cyclesIds);die;


        $tempArray = array();
        try {
            $start_date = '2020-07-02 00:00:00';
            $end_date = '2020-07-07 23:59:00';
            foreach ($this->cyclesIds as $keyCycles => $cycles) {
                $educationProgrammes = $this->EducationProgrammes
                    ->find()
                    ->where(['education_cycle_id' => $keyCycles, 'created BETWEEN :start AND :end'])
                    ->bind(':start', new \DateTime($start_date), 'datetime')
                    ->bind(':end',   new \DateTime($end_date), 'datetime')
                    ->toArray();
                if (count($educationProgrammes) > 0) {
                    foreach ($cycles as $keyStage => $stageId) {

                        foreach ($educationProgrammes as $keyProgramme => $programme) {

                            $data = array();

                            $education_programme_id = $programme->id;
                            $educProgName = $programme->name;
                            //$educProgName = str_replace('(', '', $educProgName);
                            //$educProgName = str_replace(')', '', $educProgName);
                            $duration = $programme->duration;//$this->getDuration($keyCycles);
                            $admission_age = $this->getAdmissionAge($keyCycles);
                            //if (strpos($educProgName, '4') !== false || strpos($educProgName, '2') !== false || strpos($educProgName, '5') !== false){
                            //$educProgName = mb_substr($educProgName, 0, -1);
                            //}
                            $educProgShortName = $this->getEducProgShortName(trim($educProgName));



                            //$lastLetter = mb_substr($educProgShortName, -1);
                            //if ($lastLetter == '4' || $lastLetter == '5' || $lastLetter == '2') {
                            //$educProgShortName = trim(mb_substr($educProgShortName, 0, -1));
                            //}
                            $stageNumber = $keyStage + 1;
                            $educProgShortName = $educProgShortName . ' ' . $duration . '.' . $stageNumber;


                            $educationGradesArray = $this->EducationGrades
                                ->find()
                                ->where(['education_stage_id' => $stageId, 'education_programme_id' => $education_programme_id])
                                ->first();



                            if (count($educationGradesArray) == 0) {
                                print_r($programme);die;
                                $educationGradesByCode = $this->EducationGrades
                                    ->find()
                                    ->where(['code' => $educProgShortName])
                                    ->first();

                                if (count($educationGradesByCode) > 0){
                                    $educProgShortName = $this->getEducProgShortName2(trim($educProgName));
                                    $educProgShortName = $educProgShortName . ' ' . $duration . '.' . $stageNumber;

                                }


                                $data['name'] = $educProgShortName;
                                $data['code'] = $educProgShortName;
                                $data['education_stage_id'] = $stageId;
                                $data['admission_age'] = $admission_age;
                                $data['education_programme_id'] = $education_programme_id;
                                $data['visible'] = 1;



                                $EducationGrade = $this->EducationGrades->newEntity($data);
                                $this->EducationGrades->save($EducationGrade);
                            }
                        }
                    }
                }
            }

            //print_r($tempArray);
            die;


            //print_r($institutionByOkpo);die;

            /*$parser = new ExcelParser('../src/Services/PersonalCreator/orgs.xlsx');
            $creator = new Creator($this->Staff, $this->Users, $this->UserNationalities, $this->InstitutionPositions);

            foreach($parser->getCodes() as $code) {
                $creator->create($code);
            }*/
        }catch(Exception $e) {
            Log::write('debug', $e->getMessage());
        }

        return new Response();
    }

    function getAdmissionAge($keyCycles){
        if($keyCycles == 25){
            return 17;
        }
        if($keyCycles == 38){
            return 20;
        }
        if($keyCycles == 41){
            return 17;
        }
    }

    function getDuration($keyCycles){
        if($keyCycles == 25){
            return 4;
        }
        if($keyCycles == 38){
            return 2;
        }
        if($keyCycles == 41){
            return 5;
        }
    }

    function getEducProgShortName($educProgName){
        $name = '';
        $educProgNameArray = explode(' ', $educProgName);
        if(count($educProgNameArray) == 1){
            $name = $educProgName;
        }else {
            foreach ($educProgNameArray as $key => $item) {
                $name .= mb_strtoupper(mb_substr($item, 0, 1));
            }
        }
        return $name;
    }
    function getEducProgShortName2($educProgName){
        $name = '';
        $educProgNameArray = explode(' ', $educProgName);
        foreach($educProgNameArray as $key=>$item){
            if($key == 1){
                $name .= mb_strtoupper (mb_substr($item, 0, 3));
            }else {
                $name .= mb_strtoupper(mb_substr($item, 0, 1));
            }
        }
        return $name;
    }
}