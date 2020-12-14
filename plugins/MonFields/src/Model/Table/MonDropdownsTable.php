<?php
namespace MonFields\Model\Table;

use App\Model\Table\AppTable;

class MonDropdownsTable extends AppTable
{
    public function getDropdown(int $fieldId, bool $object = false)
    {
        $dropdowns = $this->find()->where([
            'field_id' => $fieldId
        ])->first();

        if ($object) {
            return $dropdowns;
        }

        $dropdowns = unserialize($dropdowns->values);

        return $dropdowns;
    }

    public function addDropdown(int $fieldId, string $model = null, array $dropdownOptions = [])
    {
        $entity = $this->newEntity();
        $exist = $this->getDropdown($fieldId, true);

        if ($exist) {
            $entity->id = $exist->id;
        }

        $entity->field_id = $fieldId;
        $entity->model = $model;

        if ($dropdownOptions) {
            $dropdowns = [];
            foreach($dropdownOptions as $dropdownOption => $dropdownOptionValue) {
                if(!strstr($dropdownOption, 'dropdown_option')) {
                    continue;
                }
                $dropdowns[] = $dropdownOptionValue;
            }
            $entity->values = serialize($dropdowns);
        }

        $this->save($entity);

        return true;
    }

    public function deleteDropdown(int $fieldId)
    {
        $entity = $this->getDropdown($fieldId, true);
        if (!$entity) {
             return false;
        }

        $this->delete($entity);

        return true;
    }
}