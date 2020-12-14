<?php

namespace HiddenField\Model\Table;

use App\Model\Table\AppTable;
use Cake\ORM\TableRegistry;

class HiddenFieldsTable extends AppTable
{
    public function initialize(array $config)
    {
        parent::initialize($config);
    
        $this->belongsTo('SecurityRoles');
        $this->belongsTo('InstitutionTypes');
    }
    
    public function getOne(int $institutionType, string $controller, string $model, int $role, string $fieldName)
    {
        return $this->find()->where($this->getWhere($institutionType, $controller, $model, $role, $fieldName))->first();
    }
    
    public function findWith(string $controller, int $institutionType, string $model, array $roles)
    {
        $where = [
            'model' => $model,
            'institution_type_id' => $institutionType,
            'controller' => $controller,
        ];
        
        $roles = array_map(function($role) { return $role->id; }, $roles);
     
        $query = $this->find()->where($where);
        
        if(count($roles) === 1) {
            $query->where([
                'security_role_id' => $roles[0]
            ]);
        } else {
            $query->orWhere($roles);
        }
        
        return $query->toArray();
    }
    
    public function getWhere(int $institutionType, string $controller, string $model, int $role, ?string $fieldName = null)
    {
        $where = [
            'model' => $model,
            'security_role_id' => $role,
            'institution_type_id' => $institutionType,
            'controller' => $controller,
        ];
        
        if(!empty($fieldName)) {
            $where['field'] = $fieldName;
        }
        
        return $where;
    }
}