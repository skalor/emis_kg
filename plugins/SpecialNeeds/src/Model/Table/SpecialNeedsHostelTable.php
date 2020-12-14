<?php
namespace SpecialNeeds\Model\Table;

use ArrayObject;
use Cake\ORM\Query;
use App\Model\Table\ControllerActionTable;
use Cake\ORM\TableRegistry;
use Cake\Event\Event;
use Cake\ORM\Entity;

class SpecialNeedsHostelTable extends ControllerActionTable
{
    private $noYes = array('No','Yes');

    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->belongsTo('Users', ['className' => 'Security.Users', 'foreignKey' => 'security_user_id']);
        $this->belongsTo('AcademicPeriods', ['className' => 'AcademicPeriod.AcademicPeriods']);
        $this->addBehavior('SpecialNeeds.SpecialNeeds');
    }

    public function beforeAction($event) {
        $this->field('academic_period_id', ['type' => 'select']);
        $this->field('needs_a_hostel',['type' => 'select' , 'options' => $this->noYes]);
        $this->field('lives_in_a_hostel_at_the_place_of_study',['type' => 'select' , 'options' => $this->noYes]);
    }

    public function addAfterAction(Event $event, Entity $entity, ArrayObject $extra){
        $academicPeriodOptions = $this->AcademicPeriods->getYearList(['isEditable'=>true]);
        $this->fields['academic_period_id']['options'] = $academicPeriodOptions;
        $this->fields['academic_period_id']['default'] = $this->AcademicPeriods->getCurrent();
    }

    public function indexBeforeAction(Event $event, ArrayObject $extra)
    {
        $extra['elements']['controls'] = ['name' => 'SpecialNeeds.Hostel/controls', 'data' => [], 'options' => [], 'order' => 0];
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
