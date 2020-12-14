<?php
namespace MonAPI\Model\Table;

use App\Model\Table\AppTable;
use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;

class MonApiTable extends AppTable
{
    private $user;

    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->belongsTo('Users', ['className' => 'Security.Users', 'foreignKey' => 'user_id']);
    }

    public function validationDefault(Validator $validator)
    {
        $validator = parent::validationDefault($validator);
        $validator->allowEmpty('user_id');
        $validator->add('username', 'custom', [
            'rule' => function ($value, $context) {
                $this->user = $value ? TableRegistry::get('Security.Users')->find()->where(['username' => $value])->first() : null;

                if (!$this->user) {
                    return false;
                }

                if (!$context['data']['user_id'] && $this->find()->where(['user_id' => $this->user->get('id')])->first()) {
                    return __('User already exists');
                }

                return true;
            },
            'message' => __('User not found')
        ]);

        return $validator;
    }

    public function beforeSave(Event $event, Entity $entity)
    {
        $entity->user_id = $this->user->get('id');
        $params = [
            'modelsIds' => [],
            'actionsIds' => [],
            'institutionsTypesIds' => [],
            'institutionsCodes' => ''
        ];

        if (isset($entity->models['_ids']) && $entity->models['_ids']) {
            $params['modelsIds'] += $entity->models['_ids'];
        }

        if (isset($entity->actions['_ids']) && $entity->actions['_ids']) {
            $params['actionsIds'] += $entity->actions['_ids'];
        }

        if (isset($entity->institutions_types['_ids']) && $entity->institutions_types['_ids']) {
            $params['institutionsTypesIds'] += $entity->institutions_types['_ids'];
        }

        if ($entity->institutions_codes) {
            $params['institutionsCodes'] = $entity->institutions_codes;
        }

        $entity->params = serialize($params);
    }
}
