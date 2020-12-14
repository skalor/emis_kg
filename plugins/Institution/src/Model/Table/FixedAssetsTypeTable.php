<?php
namespace Institution\Model\Table;

use App\Model\Table\ControllerActionTable;
use Cake\Validation\Validator;

class FixedAssetsTypeTable extends ControllerActionTable
{
    public function initialize(array $config)
    {
        parent::initialize($config);


        $this->hasMany('FixedAssets', ['className' => 'Institution.FixedAssets', 'foreignKey' => 'fixed_assets_type_id']);


        $this->addBehavior('FieldOption.FieldOption');


        $this->addBehavior('Restful.RestfulAccessControl', [
            'FixedAssets' => ['index', 'add']
        ]);
    }


}