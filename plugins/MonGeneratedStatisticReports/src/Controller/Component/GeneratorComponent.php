<?php
namespace MonGeneratedStatisticReports\Controller\Component;

use Cake\Controller\Component;
use Cake\I18n\I18n;
use Cake\ORM\TableRegistry;

class GeneratorComponent extends Component
{
    public $model;
    public $components = [
        'MonGeneratedStatisticReports.Templator',
        'MonGeneratedStatisticReports.Pdf',
        'MonGeneratedStatisticReports.Excel',
    ];

    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->model = TableRegistry::get('MonGeneratedStatisticReports.MonGeneratedStatisticReports');
    }

    public function generate(int $id)
    {
        $query = $this->model->find()->contain(['MonStatisticReports' => ['MonTemplateReports']])->where(['MonGeneratedStatisticReports.id' => $id]);
        $entity = $query->first();
        $params = $entity->params ? unserialize($entity->params) : [];
        isset($params['locale']) ? I18n::locale($params['locale']) : null;
        $content = $entity->mon_statistic_report->mon_template_report->content;
        $templator = $this->Templator->setTemplate($content);
        $params['user']['institution_id'] = $entity->institution_id;
        $params['user']['academic_period_id'] = $entity->academic_period_id;
        $params['user']['for_date'] = $entity->mon_statistic_report->for_date;
        $templator->setUser($params['user']);
        $content = $templator->render();
        
        if ($templator->getErrors()) {
            pr($templator->getErrors());
        }

        switch ($entity->file_type) {
            case 'PDF':
                $file = [
                    'name' => $entity->mon_statistic_report->name . '_' . time() . '.pdf',
                    'content' => $this->Pdf->setTemplate($content)->render()
                ];
                break;
            case 'Excel':
                $file = [
                    'name' => $entity->mon_statistic_report->name . '_' . time() . '.xlsx',
                    'content' => $this->Excel->setTemplate($content)->render()
                ];
                break;
            default:
                $file = ['name' => null, 'content' => null];
        }

        $entity->file_name = $file['name'];
        $entity->file_content = $file['content'];
        $timeZone = isset($params['time_zone']) ? $params['time_zone'] : 'Asia/Bishkek';
        $dateTime = new \DateTime("NOW", new \DateTimeZone($timeZone));
        $params['pid'] = 0;
        $params['status'] = 1;
        $params['end_datetime'] = $dateTime->format('Y-m-d H:i:s');
        $entity->params = serialize($params);
        $this->model->beforeSaveCondition = false;
        $this->model->afterSaveCommitCondition = false;

        return $this->model->save($entity);
    }
}
