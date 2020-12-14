<?php
namespace MonTemplateReports\Model\Table;

use App\Model\Table\ControllerActionTable;

class MonTemplateTypesTable extends ControllerActionTable
{
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->hasMany('MonTemplateReports', ['className' => 'MonTemplateReports.MonTemplateReports', 'foreignKey' => 'type_id']);
        $this->addBehavior('FieldOption.FieldOption');
    }
}
