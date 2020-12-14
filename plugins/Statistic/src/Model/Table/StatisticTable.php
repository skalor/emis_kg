<?php
namespace Statistic\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\ORM\TableRegistry;
use Cake\Datasource\ConnectionManager;
use Doctrine\Common\Cache\PhpFileCache;

/**
 * Employees Model
 *
 * @property \Cake\ORM\Association\BelongsTo $AreaLevels
 *
 * @method \Statistic\Model\Entity\Statistic get($primaryKey, $options = [])
 * @method \Statistic\Model\Entity\Statistic newEntity($data = null, array $options = [])
 * @method \Statistic\Model\Entity\Statistic[] newEntities(array $data, array $options = [])
 * @method \Statistic\Model\Entity\Statistic|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Statistic\Model\Entity\Statistic patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \Statistic\Model\Entity\Statistic[] patchEntities($entities, array $data, array $options = [])
 * @method \Statistic\Model\Entity\Statistic findOrCreate($search, callable $callback = null, $options = [])
 */
class StatisticTable extends Table
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

        $this->table('employees_report');
        $this->displayField('name');
        $this->primaryKey('id');

        $this->belongsTo('AreaLevels', [
            'foreignKey' => 'area_level_id',
            'joinType' => 'INNER',
            'className' => 'Employees.AreaLevels'
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
            ->allowEmpty('region');

        $validator
            ->requirePresence('name', 'create')
            ->notEmpty('name');

        $validator
            ->allowEmpty('org_struct');

        $validator
            ->requirePresence('code', 'create')
            ->notEmpty('code');

        $validator
            ->integer('staff_male_count')
            ->requirePresence('staff_male_count', 'create')
            ->notEmpty('staff_male_count');

        $validator
            ->integer('staff_female_count')
            ->requirePresence('staff_female_count', 'create')
            ->notEmpty('staff_female_count');

        $validator
            ->integer('staff_total_count')
            ->requirePresence('staff_total_count', 'create')
            ->notEmpty('staff_total_count');

        $validator
            ->dateTime('create_at')
            ->requirePresence('create_at', 'create')
            ->notEmpty('create_at');

        $validator
            ->integer('student_male_count')
            ->allowEmpty('student_male_count');

        $validator
            ->integer('student_female_count')
            ->allowEmpty('student_female_count');

        $validator
            ->integer('student_total_count')
            ->allowEmpty('student_total_count');

        $validator
            ->integer('all_total_count')
            ->allowEmpty('all_total_count');

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
        $rules->add($rules->existsIn(['area_level_id'], 'AreaLevels'));

        return $rules;
    }

    public function getCountByRegion() {
        $connection = ConnectionManager::get('default');
        $query = $connection->newQuery();
        $query->select(
            "`region` AS region,
            COUNT(name) AS org,
            SUM(`staff_male_count`) AS staff_male_count,
            SUM(`staff_female_count`) AS staff_female_count,
            SUM(`staff_total_count`) AS staff_total_count,
            SUM(`student_male_count`) AS student_male_count,
            SUM(`student_female_count`) AS student_female_count,
            SUM(`student_total_count`) AS student_total_count,
            SUM(`all_total_count`) AS all_total_count"
        )->from('employees_report')->group('region');

        $regionTotalCount = array();
        foreach ($query as $row) {
            $regionTotalCount[] = $row;
        }

        return $regionTotalCount;
    }

    public function getCountByDistricts() {
        $connection = ConnectionManager::get('default');
        $query = $connection->newQuery();
        $query->select(
            "`region` AS region,
            `name` as name,
            COUNT(name) AS org,
            SUM(`staff_male_count`) AS staff_male_count,
            SUM(`staff_female_count`) AS staff_female_count,
            SUM(`staff_total_count`) AS staff_total_count,
            SUM(`student_male_count`) AS student_male_count,
            SUM(`student_female_count`) AS student_female_count,
            SUM(`student_total_count`) AS student_total_count,
            SUM(`all_total_count`) AS all_total_count"
        )->from('employees_report')->group('name');

        $districtTotalCount = array();

        foreach ($query as $row) {
            $region = $row['region'];
            $districtTotalCount[$region][] = $row;
        }
        return $districtTotalCount;
    }

    public function getCountByOrganization() {
        $connection = ConnectionManager::get('default');
        $query = $connection->newQuery();
        $query->select("*")->from('employees_report');

        $organizationTotalCount = array();
        foreach ($query as $row) {
            $organization = $row['name'];
            $organizationTotalCount[$organization][] = $row;
        }

//        var_dump($organizationTotalCount);die;
        return $organizationTotalCount;
    }

    public function getLastUpdateStatistic() {
        $connection = ConnectionManager::get('default');
        $query = $connection->newQuery();
        $query->select("start")->from('cron_job_report')->where('name = "ReportEmployees"');

        $lastUpdateStatistic = array();
        foreach ($query as $row) {
            $lastUpdateStatistic[] = $row;
        }
        return $lastUpdateStatistic[0];
    }

    public function getTypeOrganization() {
        $connection = ConnectionManager::get('default');
        $query = $connection->newQuery();
        $query->select("*")->from('institution_types');

        $lastUpdateStatistic = array();
        foreach ($query as $row) {
            $lastUpdateStatistic[] = ['id'=>$row['id'],'name'=>__($row['name'])];
        }
        return $lastUpdateStatistic;
    }
}
