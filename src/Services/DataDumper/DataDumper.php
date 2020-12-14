<?php

namespace App\Services\DataDumper;

use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Log\Log;
use Cake\ORM\Entity;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Exception;
use InvalidArgumentException;

class DataDumper implements DumperInterface
{
    /**
     * @var string
     */
    private $type;
    
    private $types = [
        'institution' => 'Institution.Institutions',
        'student' => 'Institution.StudentUser',
        'staff' => 'Institution.StaffUser',
        'infrastructure' => null
    ];
    
    private $prefixes = [
        'fields' => 'custom_fields',
        'values' => 'custom_field_values',
        'options' => 'custom_field_options',
    ];
    
    private $exportTable;
    
    private $primaryKey;
    
    /**
     * @var array
     */
    private $options = [];
    
    /**
     * @var bool
     */
    private $dropdown = false;
    
    /**
     * @var int
     */
    private $fieldId;
    
    public function getPrimaryKey(): string
    {
        return $this->primaryKey ?? 'id';
    }
    
    public function setPrimaryKey(string $primaryKey): void
    {
        $this->primaryKey = $primaryKey;
    }
    
    public function __construct(string $type)
    {
        $this->type = $type;
    }
    
    public function dump(int $fieldId, string $fieldName, bool $dropdown = false): void
    {
        $tableName = $this->getTableName('values');
        $values = $this->getValues($tableName, $this->getFieldName('custom_field_id'), $fieldId);
        
        $this->fieldId = $fieldId;
        
        if($dropdown) {
            $this->setDropdownValues($values);
        }
        
        $this->writeValues($values, $fieldName);
    }
    
    /**
     * @param string $type
     * @throws Exception
     */
    public function setType(string $type): void
    {
        if(!isset($this->types[$type])) {
            throw new InvalidArgumentException('You must pass correct type!');
        }
        
        if($this->types[$type] === null) {
            throw new Exception("This type doesn't work.");
        }
        
        $this->type = $type;
    }
    
    private function getTypeField(): string
    {
        return $this->type . '_id';
    }
    
    private function getTypeTable(): ?Table
    {
        return TableRegistry::get($this->exportTable ?? $this->getTypeTableName());
    }
    
    private function getTypeTableName(): ?string
    {
        return $this->exportTable ?? $this->types[$this->type];
    }
    
    private function getTableName(string $fieldType): string
    {
        return "{$this->type}_{$this->prefixes[$fieldType]}";
    }
    
    private function getFieldName(string $field, ?string $type = null): string
    {
        $type = $type ?? $this->type;
        
        return "{$type}_{$field}";
    }
    
    private function getValues(string $tableName, string $fieldName, int $fieldId): array
    {
        $table = TableRegistry::get($tableName);
        $query = $table->find()->where([$fieldName => $fieldId]);
        
        return $query->all()->toArray();
    }
    
    public function setExportTable(string $name): void
    {
        $this->exportTable = $name;
    }
    
    /**
     * @param Entity $value
     * @return int|string|null
     */
    private function getValue(Entity $value)
    {
        $fields = ['text_value', 'number_value', 'decimal_value', 'textarea_value', 'date_value', 'time_value', 'file'];
        
        foreach($fields as $field) {
            if($this->dropdown) {
                return $value->number_value - 1;
            }
            
            if($value->{$field} !== null) {
                return $value->{$field};
            }
        }
        
        return null;
    }
    
    public function setDropdown(bool $dropdown): void
    {
        $this->dropdown = $dropdown;
    }
    
    private function writeValues(array $values, string $fieldName): void
    {
        $exportingTable = $this->getTypeTable();
        $typeField = $this->getTypeField();
        $primaryKey = $this->getPrimaryKey();
        
        foreach($values as $value) {
            try {
                $id = $value->{$typeField};
                /** @var Entity $row */
                $row = $exportingTable->find()->where([
                    $primaryKey => $id
                ])->firstOrFail();
                
                if(!empty($row->{$fieldName})) {
                    continue;
                }
                
                $value = $this->getValue($value);
                
                if(!empty($value)) {
                    $row->{$fieldName} = $value;
                    $exportingTable->save($row);
                }
            } catch(RecordNotFoundException $e) {
                $table = $this->getTypeTableName();
                
                Log::debug("In $table record with id#{$id} wasn't found");
            }
        }
    }
    
    private function setDropdownValues(array $values): void
    {
        foreach($values as $value) {
            $value->number_value = $this->getOption($value->number_value);
        }
    }
    
    private function getOptions(): array
    {
        if(!$this->options) {
            $this->options = TableRegistry::get($this->getTableName('options'))
                ->find()
                ->where([
                    $this->getCustomFieldForeignKey() => $this->fieldId
                ])
                ->toArray();
        }
        
        return $this->options;
    }
    
    /**
     * @param int $id
     * @return mixed
     */
    private function getOption(int $id): ?int
    {
        /** @var Entity[] $options */
        $options = $this->getOptions();
        
        foreach($options as $index => $option) {
            if($option->id === $id) {
                return $index;
            }
        }
        
        throw new \InvalidArgumentException("Option with #{$id} doesn't exist!");
    }
    
    private function getCustomFieldForeignKey(): string
    {
        return "{$this->type}_custom_field_id";
    }
}