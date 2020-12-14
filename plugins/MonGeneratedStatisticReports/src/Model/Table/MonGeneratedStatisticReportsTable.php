<?php
namespace MonGeneratedStatisticReports\Model\Table;

use App\Model\Table\AppTable;
use Cake\Event\Event;
use Cake\I18n\I18n;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;

class MonGeneratedStatisticReportsTable extends AppTable
{
    public $beforeSaveCondition = true;
    public $afterSaveCommitCondition = true;
    public $fileUploadConfig = [
        'allowable_file_types' => ['pdf', 'xls', 'xlsx'],
        'size' => '2MB',
        'doNothing' => 1
    ];

    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->belongsTo('SignedBy',  ['className' => 'User.Users', 'foreignKey' => 'signed_by_id']);
        $this->belongsTo('Institution', ['className' => 'Institution.Institutions', 'foreignKey' => 'institution_id']);
        $this->belongsTo('AcademicPeriod', ['className' => 'AcademicPeriod.AcademicPeriods', 'foreignKey' => 'academic_period_id']);
        $this->belongsTo('MonStatisticReports',  ['className' => 'MonStatisticReports.MonStatisticReports', 'foreignKey' => 'mon_statistic_report_id']);
        $this->addBehavior('Page.FileUpload', $this->fileUploadConfig);
    }

    public function implementedEvents()
    {
        $events = parent::implementedEvents();
        $events['Restful.Model.isAuthorized'] = ['callable' => 'isAuthorized', 'priority' => 1];
        return $events;
    }

    public function isAuthorized(Event $event, $scope, $action, $extra)
    {
        if ($action == 'download' || $action == 'image') {
            $event->stopPropagation();
            return true;
        }

        return false;
    }
    
    public function validationDefault(Validator $validator)
    {
        $validator = parent::validationDefault($validator);
        $validator->requirePresence('upload');
        $validator->add('upload', 'custom', [
            'rule' => function ($value, $context) use (&$validator) {
                if ($value) {
                    $validator->requirePresence('file_content');
                } else {
                    $validator->allowEmpty('file_content');
                }
                
                return true;
            }
        ]);
        
        return $validator;
    }
    
    public function beforeSave(Event $event, Entity $entity)
    {
        if ($entity->submit && $entity->submit !== 'save') {
            $event->stopPropagation();
            return false;
        }
        
        if ($this->beforeSaveCondition) {
            $this->beforeSaveCondition = false;
            if ($entity->upload) {
                $entity->params = serialize($this->getParams(true));
            } else {
                $entity->params = serialize($this->getParams());
            }
        }
    }

    public function afterSaveCommit(Event $event, Entity $entity)
    {
        if ($entity->submit && $entity->submit !== 'save') {
            $event->stopPropagation();
            return false;
        }
        
        if ($this->afterSaveCommitCondition) {
            $this->afterSaveCommitCondition = false;
            if (!$entity->upload) {
                $entity = $this->runGeneratingReport($entity);
            }
            
            $entity->file_name_hash = null;
            $entity->file_content_hash = null;
            $entity->signed_by_id = null;
            $entity->is_signed = 0;
            $this->save($entity);
        }
    }

    public function runGeneratingReport(Entity $entity)
    {
        $cmd = ROOT . DS . 'bin' . DS . 'cake MonGenerateStatisticReport ' . $entity->id;
        $logs = ROOT . DS . 'logs' . DS . 'MonGeneratedStatisticReports.log & echo $!';
        $shellCmd = $cmd . ' >> ' . $logs;
        $pid = exec($shellCmd);

        $params = unserialize($this->get($entity->id)->params);
        $params['pid'] = is_int($pid) && $pid ? $pid : -1;
        $entity->params = serialize($params);
        
        return $entity;
    }
    
    public function getParams(bool $upload = false)
    {
        $timeZone = 'Asia/Bishkek';
        $dateTime = new \DateTime("NOW", new \DateTimeZone($timeZone));
        $user = isset($_SESSION['Auth']['User']) ? $_SESSION['Auth']['User'] : [];
        $params = [
            'pid' => 0,
            'status' => $upload ? 1 : 0,
            'user' => $user,
            'locale' => I18n::locale(),
            'time_zone' => $timeZone,
            'start_datetime' => $dateTime->format('Y-m-d H:i:s'),
            'end_datetime' => $dateTime->format('Y-m-d H:i:s')
        ];
        
        return $params;
    }
}
