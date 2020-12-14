<?php
namespace MonFields\Model\Table;

use App\Model\Table\AppTable;
use Cake\Validation\Validator;

class MonSectionsTable extends AppTable
{
    public function validationDefault(Validator $validator)
    {
        $validator = parent::validationDefault($validator);
        $validator->add('name', ['string' => [
            'rule' => ['custom', '/^[a-zA-Z ]*$/i'],
            'message' => __('Only latin letters are allowed')
        ]]);
        
        return $validator;
    }
    
    public function getSections(string $model): array
    {
        return $this->find()->where([
            'model' => $model
        ])->all()->toArray();
    }
}