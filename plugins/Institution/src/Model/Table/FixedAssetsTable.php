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

class FixedAssetsTable extends ControllerActionTable
{
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->belongsTo('Institutions', ['className' => 'Institution.Institutions', 'foreignKey' => 'institution_id']);
        $this->belongsTo('AcademicPeriods', ['className' => 'AcademicPeriod.AcademicPeriods']);

        $this->belongsTo('FixedAssetsType', ['className' => 'Institution.FixedAssetsType', 'foreignKey' => 'fixed_assets_type_id']);

    }

    public function indexBeforeAction(Event $event, ArrayObject $extra){

        $extra['elements']['controls'] = ['name' => 'Institution.Budget/controls', 'data' => [], 'options' => [], 'order' => 0];
    }

    public function addEditAfterAction(Event $event, Entity $entity){

        $this->field('academic_period_id', ['type' => 'select']);
        $this->field('fixed_assets_type_id', ['type' => 'select' ]);

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