<?php

namespace App\Controller;

use App\Services\DataDumper\DataDumper;
use App\Services\DataDumper\PinExporter;
use Cake\Controller\Controller;
use Cake\Network\Response;

class DataDumpController extends Controller
{
    public function export(): Response
    {
        ini_set('max_execution_time', 0);
    
        $this->institutionPin();
        
        return new Response([
            'body' => 'Операция закончена.'
        ]);
    }
    
    public function numberOfWorkrooms(): void
    {
        $dumper = new DataDumper('institution');
        $dumper->dump(29, 'number_of_workrooms');
    }
    
    public function numberOfLaboratories(): void
    {
        $dumper = new DataDumper('institution');
        $dumper->dump(30, 'number_of_laboratories');
    }
    
    public function typeOfLegalForm(): void
    {
        $dumper = new DataDumper('institution');
        $dumper->dump(20, 'type_of_legal_form', true);
    }
    
    public function numberOfEmployeesInZeroGrades(): void
    {
        $dumper = new DataDumper('institution');
        $dumper->dump(16, 'number_of_employees_in_zero_grades');
    }
    
    public function projectCapacity(): void
    {
        $dumper = new DataDumper('institution');
        $dumper->dump(5, 'project_capacity');
        $dumper->dump(23, 'project_capacity');
    }
    
    public function languageOfInstitution(): void
    {
        $dumper = new DataDumper('institution');
        $dumper->dump(2, 'language_of_institution', true);
    }
    
    public function workDuration(): void 
    {
        $dumper = new DataDumper('institution');
        $dumper->dump(15, 'work_duration', true);
    }
    
    public function founderType(): void
    {
        $dumper = new DataDumper('institution');
        $dumper->dump(8, 'founder_type', true);
    }
    
    public function founderName(): void
    {
        $dumper = new DataDumper('institution');
        $dumper->dump(19, 'founder_name');
    }
    
    private function pin(): void
    {
        (new PinExporter())->export();
        
        $dumper = new DataDumper('staff');
        $dumper->dump(3, 'pin');
        $dumper->setType('student');
        $dumper->dump(30, 'pin');
    }
    
    public function institutionPin(): void
    {
        $dumper = new DataDumper('institution');
        $dumper->dump(3, 'pin');
    }
    
    private function motherName(): void
    {
        $dumper = new DataDumper('student');
        $dumper->setPrimaryKey('student_id');
        $dumper->setExportTable('Student.Guardians');
        $dumper->dump(1, 'mother_name');
    }
    
    public function motherSurname(): void
    {
        $dumper = new DataDumper('student');
        $dumper->setPrimaryKey('student_id');
        $dumper->setExportTable('Student.Guardians');
        $dumper->dump(2, 'mother_surname');
    }
    
    public function motherAddress(): void
    {
        $dumper = new DataDumper('student');
        $dumper->setPrimaryKey('student_id');
        $dumper->setExportTable('Student.Guardians');
        $dumper->dump(4, 'mother_address');
    }
    
    public function motherPhoneNumber(): void
    {
        $dumper = new DataDumper('student');
        $dumper->setPrimaryKey('student_id');
        $dumper->setExportTable('Student.Guardians');
        $dumper->dump(5, 'mother_phone_number');
    }
    
    public function motherEmail(): void
    {
        $dumper = new DataDumper('student');
        $dumper->setPrimaryKey('student_id');
        $dumper->setExportTable('Student.Guardians');
        $dumper->dump(6, 'mother_email');
    }
    
    private function fatherName(): void
    {
        $dumper = new DataDumper('student');
        $dumper->setPrimaryKey('student_id');
        $dumper->setExportTable('Student.Guardians');
        $dumper->dump(10, 'father_name');
    }
    
    public function fatherSurname(): void
    {
        $dumper = new DataDumper('student');
        $dumper->setPrimaryKey('student_id');
        $dumper->setExportTable('Student.Guardians');
        $dumper->dump(11, 'father_surname');
    }
    
    public function fatherAddress(): void
    {
        $dumper = new DataDumper('student');
        $dumper->setPrimaryKey('student_id');
        $dumper->setExportTable('Student.Guardians');
        $dumper->dump(13, 'father_address');
    }
    
    public function fatherPhoneNumber(): void
    {
        $dumper = new DataDumper('student');
        $dumper->setPrimaryKey('student_id');
        $dumper->setExportTable('Student.Guardians');
        $dumper->dump(14, 'father_phone_number');
    }
    
    public function fatherEmail(): void
    {
        $dumper = new DataDumper('student');
        $dumper->setPrimaryKey('student_id');
        $dumper->setExportTable('Student.Guardians');
        $dumper->dump(15, 'father_email');
    }
    
    private function guardianName(): void
    {
        $dumper = new DataDumper('student');
        $dumper->setPrimaryKey('student_id');
        $dumper->setExportTable('Student.Guardians');
        $dumper->dump(19, 'guardian_name');
    }
    
    public function guardianSurname(): void
    {
        $dumper = new DataDumper('student');
        $dumper->setPrimaryKey('student_id');
        $dumper->setExportTable('Student.Guardians');
        $dumper->dump(20, 'guardian_surname');
    }
    
    public function guardianAddress(): void
    {
        $dumper = new DataDumper('student');
        $dumper->setPrimaryKey('student_id');
        $dumper->setExportTable('Student.Guardians');
        $dumper->dump(22, 'guardian_address');
    }
    
    public function guardianPhoneNumber(): void
    {
        $dumper = new DataDumper('student');
        $dumper->setPrimaryKey('student_id');
        $dumper->setExportTable('Student.Guardians');
        $dumper->dump(23, 'guardian_phone_number');
    }
    
    public function guardianEmail(): void
    {
        $dumper = new DataDumper('student');
        $dumper->setPrimaryKey('student_id');
        $dumper->setExportTable('Student.Guardians');
        $dumper->dump(24, 'guardian_email');
    }
    
    public function disability(): void
    {
        $dumper = new DataDumper('student');
        $dumper->dump(32, 'disability', true);
    }
    
    public function foreigner(): void
    {
        $dumper = new DataDumper('student');
        $dumper->dump(33, 'foreigner', true);
    }
    
    public function studentEducationForm(): void
    {
        $dumper = new DataDumper('student');
        $dumper->dump(40,   'student_education_form', true);
    }
    
    public function homeEducation(): void 
    {
        $dumper = new DataDumper('student');
        $dumper->dump(45, 'home_education', true);
    }
    
    public function responsibleGenderAndSocialCoordinator(): void
    {
        $dumper = new DataDumper('staff');
        $dumper->dump(4, 'responsible_gender_and_social_coordinator', true);
    }
    
    public function teacherEducation(): void
    {
        $dumper = new DataDumper('staff');
        $dumper->dump(5, 'teacher_education', true);
    }
    
    public function TeacherWithSpecialEducation(): void 
    {
        $dumper = new DataDumper('staff');
        $dumper->dump(6, 'teacher_with_special_education', true);
    }
    
    public function certificateOfInclusiveEducation(): void
    {
        $dumper = new DataDumper('staff');
        $dumper->dump(7, 'certificate_of_inclusive_education', true);
    }
}