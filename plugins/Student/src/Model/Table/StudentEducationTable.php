<?php
namespace Student\Model\Table;

use ArrayObject;
use Cake\Event\Event;
use Cake\Network\Request;
use Cake\ORM\Query;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use App\Model\Table\ControllerActionTable;
use Cake\Validation\Validator;
use App\Model\Traits\OptionsTrait;

class StudentEducationTable extends ControllerActionTable
{

    use OptionsTrait;
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->belongsTo('Students', ['className' => 'User.Users', 'foreignKey' => 'student_id']);
        $this->belongsTo('AcademicPeriods', ['className' => 'AcademicPeriod.AcademicPeriods']);
    }

    public function addAfterAction(Event $event, Entity $entity, ArrayObject $extra){
        $academicPeriodOptions = $this->AcademicPeriods->getYearList(['isEditable'=>true]);
        $this->fields['academic_period_id']['options'] = $academicPeriodOptions;
        $this->fields['academic_period_id']['default'] = $this->AcademicPeriods->getCurrent();
    }

    public function beforeAction(){
        $this->fields['academic_period_id']['type'] = 'select';
        $this->fields['academic_period_id']['order'] = 1;
        $this->field('altyn_tamga',['type' => 'section', 'order' => 2]);
        $this->field('golden_sign', ['type' => 'select','onChangeReload' => true, 'options' => $this->getSelectOptions('general.yesno') ]);
        $this->field('golden_sign_confirm', ['type' => 'select', 'options' => $this->getSelectOptions('general.yesno'), 'visible' => false]);
    }

    public function onUpdateFieldGoldenSignConfirm(Event $event, array $attr, $action, Request $request)
    {
        if ($request->is(['post', 'put'])) {
            if (array_key_exists($this->alias(), $request->data)) {
                if (array_key_exists('golden_sign', $request->data[$this->alias()])) {
                    if ($request->data[$this->alias()]['golden_sign'] == 1) {
                        $attr['visible'] = true;
                        $attr['default'] = 0;
                    } else {
                        $attr['value'] = null;
                        $attr['visible'] = false;
                    }
                }
            }
        }
        return $attr;
    }
    public function viewAfterAction(Event $event, Entity $entity, ArrayObject $extra)
    {
        if ($entity['golden_sign'] == 1) $this->field('golden_sign_confirm', ['visible' => true,'after'=>'golden_sign']);
    }
    public function addEditAfterAction(Event $event, Entity $entity, ArrayObject $extra)
    {
        if ($entity['golden_sign'] == 1) $this->field('golden_sign_confirm', ['visible' => true,'after'=>'golden_sign']);
    }

    public function afterAction(Event $event, ArrayObject $extra)
    {
        $this->setupTabElements();
    }

    private function setupTabElements()
    {
        $options['type'] = 'student';
        $tabElements = $this->controller->getAcademicTabElements($options);
        $this->controller->set('tabElements', $tabElements);
        $this->controller->set('selectedAction', $this->alias());
    }

    public function indexBeforeAction(Event $event, ArrayObject $extra){
        // element control
        $academicPeriodOptions = $this->AcademicPeriods->getYearList();
        $requestQuery = $this->request->query;

        $selectedAcademicPeriodId = !empty($requestQuery) && array_key_exists('academic_period_id', $requestQuery) ? $requestQuery['academic_period_id'] : $this->AcademicPeriods->getCurrent();

        $extra['selectedAcademicPeriodId'] = $selectedAcademicPeriodId;

        $extra['elements']['control'] = [
            'name' => 'StudentEducation/controls',
            'data' => [
                'academicPeriodOptions'=>$academicPeriodOptions,
                'selectedAcademicPeriod'=>$selectedAcademicPeriodId
            ],
            'options' => [],
            'order' => 3
        ];
        // end element control
    }

    public function indexBeforeQuery(Event $event, Query $query, ArrayObject $extra)
    {
        $session = $this->request->session();
        $studentId = $session->read('Student.Students.id');

        $query = $query
            ->where([
                $this->aliasField('student_id') => $studentId,
                $this->aliasField('academic_period_id') => $extra['selectedAcademicPeriodId']
            ])
        ;

        return $query;
    }
}
