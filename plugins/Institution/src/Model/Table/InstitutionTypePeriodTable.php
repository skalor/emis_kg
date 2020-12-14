<?php
namespace Institution\Model\Table;

use App\Model\Table\AppTable;

class InstitutionTypePeriodTable extends AppTable
{
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->belongsTo('InstitutionPeriod', ['className' => 'Institution.InstitutionPeriod', 'foreignKey' => 'institution_period_id']);
        $this->belongsTo('Types', ['className' => 'Institution.Types' , 'foreignKey' => 'institution_types_id']);
    }
}
