<?php
namespace MonTemplateReports\Model\Table;

use App\Model\Table\AppTable;

class MonTemplateReportsTable extends AppTable
{
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->belongsTo('MonTemplateTypes', [
            'className' => 'MonTemplateReports.MonTemplateTypes',
            'foreignKey' => 'type_id'
        ]);
        $this->hasMany('MonStatisticReports', [
            'className' => 'MonStatisticReports.MonStatisticReports',
            'foreignKey' => 'template_id'
        ]);
        $this->addBehavior('Page.RestrictDelete');
    }
}
