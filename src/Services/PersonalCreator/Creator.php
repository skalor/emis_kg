<?php

namespace App\Services\PersonalCreator;

use Cake\Log\Log;
use Cake\ORM\TableRegistry;
use Exception;
use Institution\Model\Entity\Institution;
use Institution\Model\Table\InstitutionPositionsTable;
use Institution\Model\Table\StaffTable;
use User\Model\Entity\User;
use User\Model\Table\UserNationalitiesTable;
use User\Model\Table\UsersTable;

class Creator
{
    const TYPE_FULL_RATE = 3;
    const NATIONALITY_KYRGYZ = 1;
    const STATUS_ACTIVE = 1;
    // On real: 852, On test: 558
    const DEFAULT_STAFF_POSITION_TITLE_ID = 852;

    const GENDER_MALE = 1;

    /**
     * @var StaffTable
     */
    private $staffTable;

    /**
     * @var UsersTable
     */
    private $usersTable;

    /**
     * @var UserNationalitiesTable
     */
    private $nationalitiesTable;

    /**
     * @var InstitutionPositionsTable
     */
    private $positionsTable;

    public function __construct(StaffTable $staffTable, UsersTable $usersTable, UserNationalitiesTable $nationalitiesTable, InstitutionPositionsTable $positionsTable)
    {
        $this->staffTable = $staffTable;
        $this->usersTable = $usersTable;
        $this->nationalitiesTable = $nationalitiesTable;
        $this->positionsTable = $positionsTable;
    }

    public function create(int $code)
    {
        try {
            $this->guardExistsUserWithCode($code);
            $institution = $this->getInstitutionByCode($code);


            $positionId = $this->getPositionId($institution, self::DEFAULT_STAFF_POSITION_TITLE_ID);

            $name = 'Специалист';

            $user = new User([
                'username' => $code,
                'password' => $code,
                'first_name' => $name,
                'last_name' => $name,
                'openemis_no' => $this->usersTable->getUniqueOpenemisId(['model' => 'Staff']),
                'gender_id' => self::GENDER_MALE,
                'date_of_birth' => '1990-01-01',
                'nationality_id' => self::NATIONALITY_KYRGYZ,
                'identity_type_id' => 453,
                'identity_number' => $code,
                'status' => self::STATUS_ACTIVE,
                'preferred_language' => 'en',
            ]);

            $this->usersTable->save($user);

            $StaffStatusesTable = TableRegistry::get('Staff.StaffStatuses');

            $staff = $this->staffTable->newEntity([
                'FTE' => 1.00,
                'start_date' => '01-09-2019',
                'start_year' => date('Y') - 1,
                'staff_id' => $user->id,
                'staff_type_id' => self::TYPE_FULL_RATE,
                'staff_status_id' => $StaffStatusesTable->getIdByCode('ASSIGNED'),
                'institution_id' => $institution->id,
                'institution_position_id' => $positionId,
                'position_type' =>  'Full-Time',
                'academic_period_id' => 30,
            ], ['validate' => 'AllowPositionType']);

            $this->staffTable->save($staff);

            $nationality = $this->nationalitiesTable->newEntity([
                'preferred' => 1,
                'nationality_id' => self::NATIONALITY_KYRGYZ,
                'security_user_id' => $user->id,
            ]);

            $this->nationalitiesTable->save($nationality);
        } catch(Exception $e) {
            Log::write('debug', $e->getMessage());
        }
    }

    private function getPositionId(Institution $institution, int $staffPositionTitleId): int
    {
        $position = $this->positionsTable->find()->where([
            'staff_position_title_id' => $staffPositionTitleId,
            'institution_id' => $institution->id,
        ])->first();

        if($position) {
            return $position->id;
        }

        throw new Exception("The position {$staffPositionTitleId} for code {$institution->code} doesn't exist.");
    }

    private function guardExistsUserWithCode(int $code): void
    {
        $user = $this->usersTable->find()->where([
            'username' => $code,
        ])->first();

        if($user) {
            throw new Exception("The user with username {$code} already exists.");
        }
    }

    private function getInstitutionByCode(int $code): ?Institution
    {
        $institution = TableRegistry::get('Institution.Institutions');

        $institution = $institution->find()->where([
            'code' => $code,
        ])->first();

        if($institution) {
            return $institution;
        }

        throw new Exception("The institution with code {$code} doesn't exist.");
    }
}