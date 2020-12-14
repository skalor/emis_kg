<?php
namespace App\Model\Table;

use ArrayObject;
use Cake\ORM\Table;
use Cake\ORM\Query;
use Cake\I18n\Time;
use Cake\I18n\Date;
use Cake\ORM\Entity;
use Cake\Event\Event;
use Cake\Log\LogTrait;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Cake\Validation\Validator;
use ControllerAction\Model\Traits\UtilityTrait;
use ControllerAction\Model\Traits\ControllerActionTrait;
use Page\Traits\OptionListTrait;

class AppTable extends Table
{
    use ControllerActionTrait;
    use UtilityTrait;
    use LogTrait;
    use OptionListTrait;
    const OpenEMIS = 'OpenEMIS ID';
    public function initialize(array $config)
    {
        Time::$defaultLocale = 'en_US';
        Date::$defaultLocale = 'en_US';

        $_config = [
            'Modified' => true,
            'Created' => true
        ];
        $_config = array_merge($_config, $config);
        parent::initialize($config);

        $schema = $this->schema();
        $columns = $schema->columns();

        if (in_array('modified', $columns) || in_array('created', $columns)) {
            $this->addBehavior('Timestamp', [
                'events' => [
                    'Model.beforeSave' => [
                        'created' => 'new',
                        'modified' => 'existing'
                    ]
                ]
            ]);
        }

        if (in_array('modified_user_id', $columns) && $_config['Modified']) {
            $this->belongsTo('ModifiedUser', ['className' => 'User.Users', 'foreignKey' => 'modified_user_id']);
        }

        if (in_array('created_user_id', $columns) && $_config['Created']) {
            $this->belongsTo('CreatedUser', ['className' => 'User.Users', 'foreignKey' => 'created_user_id']);
        }

        if (in_array('visible', $columns)) {
            $this->addBehavior('Visible');
        }

        if (in_array('order', $columns)) {
            $this->addBehavior('Reorder');
        }

        $dateFields = [];
        $timeFields = [];
        foreach ($columns as $column) {
            if ($schema->columnType($column) == 'date') {
                $dateFields[] = $column;
            } elseif ($schema->columnType($column) == 'time') {
                $timeFields[] = $column;
            }
        }
        if (!empty($dateFields)) {
            $this->addBehavior('ControllerAction.DatePicker', $dateFields);
        }
        if (!empty($timeFields)) {
            $this->addBehavior('ControllerAction.TimePicker', $timeFields);
        }
        $this->addBehavior('Validation');
        $this->addBehavior('Modification');

        $this->addBehavior('TrackAdd');
        $this->addBehavior('TrackDelete');
        $this->addBehavior('ControllerAction.Security');

        $this->_controllerActionEvents['Restful.Model.onRenderDatetime'] = 'onRestfulRenderDatetime';
        $this->_controllerActionEvents['Restful.Model.onRenderDate'] = 'onRestfulRenderDate';
        $this->_controllerActionEvents['Restful.Model.onRenderTime'] = 'onRestfulRenderTime';
    }

    public function validationDefault(Validator $validator)
    {
        $schema = $this->schema();
        $columns = $schema->columns();

        foreach ($columns as $column) {
            if ($schema->columnType($column) == 'date') {
                $attr = $schema->column($column);
                // check if is nullable
                if (array_key_exists('null', $attr) && $attr['null'] === true) {
                    $validator->allowEmpty($column);
                }
            }
        }

        return $validator;
    }

    // Function to get the entity property from the entity. If data validation occur,
    // the invalid value has to be extracted from invalid array
    // For use in Cake 3.2 and above
    public function getEntityProperty($entity, $propertyName)
    {
        if ($entity->has($propertyName)) {
            return $entity->get($propertyName);
        } elseif (array_key_exists($propertyName, $entity->invalid())) {
            return $entity->invalid($propertyName);
        } else {
            return null;
        }
    }

    // Event: 'ControllerAction.Model.onPopulateSelectOptions'
    public function onPopulateSelectOptions(Event $event, Query $query)
    {
        return $this->getList($query);
    }

    public function getList($query = null)
    {
        $schema = $this->schema();
        $columns = $schema->columns();
        $table = $schema->name();

        if (is_null($query)) {
            if ($table == 'area_levels') {
                $query = $this
                    ->find('list', [
                        'keyField' => 'level',
                        'valueField' => 'name'
                    ]);
            } else {
                $query = $this->find('list');
            }
        }

        if (in_array('order', $columns)) {
            $query->find('order');
        }

        if (in_array('visible', $columns)) {
            $query->find('visible');
        }

        return $query;
    }

