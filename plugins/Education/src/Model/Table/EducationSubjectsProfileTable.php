<?php
namespace Education\Model\Table;

use App\Model\Table\ControllerActionTable;

class EducationSubjectsProfileTable extends ControllerActionTable
{
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->hasMany('EducationSubjects', ['className' => 'Education.EducationSubjects', 'foreignKey' => 'education_subjects_profile_id']);


        $this->addBehavior('FieldOption.FieldOption');


        $this->addBehavior('Restful.RestfulAccessControl', [
            'EducationSubjects' => ['index', 'add']
        ]);
    }


}