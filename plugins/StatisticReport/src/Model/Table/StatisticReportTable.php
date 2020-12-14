<?php
namespace StatisticReport\Model\Table;

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
class StatisticReportTable extends ControllerActionTable
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

        $this->table('statistic_report');
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
            ->allowEmpty('code');

        $validator
            ->allowEmpty('short_name');

        $validator
            ->allowEmpty('name');

        $validator
            ->allowEmpty('institution_types_id');

        $validator
            ->allowEmpty('academic_periods_id');

        $validator
            ->allowEmpty('institutions_id');

        $validator
            ->allowEmpty('start_date');

        $validator
            ->allowEmpty('end_date');

        $validator
            ->allowEmpty('operator');

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

    public function getPickList() {
        $connection = ConnectionManager::get('default');

        $sql = "SELECT areas.name       AS name_sruct,
                   areas.id             AS areas_id,
                   area_parent.name     AS area_parent,
                   area_parent.id       AS area_paren_id,
                   areas.area_level_id  AS area_level_id,
                   institutions.name    AS org_name,
                   institutions.id      AS org_id,
                   institution_types.name AS institution_type_name,
                   institution_types.id AS institution_type_id
                    FROM areas
                             INNER JOIN areas as area_parent
                                        ON areas.parent_id = area_parent.id
                             INNER JOIN institutions
                                        ON areas.id = institutions.area_id
                             INNER JOIN institution_types
                                        ON institution_types.id = institutions.institution_type_id
                    GROUP BY org_name";
        $result = $connection->execute($sql,[])->fetchAll(\PDO::FETCH_ASSOC);

        $result = $this->convert2PickList($result);
        return $result;
    }

    private function convert2PickList($data)
    {
        $temp = [
            'orgs'   => ['Все'=>['name'=>'Все','value'=>'']],
            'districts' => ['Все'=>['name'=>'Все','value'=>'']],
            ];

//        var_dump($data);
        foreach ($data as $item) {
            $temp['districts'][$item['area_paren_id']][$item['areas_id']] = ['name'=>$item['name_sruct'],'value'=>$item['areas_id']];
            $temp['orgs'][$item['areas_id']][$item['org_id']] = ['name'=>$item['org_name'],'value'=>$item['org_id'],'type'=>$item['institution_type_id']];
        }

        return $temp;
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

    public function getTemplates() {
        $connection = ConnectionManager::get('default');
        $query = $connection->newQuery();

        $query->select('*')->from('template_report');

        $data = array();
        foreach ($query as $row) {
            $data[$row['id']] = "{$row['name']} (".__($row['type']).")";
        }

        return $data;
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

    public function getFAQ(){
        $connection = ConnectionManager::get('default');
        $query = $connection->newQuery();
        $query->select('*')->from('faq');

        $faq = array();
        foreach ($query as $row) {
            $faq_type = $row['type'];
            $faq[$faq_type][] = $row;
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

        return $locations;
    }

    public function getFile($id)  {
        $connection = ConnectionManager::get('default');
        $sql = "SELECT 	filename FROM faq WHERE id = ?";
        $result = $connection->execute($sql,[$id]);
        return $result->fetch()[0];
    }

}