    // Event: 'Model.excel.onFormatDate' ExcelBehavior
    public function onExcelRenderDate(Event $event, Entity $entity, $attr)
    {
        $field = $entity->{$attr['field']};
        if (!empty($field)) {
            if ($field instanceof Time || $field instanceof Date) {
                return $this->formatDate($field);
            } else {
                if ($field != '0000-00-00') {
                    $date = new Date($field);
                    return $this->formatDate($date);
                } else {
                    return '';
                }
            }
        } else {
            return $field;
        }
    }

    public function onExcelRenderDateTime(Event $event, Entity $entity, $attr)
    {
        $field = $entity->{$attr['field']};
        if (!empty($field)) {
            if ($field instanceof Time || $field instanceof Date) {
                return $this->formatDate($field);
            } else {
                $date = new Time($field);
                return $this->formatDate($date);
            }
        } else {
            return $field;
        }
    }

    // Event: 'ControllerAction.Model.onFormatDate'
    public function onFormatDate(Event $event, $dateObject)
    {
        return $this->formatDate($dateObject);
    }

    /**
     * For calling from view files
     * @param  Time   $dateObject [description]
     * @return [type]             [description]
     */
    public function formatDate($dateObject)
    {
        $ConfigItem = TableRegistry::get('Configuration.ConfigItems');
        $format = $ConfigItem->value('date_format');
        $value = '';
        if (is_object($dateObject)) {
            $value = $dateObject->format($format);
        }
        return $value;
    }

    // Event: 'ControllerAction.Model.onFormatTime'
    public function onFormatTime(Event $event, $timeObject)
    {
        return $this->formatTime($timeObject);
    }

    /**
     * For calling from view files
     * @param  Time   $dateObject [description]
     * @return [type]             [description]
     */
    public function formatTime($timeObject)
    {
        $ConfigItem = TableRegistry::get('Configuration.ConfigItems');
        $format = $ConfigItem->value('time_format');
        $value = '';
        if (is_object($timeObject)) {
            $value = $timeObject->format($format);
        }
        return $value;
    }

    // Event: 'ControllerAction.Model.onFormatDateTime'
    public function onFormatDateTime(Event $event, $timeObject)
    {
        return $this->formatDateTime($timeObject);
    }

    /**
     * For calling from view files
     * @param  Time   $dateObject [description]
     * @return [type]             [description]
     */
    public function formatDateTime($dateObject)
    {
        $ConfigItem = TableRegistry::get('Configuration.ConfigItems');
        $format = $ConfigItem->value('date_format') . ' - ' . $ConfigItem->value('time_format');
        $value = '';
        if (is_object($dateObject)) {
            $value = $dateObject->format($format);
        }
        return $value;
    }

    // Not using $extra parameter to be backward compatible with restfulv1
    public function onRestfulRenderDatetime(Event $event, $entity, $property)
    {
        $dateTimeObj = $entity[$property];
        return $this->formatDateTime($dateTimeObj);
    }

    // Not using $extra parameter to be backward compatible with restfulv1
    public function onRestfulRenderDate(Event $event, $entity, $property)
    {
        $dateTimeObj = $entity[$property];
        return $this->formatDate($dateTimeObj);
    }

    // Not using $extra parameter to be backward compatible with restfulv1
    public function onRestfulRenderTime(Event $event, $entity, $property)
    {
        $dateTimeObj = $entity[$property];
        return $this->formatTime($dateTimeObj);
    }

    // Event: 'ControllerAction.Model.onGetFieldLabel'
    public function onGetFieldLabel(Event $event, $module, $field, $language, $autoHumanize = true)
    {
        $Labels     = TableRegistry::get('Labels');
        $fieldLabel = $Labels->find()
                ->select(['name'])
                ->where(['module' => $event->data['module'],'field'=>'openemis_no'])
                ->first();

        if ($field == 'openemis_no' && !empty($fieldLabel['name'])) {
             return $fieldLabel['name'];

        } else if ($field == 'openemis_no') {
		    return self::OpenEMIS;
		}

        return $this->getFieldLabel($module, $field, $language, $autoHumanize);
    }

    public function getFieldLabel($module, $field, $language, $autoHumanize = true)
    {
        $Labels = TableRegistry::get('Labels');
        $label = $Labels->getLabel($module, $field, $language);

        if ($label === false && $autoHumanize) {
            $label = Inflector::humanize($field);
            if ($this->endsWith($field, '_id') && $this->endsWith($label, ' Id')) {
                $label = str_replace(' Id', '', $label);
            }
            $label = __($label);
        }

        if (substr($label, -1) == ')') {
            $label = $label.' ';
        }

        return $label;
    }

