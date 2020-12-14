<?php
namespace Institution\Model\Table;

use App\Model\Table\AppTable;

class InstitutionTypeClassesViewTable extends AppTable
{
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->belongsTo('InstitutionClassesView', ['className' => 'Institution.InstitutionClassesView', 'foreignKey' => 'institution_classes_view_id']);
        $this->belongsTo('Types', ['className' => 'Institution.Types' , 'foreignKey' => 'institution_types_id']);
    }
}
