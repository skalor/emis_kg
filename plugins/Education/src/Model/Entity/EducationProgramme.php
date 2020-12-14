<?php
namespace Education\Model\Entity;

use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;

class EducationProgramme extends Entity
{
    protected $_virtual = ['cycle_programme_name'];

    protected function _getCycleProgrammeName() {
        $name = $this->name;
        if ($this->has('education_cycle') && $this->education_cycle->has('name')) {
            $name = __($this->education_cycle->name) . ' - ' . __($name);
            if($this->has('education_form_of_training_id')){
                $education_form_of_training_id = $this->get('education_form_of_training_id');
                $table = TableRegistry::get('Education.EducationFormOfTraining');
                if($education_form_of_training_id)
                    $name = $name  . ' / ' . __($table->get($education_form_of_training_id)->name);
            }
            /*if($this->has('education_specialization_id')){

                $education_specialization_id = $this->get('education_specialization_id');
                $table = TableRegistry::get('Education.EducationSpecialization');
                if($education_specialization_id)
                    $name = $name  . ' / ' . __($table->get($education_specialization_id)->name);
            }*/
        } else {
            $table = TableRegistry::get('Education.EducationCycles');
            $cycleId = $this->education_cycle_id;
            $name = __($table->get($cycleId)->name) . ' - ' . __($name);

            $table = TableRegistry::get('Education.EducationFormOfTraining');
            $education_form_of_training_id = $this->education_form_of_training_id;
            if($education_form_of_training_id)
                $name = $name  . ' / ' . __($table->get($education_form_of_training_id)->name);

            /*$table = TableRegistry::get('Education.EducationSpecialization');
            $education_specialization_id = $this->education_specialization_id;
            if($education_specialization_id)
                $name = $name  . ' / ' . __($table->get($education_specialization_id)->name);*/
        }
        return $name;
    }
}