    // Event: 'Model.excel.onExcelGetLabel'
    public function onExcelGetLabel(Event $event, $module, $col, $language)
    {
       return __($this->getFieldLabel($module, $col, $language));
    }

    public function getButtonAttr()
    {
        return [
            'class' => 'btn btn-xs btn-default',
            'data-toggle' => 'tooltip',
            'data-placement' => 'bottom',
            'escape' => false
        ];
    }

    // Event: 'ControllerAction.Model.onInitializeButtons'
    public function onInitializeButtons(Event $event, ArrayObject $buttons, $action, $isFromModel, ArrayObject $extra)
    {
        // needs clean up
        $controller = $event->subject()->_registry->getController();
        $access = $controller->AccessControl;

        $toolbarButtons = new ArrayObject([]);
        $indexButtons = new ArrayObject([]);

        $toolbarAttr = $this->getButtonAttr();
        $indexAttr = ['role' => 'menuitem', 'tabindex' => '-1', 'escape' => false];

        // Set for roles belonging to the controller
        $roles = [];
        $event = $controller->dispatchEvent('Controller.Buttons.onUpdateRoles', null, $this);
        if ($event->result) {
            $roles = $event->result;
        }
        if ($action != 'index') {
            $toolbarButtons['back'] = $buttons['back'];
            $toolbarButtons['back']['type'] = 'button';
            $toolbarButtons['back']['label'] = '<svg class="backSvg" width="23" height="24" viewBox="0 0 23 24" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M23 10.4999H5.50563L13.5412 2.11501L11.5 0L0 12L11.5 24L13.5268 21.8849L5.50563 13.4999H23V10.4999Z" fill="#004A51"/> </svg>';
            $toolbarButtons['back']['attr'] = $toolbarAttr;
            $toolbarButtons['back']['attr']['title'] = __('Back');
            if ($action == 'remove' && ($buttons['remove']['strategy'] == 'transfer' || $buttons['remove']['strategy'] == 'restrict')) {
                $toolbarButtons['list'] = $buttons['index'];
                $toolbarButtons['list']['type'] = 'button';
                $toolbarButtons['list']['label'] = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"> <path fill-rule="evenodd" clip-rule="evenodd" d="M2.75 6C2.75 5.17 3.42 4.5 4.25 4.5C5.08 4.5 5.75 5.17 5.75 6C5.75 6.83 5.08 7.5 4.25 7.5C3.42 7.5 2.75 6.83 2.75 6ZM2.75 12C2.75 11.17 3.42 10.5 4.25 10.5C5.08 10.5 5.75 11.17 5.75 12C5.75 12.83 5.08 13.5 4.25 13.5C3.42 13.5 2.75 12.83 2.75 12ZM4.25 16.5C3.42 16.5 2.75 17.18 2.75 18C2.75 18.82 3.43 19.5 4.25 19.5C5.07 19.5 5.75 18.82 5.75 18C5.75 17.18 5.08 16.5 4.25 16.5ZM21.25 19H7.25V17H21.25V19ZM7.25 13H21.25V11H7.25V13ZM7.25 7V5H21.25V7H7.25Z" fill="#FF6C6C"/> </svg>';
                $toolbarButtons['list']['attr'] = $toolbarAttr;
                $toolbarButtons['list']['attr']['title'] = __('List');
            }
        }
        if ($action == 'index') {
            if ($buttons->offsetExists('add') && $access->check($buttons['add']['url'], $roles)) {
                $toolbarButtons['add'] = $buttons['add'];
                $toolbarButtons['add']['type'] = 'button';
                $toolbarButtons['add']['label'] = '<svg class="addSvg" width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M0 10C0 4.47715 4.47715 0 10 0H22C27.5228 0 32 4.47715 32 10V22C32 27.5228 27.5228 32 22 32H10C4.47715 32 0 27.5228 0 22V10Z" fill="#009966"></path> <path fill-rule="evenodd" clip-rule="evenodd" d="M17.1667 15.5H24.6667V17.1667H17.1667V24.6667H15.5V17.1667H8V15.5H15.5V8H17.1667V15.5Z" fill="white"></path> </svg>';
                $toolbarButtons['add']['attr'] = $toolbarAttr;
                $toolbarButtons['add']['attr']['title'] = __('Add');
            }
            if ($buttons->offsetExists('search')) {
                $toolbarButtons['search'] = [
                    'type' => 'element',
                    'element' => 'OpenEmis.search',
                    'data' => ['url' => $buttons['index']['url']],
                    'options' => []
                ];
            }
        } elseif ($action == 'add' || $action == 'edit') {
            if ($action == 'edit' && $buttons->offsetExists('index')) {
                $toolbarButtons['list'] = $buttons['index'];
                $toolbarButtons['list']['type'] = 'button';
                $toolbarButtons['list']['label'] = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"> <path fill-rule="evenodd" clip-rule="evenodd" d="M2.75 6C2.75 5.17 3.42 4.5 4.25 4.5C5.08 4.5 5.75 5.17 5.75 6C5.75 6.83 5.08 7.5 4.25 7.5C3.42 7.5 2.75 6.83 2.75 6ZM2.75 12C2.75 11.17 3.42 10.5 4.25 10.5C5.08 10.5 5.75 11.17 5.75 12C5.75 12.83 5.08 13.5 4.25 13.5C3.42 13.5 2.75 12.83 2.75 12ZM4.25 16.5C3.42 16.5 2.75 17.18 2.75 18C2.75 18.82 3.43 19.5 4.25 19.5C5.07 19.5 5.75 18.82 5.75 18C5.75 17.18 5.08 16.5 4.25 16.5ZM21.25 19H7.25V17H21.25V19ZM7.25 13H21.25V11H7.25V13ZM7.25 7V5H21.25V7H7.25Z" fill="#FF6C6C"/> </svg>';
                $toolbarButtons['list']['attr'] = $toolbarAttr;
                $toolbarButtons['list']['attr']['title'] = __('List');
            }
        } elseif ($action == 'view') {
            // edit button
            if ($buttons->offsetExists('edit') && $access->check($buttons['edit']['url'], $roles)) {
                $toolbarButtons['edit'] = $buttons['edit'];
                $toolbarButtons['edit']['type'] = 'button';
                $toolbarButtons['edit']['label'] = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0)"><path d="M2.4 16.5601L0 24.0001L7.44 21.6001L2.4 16.5601Z" fill="#009966"></path><path d="M15.7952 3.12597L4.08569 14.8354L9.17678 19.9265L20.8863 8.21706L15.7952 3.12597Z" fill="#009966"></path><path d="M23.64 3.72L20.28 0.36C19.8 -0.12 19.08 -0.12 18.6 0.36L17.52 1.44L22.56 6.48L23.64 5.4C24.12 4.92 24.12 4.2 23.64 3.72Z" fill="#009966"></path></g><defs><clipPath id="clip0"><rect width="24" height="24" fill="white"></rect></clipPath></defs></svg>';
                $toolbarButtons['edit']['attr'] = $toolbarAttr;
                $toolbarButtons['edit']['attr']['title'] = __('Edit');
            }

            // delete button
            // disabled for now until better solution
            if ($buttons->offsetExists('remove') && $buttons['remove']['strategy'] != 'transfer' && $access->check($buttons['remove']['url'], $roles)) {
                $toolbarButtons['remove'] = $buttons['remove'];
                $toolbarButtons['remove']['type'] = 'button';
                $toolbarButtons['remove']['label'] = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 21.3335C4 22.8069 5.19331 24.0002 6.66669 24.0002H17.3334C18.8067 24.0002 20 22.8069 20 21.3335V5.3335H4V21.3335Z" fill="#C71100"></path><path d="M16.6667 1.33331L15.3334 0H8.66675L7.33337 1.33331H2.66675V4H21.3334V1.33331H16.6667Z" fill="#C71100"></path></svg>';
                $toolbarButtons['remove']['attr'] = $toolbarAttr;
                $toolbarButtons['remove']['attr']['title'] = __('Delete');

                if ($buttons['remove']['strategy'] != 'restrict') {
                    $toolbarButtons['remove']['attr']['data-toggle'] = 'modal';
                    $toolbarButtons['remove']['attr']['data-target'] = '#delete-modal';
                    $toolbarButtons['remove']['attr']['field-target'] = '#recordId';
                    $toolbarButtons['remove']['attr']['onclick'] = 'ControllerAction.fieldMapping(this)';
                    if ($extra->offsetExists('primaryKeyValue')) {
                        $toolbarButtons['remove']['attr']['field-value'] = $extra['primaryKeyValue'];
                    }
                }
            }
        }

