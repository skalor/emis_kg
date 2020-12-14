<?php

namespace HiddenField\Services;

use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use HiddenField\Model\Repository\Tables;
use HiddenField\Model\Table\HiddenFieldsTable;

class HiddenFieldService
{
    private $models = [];
    private $functions = [];
    
    public function hide(int $institutionType, string $controller, string $model, int $role, array $fields)
    {
        /** @var HiddenFieldsTable $hidden */
        $hidden = TableRegistry::get('HiddenField.HiddenFields');
        
        foreach($fields as $field) {
            $where = $hidden->getWhere($institutionType, $controller, $model, $role, $field['name']);
            
            $data = array_merge($where, ['action' => $field['state']]);
            
            if($entity = $hidden->getOne($institutionType, $controller, $model, $role, $field['name'])) {
                $hidden->updateAll($data, $where);
            } else {
                $hidden->save($hidden->newEntity($data));
            }
        }
    }
    
    public function getModel(int $id): ?string
    {
        if($this->models[$id]) {
            return $this->models[$id];
        }
        
        $func = $this->getFunction($id);
        $tables = new Tables();
    
        return $this->models[$id] = $tables->getItem($func['category'], $func['controller'], $func['name']);
    }
    
    public function getFunction(int $id): ?Entity
    {
        if(isset($this->functions[$id])) {
            return $this->functions[$id];
        }
        
        return $this->functions[$id] = TableRegistry::get('Security.SecurityFunctions')->get($id);
    }
    
    public function getNotFillables(array $funcs)
    {
        $ids = [];
        $tables = new Tables();
        
        foreach($funcs as $func) {
            if($tables->isNull($func)) {
                $ids[] = $func->id;
            }
        }
        
        return $ids;
    }
    
    public function getFields(string $tableName, array $params)
    {
        $tables = new Tables();
        $table = TableRegistry::get($tableName);
        
        $fields = $this->getDefaultFields($tableName, $tables->only($tableName) ?? $table->schema()->columns(), $params);
        
        $params['model'] = $tableName;
        $fields = $this->getFieldsFromDb($params, $fields);
    
        return array_map(function(array $field) {
            $field['label'] = __(Inflector::humanize(str_replace('_id', '', $field['name'])));
            return $field;
        }, array_values(array_filter($fields, function(array $field) use($tables, $tableName) {
            return !in_array($field['name'], $tables->getExcludedFields($tableName));
        })));
    }
    
    public function get(string $field, array $params): ?Entity
    {
        $hidden = TableRegistry::get('HiddenField.HiddenFields');
        return $hidden->getOne($params['institutionType'], $params['controller'], $params['model'], $params['role'], $field);
    }
    
    private function getFieldsFromDb(array $params, array $fields): array
    {
        return array_map(function(array $field) use ($params) {
            $entity = $this->get($field['name'], $params);
            $field['state'] = $entity ? $entity->action : null;
            
            return $field;
        }, $fields);
    }
    
    private function getDefaultFields(string $tableName, array $fields, array $params): array
    {
        $table = TableRegistry::get($tableName);
        
        return array_map(function($field) use ($table, $tableName, $params) {
            $required = !$table->schema()->isNullable($field);
            return ['name' => $field, 'required' => $required, 'state' => null];
        }, $fields);
    }
}