<?php

namespace MonFields\Model\Behavior;

use ArrayObject;
use Cake\ORM\Behavior;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\Event\Event;

class MonFieldsBehavior extends Behavior
{
    private $sections;
    private $fields;
    private $dropdowns;
    private $relations;

    public function implementedEvents()
    {
        $events = parent::implementedEvents();

        $newEvent = [
            'ControllerAction.Model.beforeAction' => 'beforeAction',
            'ControllerAction.Model.afterAction' => 'afterAction'
        ];

        return array_merge($events, $newEvent);
    }

    public function initialize(array $config)
    {
        parent::initialize($config);

        $fields = $this->getRelations();
        if ($fields) {
            foreach ($fields as $field) {
                if ($field->relation->model) {
                    $this->_table->belongsTo($field->relation->model, ['foreignKey' => $field->name]);
                }
            }
        }
    }

    public function beforeSave(Event $event, Entity $entity)
    {
        $alias = $this->_table->alias();
        $request = isset($this->_table->request) ? $this->_table->request : null;
        if ($request && isset($request->data[$alias])) {
            $data = $request->data;
            $fields = $this->getFields();
            if ($fields) {
                foreach ($fields as $field) {
                    $item = isset($data[$alias][$field->name]) ? $data[$alias][$field->name] : null;
                    if ($item && is_array($item) && isset($item['_ids'])) {
                        $serialized = serialize($item['_ids']);
                        $entity->{$field->name} = $serialized;
                        $request->data[$alias][$field->name] = $serialized;
                    }
                }
            }
        }
    }

    public function beforeAction(Event $event)
    {
        $table = $this->_table;

        $fields = $this->getDropdowns();
        if ($fields) {
            foreach ($fields as $field) {
                $type = 'select';
                $params = $field->params ? unserialize($field->params) : [];
                if (isset($params['is_multiple']) && $params['is_multiple']) {
                    $type = 'chosenSelect';
                }

                $table->field($field->name, [
                    'type' => $type,
                    'options' => $field->dropdown ? $this->translateDropdowns($field->dropdown) : []
                ]);
            }
        }

        $fields = $this->getRelations();
        if ($fields) {
            foreach ($fields as $field) {
                $table->field($field->name, [
                    'type' => 'select'
                ]);
            }
        }

        $sections = $this->getSections();
        if ($sections) {
            foreach ($sections as $section) {
                $table->field($section->name, [
                    'type' => 'section',
                    'title' => __($section->name),
                    'visible' => ['view' => true, 'add' => true, 'edit' => true, 'delete' => true]
                ]);
            }
        }
    }

    public function afterAction(Event $event, ArrayObject $extra)
    {
        $pass = $this->_table->request->param('pass');
        $table = $this->_table;

        $fields = $this->getFields();
        if ($fields) {
            foreach ($fields as $field) {
                $options = ['after' => $field->after_field];
                if (
                    in_array($field->field_type, ['dropdown'])
                    && isset($extra['entity'][$field->name])
                    && $extra['entity'][$field->name]
                ) {
                    $values = @unserialize($extra['entity'][$field->name]);
                    if ($values) {
                        $valuesData = [];
                        foreach($values as $value) {
                            $valuesData[] = $field->dropdown[$value];
                        }
                        $options['attr']['value'] = isset($pass[0]) && in_array($pass[0], ['index', 'view']) ?
                            implode(', ', $valuesData) :
                            $values;
                        $extra['entity'][$field->name] = implode(', ', $valuesData);
                    }
                }
                $table->field($field->name, $options);
            }
        }

        $sections = $this->getSections();
        if ($sections) {
            foreach ($sections as $section) {
                $table->field($section->name, ['before' => $section->before_field]);
            }
        }
    }

    private function getSections()
    {
        if ($this->sections) {
            return $this->sections;
        }

        $section = TableRegistry::get('MonFields.MonSections');

        return $this->sections = $section->getSections($this->_table->registryAlias());
    }

    private function getFields()
    {
        if ($this->fields) {
            return $this->fields;
        }

        $fields = TableRegistry::get('MonFields.MonFields')->getFields($this->_table->registryAlias());

        return $this->fields = $fields;
    }

    private function getDropdowns()
    {
        if ($this->dropdowns) {
            return $this->dropdowns;
        }

        $fields = $this->getFields();
        if (!$fields) {
            return;
        }

        $dropdowns = [];
        foreach ($fields as $field) {
            if ($field->field_type === 'dropdown') {
                $field->dropdown = TableRegistry::get('MonFields.MonDropdowns')->getDropdown($field->id);
                $dropdowns[] = $field;
            }
        }

        return $this->dropdowns = $dropdowns;
    }

    private function getRelations()
    {
        if ($this->relations) {
            return $this->relations;
        }

        $fields = $this->getFields();
        if (!$fields) {
            return;
        }

        $relations = [];
        foreach ($fields as $field) {
            if ($field->field_type === 'relation') {
                $field->relation = TableRegistry::get('MonFields.MonDropdowns')->getDropdown($field->id, true);
                $relations[] = $field;
            }
        }

        return $this->relations = $relations;
    }

    public function translateDropdowns(array $dropdowns)
    {
        $result = [];
        foreach ($dropdowns as $dropdown) {
            $result[] = __($dropdown);
        }

        return $result;
    }
}
