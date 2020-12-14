<?php
namespace Institution\Model\Table;

use ArrayObject;
use App\Model\Table\ControllerActionTable;
use Cake\Event\Event;
use Cake\Network\Request;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\Validation\Validator;
use App\Model\Traits\OptionsTrait;
use function foo\func;

class MonLicensingTable extends ControllerActionTable
{
    use OptionsTrait;
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->belongsTo('Institutions', ['className' => 'Institution.Institutions']);
        $this->belongsTo('AcademicPeriods', ['className' => 'AcademicPeriod.AcademicPeriods']);
        $this->belongsTo('MonLicensingIssuingAuthority', ['className' => 'Institution.MonLicensingIssuingAuthority', 'foreignKey' => 'issuing_authority_id']);
        $this->belongsTo('MonLicensingType', ['className' => 'Institution.MonLicensingType', 'foreignKey' => 'type_id']);

        $this->addBehavior('ControllerAction.FileUpload', [
            'size' => '2MB',
            'contentEditable' => true,
            'allowable_file_types' => 'all',
            'useDefaultName' => true
        ]);

        if ($this->behaviors()->has('ControllerAction')) {
            $this->behaviors()->get('ControllerAction')->config([
                'actions' => [
                    'download' => ['show' => true] // to show download on toolbar
                ]
            ]);
        }
    }

    public function validationDefault(Validator $validator)
    {
        $validator = parent::validationDefault($validator);

        return $validator
            ->allowEmpty('file_content')
            ->allowEmpty('term_date', function ($context) {
                return($context['data']['indefinite'] == 1) ? true : false;
            });
    }

    public function indexBeforeAction(Event $event)
    {
        $this->setFields();
        $this->field('file_name', ['visible' => true]);
        $this->field('file_content', ['visible' => false]);
    }

    public function viewBeforeAction()
    {
        $this->setFields();
        $this->field('file_name', ['visible' => false]);
    }
    public function addBeforeAction(Event $event, ArrayObject $extra)
    {
        $this->field('term_date', ['attr' =>['label_class'=>'required'] ,'after'=>'issue_date']);
    }
    public function addEditAfterAction(Event $event, Entity $entity)
    {
        $this->setFields();
        $this->field('file_name', ['visible' => false]);
    }

    private function setFields()
    {
        $academicPeriodOptions = $this->AcademicPeriods->getYearList(['isEditable'=>true]);
        $this->field('issuing_authority_id', ['type' => 'select']);
        $this->field('term_date', ['attr'=>[ 'required' => true],'after'=>'issue_date']);
        $this->field('indefinite', [
            'type' => 'select',
            'options' => $this->getSelectOptions('general.yesno'),
            'after' => 'number']);
        $this->field('type_id', ['type' => 'select']);
        $this->field('academic_period_id', [
            'type' => 'select',
            'options' => $academicPeriodOptions,
            'attr' => ['value' => $this->AcademicPeriods->getCurrent()]
        ]);
    }
    /******************************************************************************************************************
     **
     ** essential methods
     **
     ******************************************************************************************************************/
    public function onUpdateFieldIndefinite(Event $event, array $attr, $action, Request $request)
    {
        if ( $action == 'add' || $action == 'edit' ) {
            if (array_key_exists($this->alias(), $request->data)) {
                if (array_key_exists('indefinite', $request->data[$this->alias()])) {
                    if ($request->data[$this->alias()]['indefinite'] == 1)
                        $this->field('term_date',['visible'=>false]);
                }
            }
        }
        $attr['onChangeReload'] = 'changeIndefinite';
        return $attr;
    }
}
