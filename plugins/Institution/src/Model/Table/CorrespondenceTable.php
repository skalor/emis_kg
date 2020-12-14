<?php
namespace Institution\Model\Table;

use ArrayObject;
use Cake\I18n\Time;
use Cake\ORM\Query;
use App\Model\Table\ControllerActionTable;
use Cake\ORM\TableRegistry;
use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\Validation\Validator;

class CorrespondenceTable extends ControllerActionTable
{
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->belongsTo('Institutions', ['className' => 'Institution.Institutions', 'foreignKey' => 'institution_id']);
        $this->belongsTo('AcademicPeriods', ['className' => 'AcademicPeriod.AcademicPeriods']);

        $this->belongsTo('DocumentStatus', ['className' => 'Institution.DocumentStatus', 'foreignKey' => 'document_status_id']);
        $this->belongsTo('DocumentTypes', ['className' => 'Institution.DocumentTypes', 'foreignKey' => 'document_type_id']);
        $this->belongsTo('Importance', ['className' => 'Institution.Importance', 'foreignKey' => 'importance_id']);
        $this->belongsTo('Circulating', ['className' => 'Institution.Circulating', 'foreignKey' => 'circulating_id']);
        $this->belongsTo('Organization', ['className' => 'Institution.Institutions', 'foreignKey' => 'organization_id']);

        $this->addBehavior('OpenEmis.Section');
    }

    public function indexBeforeAction(Event $event, ArrayObject $extra){

        $extra['elements']['controls'] = ['name' => 'Institution.Budget/controls', 'data' => [], 'options' => [], 'order' => 0];
    }
    public function beforeAction(Event $event, ArrayObject $extra)
    {
        $this->field('academic_period_id', ['type' => 'select']);
        $this->field('basic_data', ['type' => 'section','view'=>['edit'=>true,'add'=>true,'view'=>true,'index'=>false], 'title' => __('Basic data')]);
        $this->field('circulating_id', ['type' => 'select' ]);
        $this->field('importance_id', ['type' => 'select' ]);
        $this->field('organization_id', [
            'type' => 'select',
            'attr' => [
                'class' => 'selectpicker',
                'data-size' => '15',
                'data-dropup-auto' => 'false',
                'data-live-search' => 'true'
            ]
        ]);
        $this->field('document_status_id', ['type' => 'select' ]);
        $this->field('document_type_id', ['type' => 'select', 'after'=>'incoming_document_number']);
    }

    public function addAfterAction(Event $event, Entity $entity, ArrayObject $extra){

        $academicPeriodOptions = $this->AcademicPeriods->getYearList(['isEditable'=>true]);
        $this->fields['academic_period_id']['options'] = $academicPeriodOptions;
        $this->fields['academic_period_id']['default'] = $this->AcademicPeriods->getCurrent();

    }


    public function indexBeforeQuery(Event $event, Query $query, ArrayObject $extra)
    {

        $periodOptions = $this->AcademicPeriods->getYearList(['withLevels' => true, 'isEditable' => true]);

        $selectedPeriod = !is_null($this->request->query('academic_period_id')) ? $this->request->query('academic_period_id') : $this->AcademicPeriods->getCurrent();

        $this->controller->set(compact('periodOptions', 'selectedPeriod'));

        $where[$this->aliasField('academic_period_id')] = $selectedPeriod;

        $query->where($where);

    }




}