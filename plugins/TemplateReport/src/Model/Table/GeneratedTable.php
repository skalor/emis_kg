<?php
namespace TemplateReport\Model\Table;

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
 * @method \Faq\Model\Entity\StatisticReport get($primaryKey, $options = [])
 * @method \Faq\Model\Entity\StatisticReport newEntity($data = null, array $options = [])
 * @method \Faq\Model\Entity\StatisticReport[] newEntities(array $data, array $options = [])
 * @method \Faq\Model\Entity\StatisticReport|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Faq\Model\Entity\StatisticReport patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \Faq\Model\Entity\StatisticReport[] patchEntities($entities, array $data, array $options = [])
 * @method \Faq\Model\Entity\StatisticReport findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class GeneratedTable extends ControllerActionTable
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('statistic_report_history');
        parent::initialize($config);

        $this->table('statistic_report_history');
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

//        $this->belongsTo('Institution', [
//            'foreignKey' => 'institutions_id',
//            'className' => 'Institution.institutions'
//        ]);
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
            ->allowEmpty('linkreport');

        $validator
            ->allowEmpty('date_start');

        $validator
            ->allowEmpty('date_end');

        $validator
            ->allowEmpty('file_content');

        $validator
            ->allowEmpty('org_name');

        $validator
            ->allowEmpty('hash');

        $validator
            ->allowEmpty('employeer');

        $validator
            ->allowEmpty('owner');

        $validator
            ->allowEmpty('ecp');

        $validator
            ->allowEmpty('ecp');

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

    public function getTemplates($type = []) {
        $connection = ConnectionManager::get('default');
        $query = $connection->newQuery();

        $query->select('*')->from('template_report');
        
        if(!empty($type)) {
            $query->where($type);
        }

        $data = array();
        foreach ($query as $row) {
            $data[$row['id']] = "{$row['name']} {$row['type']}";
        }

        return $data;
    }

    public function getInstitutionType() {
        $connection = ConnectionManager::get('default');
        $query = $connection->newQuery();
        $query->select('*')->from('institution_types');

        $inst = array();
        foreach ($query as $row) {
            $inst[$row['id']] = $row['name'];
        }

        return $inst;
    }

    public function getRegions() {
        $connection = ConnectionManager::get('default');
        $query = $connection->newQuery();
        $query->select('*')->from('areas')->where(['area_level_id = 2']);

        $inst = array();
        foreach ($query as $row) {
            $inst[$row['id']] = $row['name'];
        }
        return $inst;
    }

    public function getDistricts() {
        $connection = ConnectionManager::get('default');
        $query = $connection->newQuery();
        $query->select('*')->from('areas')->where(['area_level_id = 3']);

        $inst = array();
        foreach ($query as $row) {
            $inst[$row['id']] = $row['name'];
        }
        return $inst;
    }

    public function getOrganizations() {
        $connection = ConnectionManager::get('default');
        $query = $connection->newQuery();
        $query->select('*')->from('institutions');

        $inst = array();
        foreach ($query as $row) {
            $inst[$row['id']] = $row['name'];
        }
        return $inst;
    }

    public function getAcademicPeriod() {
        $connection = ConnectionManager::get('default');
        $query = $connection->newQuery();
        $query->select('*')
            ->from('academic_periods')
            ->where(['name != "All Data"']);

        $inst = array();
        foreach ($query as $row) {
            $inst[$row['id']] = $row['name'];
        }
        return $inst;
    }

    public function getUserByRole() {
        $connection = ConnectionManager::get('default');
        $query = $connection->newQuery();
        $query->select('*' )
            ->from('security_group_users')
            ->innerJoin(['Users' => 'security_users'],
                [ 'Users.id = security_group_users.security_user_id' ]
            )
            ->where(['security_group_users.security_role_id = 14']);

        $roles = array();
        foreach ($query as $row) {
            $roles[$row['security_user_id']] = "{$row['middle_name']} {$row['first_name']} {$row['last_name']}";
        }
        return $roles;
    }

    public function getUserByRoles() {
        $connection = ConnectionManager::get('default');
        $query = $connection->newQuery();
        $query->select('*' )
            ->from('security_group_users')
            ->innerJoin(['Users' => 'security_users'],
                [ 'Users.id = security_group_users.security_user_id' ]
            )
            ->where(['OR' => [
                ['security_group_users.security_role_id = 19'],
                ['security_group_users.security_role_id = 20']
            ]]);

        $roles = array();
        foreach ($query as $row) {
            $roles[$row['security_user_id']] = "{$row['middle_name']} {$row['first_name']} {$row['last_name']}";
        }
        return $roles;
    }
}
