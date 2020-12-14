<?php

namespace HiddenField\Model\Repository;

use Cake\ORM\TableRegistry;
use FieldOption\Model\Table\InstitutionTypesTable;

class InstitutionTypeRepository
{
    /**
     * @var InstitutionTypesTable
     */
    private $typesTable;
    
    public function __construct()
    {
        $this->typesTable = TableRegistry::get('Institution.InstitutionTypes');
    }
    public function getTypes()
    {
        return $this->typesTable->find()->all()->toList();
    }
}