<?php

namespace App\Services\DataDumper;

use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;

class LabelExporter
{
    private $types = [
        'institution',
        'student',
        'staff',
        'infrastructure'
    ];
    private $field = 'custom_fields';
    
    public function export(): void
    {
        $fields = [];
        
        foreach($this->types as $type) {
            $table = TableRegistry::get($this->getType($type));
            $all = $table->find()->all()->toArray();
            $all = array_map(function(Entity $entity) {
                return trim(str_replace(',', ' ', $entity->name));
            }, $all);
            $fields = array_merge($fields, $all);
        }
        
        $str = "";
        echo (implode('<br>', explode("\n", $str)));
        
//        for($i = 0; $i < count($translations); $i++) {
//            $res[$fields[$i]] = $translations[$i];
//        }
//        dd($translations);
        
        foreach($translations as $field) {
            echo $field . "<br>";
        }
        
        die;
//        dd($fields, $translations, $res);
    }
    
    private function getType(string $type): string
    {
        if(!in_array($type, $this->types)) {
            throw new \InvalidArgumentException("$type doesn't exist, please check.");
        }
        
        return "{$type}_{$this->field}";
    }
}