        if ($buttons->offsetExists('view') && $access->check($buttons['view']['url'], $roles)) {
            $indexButtons['view'] = $buttons['view'];
            $indexButtons['view']['label'] = '<i class="fa fa-eye"></i>' . __('View');
            $indexButtons['view']['attr'] = $indexAttr;
        }

        if ($buttons->offsetExists('edit') && $access->check($buttons['edit']['url'], $roles)) {
            $indexButtons['edit'] = $buttons['edit'];
            $indexButtons['edit']['label'] = '<i class="fa fa-pencil"></i>' . __('Edit');
            $indexButtons['edit']['attr'] = $indexAttr;
        }

        if ($buttons->offsetExists('remove') && $access->check($buttons['remove']['url'], $roles)) {
            $indexButtons['remove'] = $buttons['remove'];
            $indexButtons['remove']['label'] = '<i class="fa fa-trash df"></i>' . __('Delete');
            $indexButtons['remove']['attr'] = $indexAttr;
        }

        if ($buttons->offsetExists('reorder') && $buttons->offsetExists('edit') && $access->check($buttons['edit']['url'], $roles)) {
            // if ($buttons->offsetExists('reorder') && $access->check($buttons['edit']['url'])) {
            $controller->set('reorder', true);
        }

        $event = new Event('Model.custom.onUpdateToolbarButtons', $this, [$buttons, $toolbarButtons, $toolbarAttr, $action, $isFromModel]);
        $this->eventManager()->dispatch($event);

