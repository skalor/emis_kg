<?php
namespace MonFields\Model\Table;

use App\Model\Table\AppTable;
use ArrayObject;
use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;

class MonFieldsTable extends AppTable
{
    protected $fieldTypes;
    protected $boolOptions;
    protected $fieldTypeOptions;

    public function initialize(array $config)
    {
        $this->table('mon_fields');
        parent::initialize($config);

        $this->boolOptions = ['Yes', 'No'];
        $this->fieldTypes = [
            'number' => 'int',
            'decimal' => 'double',
            'string' => 'varchar',
            'text' => 'text',
            'date' => 'date',
            'time' => 'time',
            'file' => 'mediumblob',
            'dropdown' => 'varchar',
            'relation' => 'varchar'
        ];
        $this->fieldTypeOptions = [
            'number' => 'Number',
            'decimal' => 'Decimal',
            'string' => 'String',
            'text' => 'Text',
            'date' => 'Date',
            'time' => 'Time',
            'file' => 'File',
            'dropdown' => 'Dropdown',
            'relation' => 'Relation'
        ];
    }

    public function validationDefault(Validator $validator)
    {
        $validator = parent::validationDefault($validator);
        $validator->notEmpty('length');
        $validator->notEmpty('field_type');
        $validator->notEmpty('relation_model');
        $validator->notEmpty('length_validation');
        $validator->notEmpty('validation_format');
        $validator->notEmpty('start_date');
        $validator->notEmpty('end_date');
        $validator->notEmpty('start_time');
        $validator->notEmpty('end_time');
        $validator->add('minimum_length', 'custom', [
            'rule' => function ($value, $context) {
                if ($value > 255 || $value < 1) {
                    return false;
                }

                return true;
            },
            'message' => __('The minimum length is not valid. Must be in 1 - 255')
        ]);
        $validator->add('maximum_length', 'custom', [
            'rule' => function ($value, $context) {
                if ($value > 255 || $value < 1) {
                    return false;
                }

                return true;
            },
            'message' => __('The maximum length is not valid. Must be in 1 - 255')
        ]);
        $validator->add('minimum_value', 'custom', [
            'rule' => function ($value, $context) {
                if ($value > 2147483647 || $value < 0) {
                    return false;
                }

                return true;
            },
            'message' => __('The minimum value is not valid. Must be in 0 - 2147483647')
        ]);
        $validator->add('maximum_value', 'custom', [
            'rule' => function ($value, $context) {
                if ($value > 2147483647 || $value < 1) {
                    return false;
                }

                return true;
            },
            'message' => __('The maximum value is not valid. Must be in 1 - 2147483647')
        ]);
        $validator->add('decimal_length', 'custom', [
            'rule' => function ($value, $context) {
                if ($value > 9 || $value < 1) {
                    return false;
                }

                return true;
            },
            'message' => __('The decimal length is not valid. Must be in 1 - 10')
        ]);
        $validator->add('decimal_place', 'custom', [
            'rule' => function ($value, $context) {
                if ($value > 4 || $value < 1) {
                    return false;
                }

                return true;
            },
            'message' => __('The decimal place is not valid. Must be in 1 - 5')
        ]);
        $validator->add('name', 'custom', [
            'rule' => function ($value, $context) {
                $matches = [];
                preg_match("/[A-ZА-Яа-я\s\W]/", $value, $matches);

                if ($value[0] === '_' || $value[strlen($value)-1] === '_' || $matches) {
                    return false;
                }

                if ($context['data']['model'] && isset($this->controller->MonFields)) {
                    $tableColumns = $this->controller->MonFields->getTableColumns($context['data']['model']);
                    if ($tableColumns && in_array($value, $tableColumns)) {
                        return __('This column name already exists in the table!');
                    }
                }

                return true;
            },
            'message' => __('The name is not valid. Must be like: `column_name`')
        ]);

        return $validator;
    }

    public function beforeSave(Event $event, Entity $entity, ArrayObject $options)
    {
        if (!isset($this->controller->MonFields) || $entity->submit !== 'save') {
            $event->stopPropagation();
            $event->result = false;
            return;
        }

        $table = TableRegistry::get($entity->model);
        $action = $this->controller->request->action;
        $monFieldsComponent = $this->controller->MonFields;
        $columnExist = in_array($entity->name, $table->schema()->columns());

        if (
            !$table && !$action && !$monFieldsComponent &&
            !$entity->name && !$entity->length && !$entity->is_mandatory && !$entity->field_type ||
            $columnExist && $action !== 'edit'
        ) {
            $event->stopPropagation();
            $event->result = false;
            return;
        }

        $this->setParams($entity);

        $fieldType = $this->fieldTypes[$entity->field_type];
        if ($fieldType) {
            $length = $entity->length;
            $afterLength = null;

            if ($fieldType === 'int' && (!$length || $length > 11 || $length < 0)) {
                $length = 11;
            } else if ($fieldType === 'double' && (!$afterLength || !$length || $length > 14 || $length < 0)) {
                $length = 14; $afterLength = 4;
            } else if ($fieldType === 'varchar' && (!$length || $length > 255 || $length < 0)) {
                $length = 255;
            } else if ($fieldType === 'text' && ($length > 65535 || $length < 0)) {
                $length = 65535;
            }

            $monFieldsComponent->columnQuery($action, $table->_table, $entity->name, $fieldType, $entity->is_mandatory, $entity->after_field, $length, $afterLength);
        }
    }

