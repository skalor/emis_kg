<?php
namespace Institution\Model\Table;

use App\Model\Table\AppTable;

class InstitutionTypeViewTable extends AppTable
{
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->belongsTo('InstitutionView', ['className' => 'Institution.InstitutionView', 'foreignKey' => 'institution_view_id']);
        $this->belongsTo('Types', ['className' => 'Institution.Types' , 'foreignKey' => 'institution_types_id']);
    }
}
