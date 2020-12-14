<?php
namespace MonFields\Controller;

use App\Controller\PageController;
use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Page\Model\Entity\PageElement;

class MonFieldsController extends PageController
{
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('MonFields.MonFields');
    }

    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        $action = $this->request->action;
        $page = $this->Page;

        $page->addCrumb('MonFields', ['plugin' => 'MonFields', 'controller' => 'MonFields', 'action' => 'index']);
        if ($action) {
            $page->addCrumb($action);
        }

        $page->setHeader(__('MonFields'));

        $page->get('params')->setControlType('hidden');
        $page->get('name')->setAttributes(['pattern' => '[a-z0-9_]']);
        $page->get('field_type')->setControlType('select')->setOptions($this->MonFieldsModel->getFieldTypeOptions())->setAttributes(['onchange' => '$(\'#reload\').click()']);
        $page->get('length')->setControlType('integer')->setAttributes(['min' => 1, 'max' => 2500]);
        $page->move('length')->after('field_type');
        $page->get('is_mandatory')->setControlType('select')->setOptions($this->MonFieldsModel->getBoolOptions(), false);

        if ($action === 'edit') {
            $page->get('name')->setDisabled(true);
        }
    }

    public function beforeRender(Event $event)
    {
        parent::beforeRender($event);

        $session = $this->request->session();

        if (in_array($this->request->data('submit'), ['reload', 'dropdownAdd', 'dropdownRemove']) && $session->check('alert')) {
            $session->delete('alert');
        }
    }

    public function implementedEvents()
    {
        $events = parent::implementedEvents();
        $events['Controller.Page.onRenderFieldType'] = 'onRenderFieldType';
        return $events;
    }

    public function onRenderFieldType(Event $event, Entity $entity, PageElement $element)
    {
        $action = $this->request->action;
        $page = $this->Page;
        if ($action === 'edit') {
            $page->get('field_type')->setDisabled(true);
        }

        if ($action === 'add' && !$entity->submit) {
            $page->get('is_mandatory')->setValue(1);
        }

        $length = ['required' => true, 'disabled' => false, 'value' => 0];
        switch ($entity->field_type) {
            case 'date':
            case 'time':
            case 'file':
                $length['disabled'] = true;
                $length['value'] = 0;
                break;
            case 'number':
            case 'dropdown':
            case 'relation':
                $length['disabled'] = true;
                $length['value'] = 11;
                break;
            case 'decimal':
                $length['disabled'] = true;
                $length['value'] = 14;
                break;
            case 'string':
                $length['value'] = $entity->length ? $entity->length : 250;
                break;
            case 'text':
                $length['value'] = $entity->length ? $entity->length : 0;
        }

        $entity->length = $length['value'];
        $page->get('length')->setDisabled($length['disabled'])->setRequired($length['required']);

        $this->addValidation($entity);
        $this->addDropdown($entity);
        $this->addRelation($entity);
    }

    public function addValidation(Entity $entity)
    {
        $action = $this->request->action;
        if (!in_array($action, ['add', 'edit'])) {
            return;
        }

        if ($entity->field_type === 'number') {
            $this->buildNumberValidation($entity);
        } else if ($entity->field_type === 'decimal') {
            $this->buildDecimalValidation($entity);
        } else if ($entity->field_type === 'string') {
            $this->buildStringValidation($entity);
        } else if ($entity->field_type === 'date') {
            $this->buildDateValidation($entity);
        } else if ($entity->field_type === 'time') {
            $this->buildTimeValidation($entity);
        }
    }

    private function buildNumberValidation(Entity $entity)
    {
        $page = $this->Page;
        $params = unserialize($entity->params);
        $validRules = $page->addNew('number_validation_rules')->setControlType('select')->setOptions([
            'min' => __('Should not be lesser than'),
            'max' => __('Should not be greater than'),
            'range' => __('In between (inclusive)')
        ])->setAttributes(['onchange' => '$(\'#reload\').click()']);

        if ($entity->submit === 'reload') {
            // nothing
        } else if (isset($params['range']['min_value']) && isset($params['range']['max_value'])) {
            $entity->number_validation_rules = 'range';
        } else if (isset($params['range']['min_value'])) {
            $entity->number_validation_rules = 'min';
        } else if (isset($params['range']['max_value'])) {
            $entity->number_validation_rules = 'max';
        }

        if ($entity->number_validation_rules === 'min' || $entity->number_validation_rules === 'range') {
            $page->addNew('minimum_value')->setControlType('integer')->setRequired(true);
            if (isset($params['range']['min_value']) && $params['range']['min_value']) {
                $entity->minimum_value = $params['range']['min_value'];
            }
        }

        if ($entity->number_validation_rules === 'max' || $entity->number_validation_rules === 'range') {
            $page->addNew('maximum_value')->setControlType('integer')->setRequired(true);
            if (isset($params['range']['max_value']) && $params['range']['max_value']) {
                $entity->maximum_value = $params['range']['max_value'];
            }
        }
    }

    private function buildDecimalValidation(Entity $entity)
    {
        $page = $this->Page;
        $params = unserialize($entity->params);

        $page->addNew('decimal_length')->setControlType('integer')->setRequired(true)->setAttributes([
            'min' => 1,
            'max' => 10
        ])->setLabel([
            'escape' => false,
            'class' => 'tooltip-desc',
            'text' => __('Decimal length') . $this->tooltipMessage('1 - 10')
        ]);

        if (isset($params['decimal_length']) && $params['decimal_length']) {
            $entity->decimal_length = $params['decimal_length'];
        }

        $page->addNew('decimal_place')->setControlType('integer')->setRequired(true)->setAttributes([
            'min' => 1,
            'max' => 5
        ])->setLabel([
            'escape' => false,
            'class' => 'tooltip-desc',
            'text' => __('Decimal place') . $this->tooltipMessage('1 - 5')
        ]);

        if (isset($params['decimal_place']) && $params['decimal_place']) {
            $entity->decimal_place = $params['decimal_place'];
        }
    }

    private function buildStringValidation(Entity $entity)
    {
        $page = $this->Page;
        $params = unserialize($entity->params);
        $validRules = $page->addNew('string_validation_rules')->setControlType('select')->setOptions([
            'length' => __('Length Validation'),
            'url' => __('URL Validation'),
            'input_mask' => __('Custom Validation')
        ])->setAttributes(['onchange' => '$(\'#reload\').click()']);

        if ($entity->submit === 'reload') {
            // nothing
        } else if (isset($params['range'])) {
            $entity->string_validation_rules = 'length';
        } else if (isset($params['url'])) {
            $entity->string_validation_rules = 'url';
        } else if (isset($params['validation_format'])) {
            $entity->string_validation_rules = 'input_mask';
        }

        if ($entity->string_validation_rules === 'length') {
            $page->addNew('length_validation')->setControlType('select')->setOptions([
                'min' => __('Should be at least'),
                'max' => __('Should not exceed'),
                'range' => __('Should be between')
            ])->setAttributes(['onchange' => '$(\'#reload\').click()'])->setRequired(true);

            if ($entity->submit === 'reload') {
                // nothing
            } else if (isset($params['range']['min_length']) && isset($params['range']['max_length'])) {
                $entity->length_validation = 'range';
            } else if (isset($params['range']['min_length'])) {
                $entity->length_validation = 'min';
            } else if (isset($params['range']['max_length'])) {
                $entity->length_validation = 'max';
            }

            if ($entity->length_validation === 'min' || $entity->length_validation === 'range') {
                $page->addNew('minimum_length')->setControlType('integer')->setRequired(true);
                if (isset($params['range']['min_length']) && $params['range']['min_length']) {
                    $entity->minimum_length = $params['range']['min_length'];
                }
            }

            if ($entity->length_validation === 'max' || $entity->length_validation === 'range') {
                $page->addNew('maximum_length')->setControlType('integer')->setRequired(true);
                if (isset($params['range']['max_length']) && $params['range']['max_length']) {
                    $entity->maximum_length = $params['range']['max_length'];
                }
            }
        } else if ($entity->string_validation_rules === 'input_mask') {
            $page->addNew('validation_format')
                ->setControlType('string')
                ->setAttributes(['placeholder' => 'Example Format : 9999aaaa => Example Input : 1234abcd'])
                ->setRequired(true)
                ->setLength(250);

            if (isset($params['validation_format']) && $params['validation_format']) {
                $entity->validation_format = $params['validation_format'];
            }
        }
    }

    private function buildDateValidation(Entity $entity)
    {
        $page = $this->Page;
        $params = unserialize($entity->params);
        $validRules = $page->addNew('date_validation_rules')->setControlType('select')->setOptions([
            'earlier' => __('Should not be earlier than'),
            'later' => __('Should not be later than'),
            'between' => __('In between (inclusive)')
        ])->setAttributes(['onchange' => '$(\'#reload\').click()']);

        if ($entity->submit === 'reload') {
            // nothing
        } else if (isset($params['range']['start_date']) && isset($params['range']['end_date'])) {
            $entity->date_validation_rules = 'between';
        } else if (isset($params['range']['start_date'])) {
            $entity->date_validation_rules = 'earlier';
        } else if (isset($params['range']['end_date'])) {
            $entity->date_validation_rules = 'later';
        }

        if ($entity->date_validation_rules === 'earlier' || $entity->date_validation_rules === 'between') {
            $page->addNew('start_date')->setControlType('date')->setRequired(true);
            if (isset($params['range']['start_date']) && $params['range']['start_date']) {
                $entity->start_date = $params['range']['start_date'];
            }
        }

        if ($entity->date_validation_rules === 'later' || $entity->date_validation_rules === 'between') {
            $page->addNew('end_date')->setControlType('date')->setRequired(true);
            if (isset($params['range']['end_date']) && $params['range']['end_date']) {
                $entity->end_date = $params['range']['end_date'];
            }
        }
    }

    private function buildTimeValidation(Entity $entity)
    {
        $page = $this->Page;
        $params = unserialize($entity->params);
        $validRules = $page->addNew('time_validation_rules')->setControlType('select')->setOptions([
            'earlier' => __('Should not be earlier than'),
            'later' => __('Should not be later than'),
            'between' => __('In between (inclusive)')
        ])->setAttributes(['onchange' => '$(\'#reload\').click()']);

        if ($entity->submit === 'reload') {
            // nothing
        } else if (isset($params['range']['start_time']) && isset($params['range']['end_time'])) {
            $entity->time_validation_rules = 'between';
        } else if (isset($params['range']['start_time'])) {
            $entity->time_validation_rules = 'earlier';
        } else if (isset($params['range']['end_time'])) {
            $entity->time_validation_rules = 'later';
        }

        if ($entity->time_validation_rules === 'earlier' || $entity->time_validation_rules === 'between') {
            $page->addNew('start_time')->setControlType('time')->setRequired(true);
            if (isset($params['range']['start_time']) && $params['range']['start_time']) {
                $entity->start_time = $params['range']['start_time'];
            }
        }

        if ($entity->time_validation_rules === 'later' || $entity->time_validation_rules === 'between') {
            $page->addNew('end_time')->setControlType('time')->setRequired(true);
            if (isset($params['range']['end_time']) && $params['range']['end_time']) {
                $entity->end_time = $params['range']['end_time'];
            }
        }
    }

    public function addDropdown(Entity $entity)
    {
        $page = $this->Page;
        $action = $this->request->action;
        $session = $this->request->session();

        if (
            !in_array($action, ['add', 'edit']) ||
            !in_array($entity->submit, ['dropdownAdd', 'dropdownRemove', 'reload']) &&
            $session->check($entity->primaryKey)
        ) {
            $session->write($entity->primaryKey, 2);
        }

        if (in_array($action, ['add', 'edit']) && $entity->field_type === 'dropdown') {
            $dropdowns = [];
            if (isset($entity->id)) {
                $dropdowns = TableRegistry::get('MonFields.MonDropdowns')->getDropdown($entity->id);
            }

            $page->addNew('is_multiple')->setControlType('select')->setOptions(['no', 'yes'], false);

            if ($entity->submit === 'dropdownAdd' || $dropdowns && $entity->submit !== 'dropdownRemove') {
                if (!$session->check($entity->primaryKey)) {
                    $count = $dropdowns ? count($dropdowns) : 2;
                    $session->write($entity->primaryKey, $count);
                }

                $dropdownCount = $session->read($entity->primaryKey);
                $dropdownCount++;
                $session->write($entity->primaryKey, $dropdownCount);
            } else if ($entity->submit === 'dropdownRemove' && $session->read($entity->primaryKey) > 2) {
                $dropdownCount = $session->read($entity->primaryKey);
                $dropdownCount--;
                $session->write($entity->primaryKey, $dropdownCount);
            }

            $count = $session->read($entity->primaryKey);
            $page->addNew('dropdown_elements')->setControlType('section');
            for ($i = 1; $i < $count; $i++) {
                $dropdown = $page->addNew('dropdown_option_' . $i)->setControlType('string')->setLength(250);
                if ($dropdown && $dropdowns) {
                    $dropdown->setValue($dropdowns[$i-1]);
                }
            }
        }
    }

    public function addRelation(Entity $entity)
    {
        $action = $this->request->action;
        if (in_array($action, ['add', 'edit']) && $entity->field_type === 'relation') {
            $fieldsOptions = $this->MonFields->buildFieldOptions();
            $page = $this->Page;
            $page->addNew('relation_elements')->setControlType('section');
            $relationElement = $page->addNew('relation_model')->setRequired(true)->setControlType('select')->setOptions($fieldsOptions)->setAttributes([
                'class' => 'selectpicker monfields-model',
                'data-size' => '15',
                'data-dropup-auto' => 'false',
                'data-live-search' => 'true'
            ]);;

            if ($entity->id) {
                $relation = TableRegistry::get('MonFields.MonDropdowns')->getDropdown($entity->id, true)->model;
                $relationElement->setValue($relation);
            }

            //$page->addNew('is_multiple')->setControlType('select')->setOptions(['no', 'yes'], false);

            //$this->MonFields->setPageModels('relation_model');
        }
    }

    // for info tooltip
    protected function tooltipMessage($message)
    {
        $tooltipMessage = '&nbsp&nbsp;<i class="fa fa-info-circle fa-lg table-tooltip icon-blue" data-placement="right" data-toggle="tooltip" data-animation="false" data-container="body" title="" data-html="true" data-original-title="' . $message . '"></i>';

        return $tooltipMessage;
    }

    public function index()
    {
        $this->Page->exclude(['params']);
        $this->Page->setPaginateOption('order', ['MonFields.created' => 'desc']);
        parent::index();
    }
}