    public function afterSave(Event $event, Entity $entity, ArrayObject $options)
    {
        $dropdown = TableRegistry::get('MonFields.MonDropdowns');

        if ($dropdown->getDropdown($entity->id, true)) {
            $dropdown->deleteDropdown($entity->id);
        }

        if ($entity->id && $entity->field_type === 'dropdown') {
            $dropdown->addDropdown($entity->id, null, $entity->toArray());
        }

        if ($entity->id && $entity->field_type === 'relation') {
            $dropdown->addDropdown($entity->id, $entity->relation_model);
        }
    }

    public function beforeDelete(Event $event, Entity $entity, ArrayObject $options)
    {
        if (!isset($this->controller->MonFields)) {
            $event->stopPropagation();
            $event->result = false;
            return;
        }

        $table = TableRegistry::get($entity->model);
        $action = $this->controller->request->action;
        $monFieldsComponent = $this->controller->MonFields;

        if ($table && $action && $monFieldsComponent && $entity->name) {
            $monFieldsComponent->columnQuery($action, $table->_table, $entity->name, null, null, null, null);
        } else {
            $event->stopPropagation();
            $event->result = false;
            return;
        }
    }

    public function afterDelete(Event $event, Entity $entity, ArrayObject $options)
    {
        if (isset($this->controller->MonFields)) {
            $this->controller->MonFields->cacheClear();
        }

        return TableRegistry::get('MonFields.MonDropdowns')->deleteDropdown($entity->id);
    }

    public function getFields(string $model, string $type = null)
    {
        $where = ['model' => $model];
        if ($type) {
            $where['field_type'] = $type;
        }

        return $this->find()->where($where)->all()->toArray();
    }

    public function getBoolOptions()
    {
        foreach ($this->boolOptions as $key => $option) {
            $this->boolOptions[$key] = __($option);
        }

        return $this->boolOptions;
    }

    public function getFieldTypeOptions()
    {
        foreach ($this->fieldTypeOptions as $key => $option) {
            $this->fieldTypeOptions[$key] = __($option);
        }

        return $this->fieldTypeOptions;
    }

    private function setParams(Entity $entity)
    {
        if ($entity->field_type === 'number') {
            $this->setNumberParams($entity);
        } else if ($entity->field_type === 'decimal') {
            $this->setDecimalParams($entity);
        } else if ($entity->field_type === 'string') {
            $this->setStringParams($entity);
        } else if ($entity->field_type === 'date') {
            $this->setDateParams($entity);
        } else if ($entity->field_type === 'time') {
            $this->setTimeParams($entity);
        } else if ($entity->field_type === 'dropdown' || $entity->field_type === 'relation') {
            $this->setDropdownRelationParams($entity);
        }
    }

    private function setNumberParams(Entity $entity)
    {
        $params = [];

        if ($entity->minimum_value) {
            $params['range']['min_value'] = $entity->minimum_value;
        }

        if ($entity->maximum_value) {
            $params['range']['max_value'] = $entity->maximum_value;
        }

        $entity->params = serialize($params);

        return $entity;
    }

    private function setDecimalParams(Entity $entity)
    {
        $params = [];

        if ($entity->decimal_length) {
            $params['decimal_length'] = $entity->decimal_length;
        }

        if ($entity->decimal_place) {
            $params['decimal_place'] = $entity->decimal_place;
        }

        $entity->params = serialize($params);

        return $entity;
    }

    private function setStringParams(Entity $entity)
    {
        $params = [];

        if ($entity->minimum_length) {
            $params['range']['min_length'] = $entity->minimum_length;
        }

        if ($entity->maximum_length) {
            $params['range']['max_length'] = $entity->maximum_length;
        }

        if ($entity->string_validation_rules === 'url') {
            $params['url'] = 'url';
        }

        if ($entity->validation_format) {
            $params['validation_format'] = $entity->validation_format;
        }

        $entity->params = serialize($params);

        return $entity;
    }

    private function setDateParams(Entity $entity)
    {
        $params = [];

        if ($entity->start_date) {
            $params['range']['start_date'] = $entity->start_date;
        }

        if ($entity->end_date) {
            $params['range']['end_date'] = $entity->end_date;
        }

        $entity->params = serialize($params);

        return $entity;
    }

    private function setTimeParams(Entity $entity)
    {
        $params = [];

        if ($entity->start_time) {
            $params['range']['start_time'] = $entity->start_time;
        }

        if ($entity->end_time) {
            $params['range']['end_time'] = $entity->end_time;
        }

        $entity->params = serialize($params);

        return $entity;
    }

    private function setDropdownRelationParams(Entity $entity)
    {
        $params = [];

        if (isset($entity->is_multiple)) {
            $params['is_multiple'] = $entity->is_multiple;
        }

        $entity->params = serialize($params);

        return $entity;
    }
}
