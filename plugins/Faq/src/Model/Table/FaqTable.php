<?php
namespace Faq\Model\Table;

use App\Model\Table\AppTable;
use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Datasource\ConnectionManager;
use App\Model\Table\ControllerActionTable;

/**
 * Faq Model
 *
 * @property \Cake\ORM\Association\BelongsTo $ModifiedUsers
 * @property \Cake\ORM\Association\BelongsTo $CreatedUsers
 *
 * @method \Faq\Model\Entity\Faq get($primaryKey, $options = [])
 * @method \Faq\Model\Entity\Faq newEntity($data = null, array $options = [])
 * @method \Faq\Model\Entity\Faq[] newEntities(array $data, array $options = [])
 * @method \Faq\Model\Entity\Faq|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Faq\Model\Entity\Faq patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \Faq\Model\Entity\Faq[] patchEntities($entities, array $data, array $options = [])
 * @method \Faq\Model\Entity\Faq findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class FaqTable extends ControllerActionTable
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('faq');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');
//        $this->belongsTo('Users', ['className' => 'Security.Users', 'foreignKey' => 'user_id']);

        $this->belongsTo('ModifiedUsers', [
            'foreignKey' => 'modified_user_id',
            'className' => 'Users.security_users'
        ]);
        $this->belongsTo('CreatedUsers', [
            'foreignKey' => 'created_user_id',
            'className' => 'Users.security_users'
        ]);

    }

    public function implementedEvents()
    {
        $events = parent::implementedEvents();
        $events['Restful.Model.isAuthorized'] = ['callable' => 'isAuthorized', 'priority' => 1];
        return $events;
    }

    public function isAuthorized(Event $event, $scope, $action, $extra)
    {
        if ($action == 'download' || $action == 'image') {
            $event->stopPropagation();
            return true;
        }

        return false;
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('id')
            ->allowEmpty('id', 'create');

        $validator
            ->allowEmpty('question');

        $validator
            ->allowEmpty('answer');

        $validator
            ->allowEmpty('type');

        $validator
            ->allowEmpty('lang');

        $validator
            ->allowEmpty('file_name');
        $validator
            ->allowEmpty('file_content');

        $validator
            ->allowEmpty('location_url');

        $validator
            ->allowEmpty('audit');

        $validator
            ->allowEmpty('category');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['modified_user_id'], 'ModifiedUsers'));
        $rules->add($rules->existsIn(['created_user_id'], 'CreatedUsers'));

        return $rules;
    }

    public function beforeSave(Event $event, Entity $entity)
    {
        $entity->file_name = $entity->file_content['name'];
        $entity->file_content = file_get_contents($entity->file_content['tmp_name']);

    }

    public function getInnerFAQ(){
        $query = $this->find('all')->all()->toArray();
        $faq = array();
        foreach ($query as $row) {
            $faq_type = $row->category;
            if($faq_type=='outer'){
                continue;
            }else if ($faq_type=='video'){
                $faq['videos'][] = $row;
            }else if ($faq_type=='file') {

                $tempArray = array();
                $row->{"base64"}=base64_encode(stream_get_contents($row['file_content']));
                $faq[__("files")][] = $row;
            }else{
                $faq['questions'][__($faq_type)][] = $row;
            }

        }
        


        return $faq;
    }
    public function getOuterFAQ(){
        $connection = ConnectionManager::get('default');
        $query = $connection->newQuery();
        $query->select('*')->from('faq');

        $faq = array();
        foreach ($query as $row) {
            $faq_type = $row['category'];
            if($faq_type=='outer'){
                $faq[$faq_type][] = $row;
            }

        }

        return $faq;
    }

    public function getLocation() {
        $connection = ConnectionManager::get('default');
        $query = $connection->newQuery();
        $query->select('*')->from('locales');

        $locations = array();
        foreach ($query as $row) {
            $locations[$row['iso']] = $row['name'];
        }

        return array_reverse($locations,true);;
    }

    public function getFile($id)  {
        $connection = ConnectionManager::get('default');
        $sql = "SELECT 	file_name FROM faq WHERE id = ?";
        $result = $connection->execute($sql,[$id]);
        return $result->fetch()[0];
    }

}