        if ($toolbarButtons->offsetExists('back')) {
            $controller->set('backButton', $toolbarButtons['back']);
        }
        $controller->set(compact('toolbarButtons', 'indexButtons'));
    }

    public function onUpdateActionButtons(Event $event, Entity $entity, array $buttons)
    {
        $id = $this->getEncodedKeys($entity);

        if (array_key_exists('view', $buttons)) {
            $buttons['view']['url'][] = $id;
        }
        if (array_key_exists('edit', $buttons)) {
            $buttons['edit']['url'][] = $id;
        }
        if (array_key_exists('remove', $buttons)) {
            if (in_array($buttons['remove']['strategy'], ['cascade'])) {
                $buttons['remove']['attr']['data-toggle'] = 'modal';
                $buttons['remove']['attr']['data-target'] = '#delete-modal';
                $buttons['remove']['attr']['field-target'] = '#recordId';
                $buttons['remove']['attr']['field-value'] = $id;
                $buttons['remove']['attr']['onclick'] = 'ControllerAction.fieldMapping(this)';
            } else {
                $buttons['remove']['url'][] = $id;
            }
        }
        return $buttons;
    }

    public function findVisible(Query $query, array $options)
    {
        return $query->where([$this->aliasField('visible') => 1]);
    }

    public function findActive(Query $query, array $options)
    {
        return $query->where([$this->aliasField('active') => 1]);
    }

    public function findOrder(Query $query, array $options)
    {
        return $query->order([$this->aliasField('order') => 'ASC']);
    }

    public function postString($key)
    {
        $request = $this->request;
        $selectedId = null;
        if ($request->data($this->aliasField($key))) {
            $selectedId = $request->data($this->aliasField($key));
        }
        return $selectedId;
    }

    public function isForeignKey($field, $table = null)
    {
        if (is_null($table)) {
            $table = $this;
        }
        foreach ($table->associations() as $assoc) {
            if ($assoc->type() == 'manyToOne') { // belongsTo associations
                if ($field === $assoc->foreignKey()) {
                    return true;
                }
            }
        }
        return false;
    }

    public function getAssociatedTable($field, $table = null)
    {
        if (is_null($table)) {
            $table = $this;
        }
        $relatedModel = null;

        foreach ($table->associations() as $assoc) {
            if ($assoc->type() == 'manyToOne') { // belongsTo associations
                if ($field === $assoc->foreignKey()) {
                    $relatedModel = $assoc;
                    break;
                }
            }
        }
        return $relatedModel;
    }

    public function getAssociatedKey($field, $table = null)
    {
        if (is_null($table)) {
            $table = $this;
        }
        $tableObj = $this->getAssociatedTable($field, $table);
        $key = null;
        if (is_object($tableObj)) {
            $key = Inflector::underscore(Inflector::singularize($tableObj->alias()));
        }
        return $key;
    }

    public function getEncodedKeys(Entity $entity)
    {
        $primaryKey = $this->primaryKey();
        $primaryKeyValue = [];
        if (is_array($primaryKey)) {
            foreach ($primaryKey as $key) {
                $primaryKeyValue[$key] = $entity->getOriginal($key);
            }
        } else {
            $primaryKeyValue[$primaryKey] = $entity->getOriginal($primaryKey);
        }

        $encodedKeys = $this->paramsEncode($primaryKeyValue);

        return $encodedKeys;
    }

    public function startsWith($haystack, $needle)
    {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
    }

    public function dispatchEventToModels($eventKey, $params, $subject, $listeners)
    {
        foreach ($listeners as $listener) {
            $listener->dispatchEvent($eventKey, $params, $subject);
        }
    }
}
