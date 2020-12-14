<?php

namespace App\Services\EducationCreator;

use Cake\Log\Log;
use Cake\ORM\TableRegistry;
use Exception;
use Cake\ORM\Entity;

class Creator
{
    private $EducationProgrammesTable;
    private $EducationSpecializationTable;

    public function __construct($EducationProgrammesTable, $EducationSpecializationTable)
    {
        $this->EducationProgrammesTable = $EducationProgrammesTable;
        $this->EducationSpecializationTable = $EducationSpecializationTable;
    }

    public function create($itemProgramme){

        $years_of_study = trim($itemProgramme[0]);
        $education_form_of_training = trim($itemProgramme[1]);
        $education_cycle = trim($itemProgramme[2]);
        $education_certification = trim($itemProgramme[3]);
        $education_specialization = trim($itemProgramme[4]);
        $specialty = trim($itemProgramme[6]);

        $EducationProgrammesArray = $this->getAllEducationProgramme($education_cycle);
        $education_field_of_study_array = $this->getEducationFieldsOfStudies($education_specialization);

        if(count($education_field_of_study_array) > 0) {
            $education_specialization_id = $education_field_of_study_array[0];
            $education_field_of_study_id = $education_field_of_study_array[1];
            if (!$this->findProgramme($years_of_study, $education_form_of_training, $education_specialization_id, $specialty, $education_field_of_study_id, $EducationProgrammesArray)) {
                $this->createRecordProgramme($years_of_study, $education_form_of_training, $education_specialization_id, $specialty, $education_field_of_study_id,
                    $education_certification, $education_cycle, $education_specialization);
            }else{
                Log::write('debug', "There is a specialty in code {$education_specialization}");
            }
        }else{
            Log::write('debug', "No specialty in code {$education_specialization}");
        }
    }

    function createRecordProgramme($years_of_study, $education_form_of_training, $education_specialization_id, $specialty,
                                   $education_field_of_study_id, $education_certification, $education_cycle, $education_specialization){
        $data = array();
        $data['code'] = $education_specialization . '_' . $years_of_study . ' ' . $this->getSpecialtyShortName($specialty);
        $data['name'] = $specialty;
        $data['duration'] = $years_of_study;
        $data['education_field_of_study_id'] = $education_field_of_study_id;

        $data['education_specialization_id'] = $education_specialization_id;
        $data['education_form_of_training_id'] = $education_form_of_training;
        $data['education_cycle_id'] = $education_cycle;
        $data['education_certification_id'] = $education_certification;



        $entity = $this->EducationProgrammesTable->save($this->EducationProgrammesTable->newEntity($data));
        if(!$entity) {
            Log::write('debug', "Creation error by code {$education_specialization} by specialization {$education_specialization}");
        }
    }

    function getSpecialtyShortName($specialty){

        $name = '';
        $specialty = str_replace('(', '', $specialty);
        $specialty = str_replace(')', '', $specialty);

        $specialtyArray = explode(' ', $specialty);
        if(count($specialtyArray) == 1){
            $name = $specialty;
        }else {
            foreach ($specialtyArray as $key => $item) {
                $name .= mb_strtoupper(mb_substr($item, 0, 1));
            }
        }
        return $name;
    }

    function getAllEducationProgramme($education_cycle){
        $educationProgrammesArray = $this->EducationProgrammesTable->find()
            ->where(['education_cycle_id'=>$education_cycle])
            ->toArray();
        return $educationProgrammesArray;
    }

    function findProgramme($years_of_study, $education_form_of_training, $education_specialization_id, $specialty, $education_field_of_study_id, $EducationProgrammesArray){
        foreach ($EducationProgrammesArray as $keyProgramme=>$itemProgramme){
            $name = $itemProgramme->name;
            $duration = $itemProgramme->duration;
            $visible = $itemProgramme->visible;
            $education_field_of_study_id_programme = $itemProgramme->education_field_of_study_id;

            $education_specialization_id_programme = $itemProgramme->education_specialization_id;
            $education_form_of_training_id_programme = $itemProgramme->education_form_of_training_id;
            if($visible && $specialty == $name && $years_of_study == $duration && $education_form_of_training == $education_form_of_training_id_programme
                && $education_specialization_id == $education_specialization_id_programme && $education_field_of_study_id == $education_field_of_study_id_programme){
                return true;
            }
        }
        return false;
    }

    function getEducationFieldsOfStudies($education_specialization_code){
        $educationFieldOfStudiesArray = $this->EducationSpecializationTable->find()
            ->where(['specialization_code'=>$education_specialization_code])
            ->toArray();
        if(count($educationFieldOfStudiesArray) > 0){
            return array($educationFieldOfStudiesArray[0]->id, $educationFieldOfStudiesArray[0]->education_field_of_studies_id);
        }
        return array();
    }
}