<?php declare(strict_types=1);

namespace App\Services\DataDumper;

use Cake\Log\Log;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;

class DataFieldExporter
{
    private const CHUNK_COUNT = 10000;
    
    /**
     * @var Table
     */
    private $toTable;
    
    /**
     * @var callable|null
     */
    private $validatorFrom;
    
    /**
     * @var callable|null
     */
    private $validatorTo;
    
    /**
     * @var Table
     */
    private $fromTable;
    
    /**
     * @var string
     */
    private $foreignKey;
    
    public function __construct(string $table)
    {
        $this->setTable($table);
        
        $this->validatorFrom = function() {
            return true;
        };
        
        $this->validatorTo = function($value) {
            return !empty($value);
        };
    }
    
    public function __clone()
    {
        $this->toTable = null;
        $this->fromTable = null;
    }
    
    public function export($from, string $to): void
    {
        if(!is_string($from) && !is_array($from)) {
            throw new \InvalidArgumentException('From must be string or array!');    
        }
        
        if(is_string($from)) {
            $from = [$from];
        }    
        
        foreach($from as $one) {
            $this->exportSingle($one, $to);
        }
    }
    
    private function exportSingle(string $from, string $to): void
    {
        $count = $this->fromTable->find()->count();
        $chunks = ceil($count / self::CHUNK_COUNT);
        
        for($i = 0; $i < $chunks; $i++) {
            $this->exportSingleChunk($i * self::CHUNK_COUNT, $from, $to);
        }
    }
    
    private function exportSingleChunk(int $offset, string $from, string $to): void
    {
        $fromQuery = $this->fromTable->find()->limit(self::CHUNK_COUNT)->offset($offset)->all();
        $fromAll = $fromQuery->toArray();
        $isSame = $this->isSameTables();
        
        $toRows = [];
        
        foreach($fromAll as $fromRow) {
            try {
                $toRow = $isSame ? $fromRow : $this->toTable->get($fromRow->{$this->getForeignKey()});
                
                if(!$this->validateFrom($fromRow->{$from}) || !$this->validateTo($toRow->{$to})) {
                    continue;
                }
                
                $toRow->{$to} = $fromRow->{$from};
                $toRows[] = $toRow;
            } catch(\Exception $e) {
                Log::debug($e->getMessage());
            }
        }
        
        $this->toTable->saveMany($toRows);
    }
    
    private function validateFrom($value): bool
    {
        return $this->validate($this->validatorFrom, $value);
    }
    
    private function validateTo($value): bool
    {
        return $this->validate($this->validatorTo, $value);
    }
    
    private function validate(?callable $validator, $value): bool
    {
        if(is_null($validator)) {
            return true;
        }
    
        return $validator($value);
    }
    
    public function setValidatorFrom(callable $validatorFrom): void
    {
        $this->validatorFrom = $validatorFrom;
    }
    
    public function setValidatorTo(callable $validatorTo): void
    {
        $this->validatorTo = $validatorTo;
    }
    
    public function setTable(string $tableName): void
    {
        $this->toTable = TableRegistry::get($tableName);
        $this->fromTable = TableRegistry::get($tableName);
    }
    
    public function setFromTable(string $tableName): void
    {
        $this->fromTable = TableRegistry::get($tableName);
    }
    
    public function setToTable(string $tableName): void
    {
        $this->toTable = TableRegistry::get($tableName);
    }
    
    private function getForeignKey(): string
    {
        if(!$this->foreignKey) {
            $this->foreignKey = Inflector::singularize($this->getToTable()->table()) . '_id';
        }
        
        return $this->foreignKey;
    }
    
    public function setForeignKey(string $fieldName): void
    {
        $this->foreignKey = $fieldName;
    }
    
    private function getFromTable(): Table
    {
        return $this->fromTable;
    }
    
    private function isSameTables(): bool
    {
        return $this->fromTable->table() === $this->toTable->table();
    }
    
    private function getToTable(): Table
    {
        return $this->toTable;
    }
}