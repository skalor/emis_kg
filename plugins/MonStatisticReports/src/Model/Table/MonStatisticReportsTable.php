<?php
namespace MonStatisticReports\Model\Table;

use App\Model\Table\AppTable;
use Cake\Event\Event;
use Cake\ORM\Entity;

class MonStatisticReportsTable extends AppTable
{
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->belongsTo('MonTemplateReports', ['className' => 'MonTemplateReports.MonTemplateReports', 'foreignKey' => 'template_id']);
        $this->hasMany('MonGeneratedStatisticReports', [
            'className' => 'MonGeneratedStatisticReports.MonGeneratedStatisticReports',
            'foreignKey' => 'mon_statistic_report_id'
        ]);
        $this->addBehavior('Page.RestrictDelete');
    }
    
    public function beforeSave(Event $event, Entity $entity)
    {
        $params = [
            'areasIds' => [],
            'areaAdministrativesIds' => [],
            'institutionTypesIds' => []
        ];

        if (isset($entity->areas['_ids']) && $entity->areas['_ids']) {
            $params['areasIds'] = $entity->areas['_ids'];
        }

        if (isset($entity->area_administratives['_ids']) && $entity->area_administratives['_ids']) {
            $params['areaAdministrativesIds'] = $entity->actions['_ids'];
        }

        if (isset($entity->institution_types['_ids']) && $entity->institution_types['_ids']) {
            $params['institutionTypesIds'] = $entity->institution_types['_ids'];
        }

        $entity->params = serialize($params);
    }
}
