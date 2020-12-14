<?php
namespace TemplateReport\Model\Table;

use ArrayObject;
use Cake\Event\Event;
use Cake\ORM\Query;
use Cake\ORM\ResultSet;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Datasource\ConnectionManager;
use App\Model\Table\ControllerActionTable;
use PHPExcel_Worksheet;
use DateTime;
use DateInterval;
use Cake\Utility\Inflector;
use Cake\ORM\TableRegistry;

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
class TemplateReportTable extends ControllerActionTable
{

    protected $rootFolder = 'import';

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('template_report');
        //$this->displayField('id');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');

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

    public function getCountStudentByGender($params) {
        $params = $params['query'];
        $connection = ConnectionManager::get('default');
        $regDist = [];
        $institutions = [];
        if(!empty($params['region'])) {
            array_push($regDist, $params['region']);
        }

        if(!empty($params['districts'])) {
            array_push($regDist, $params['districts']);
        }

        if(!empty($params['institutions_id'])) {
            $institutions   = [$params['institutions_id']];
        }

        $sql = $this->getQueryByGender($params,$regDist,$institutions);

        $data = $connection->execute($sql,[])->fetchAll(\PDO::FETCH_ASSOC);
        $studentSheets = [];
        foreach ($data as $row) {

            if($row['gender_id'] == 2) {
                if($row['year_old'] >= 25 &&  $row['year_old'] <= 35) {
                    $studentSheets['female']['25_35'] += $row['count'];
                } else if($row['year_old'] > 35) {
                    $studentSheets['female']['35_more'] += $row['count'];
                } else {
                    $studentSheets['female'][$row['year_old']] = $row['count'];
                }
            }

            if($row['year_old'] >= 25 &&  $row['year_old'] <= 35) {
                $studentSheets['total']['25_35'] += $row['count'];
            } else if($row['year_old'] > 35) {
                $studentSheets['total']['35_more'] += $row['count'];
            } else {
                $studentSheets['total'][$row['year_old']] += $row['count'];
            }
        }

        return $studentSheets;
    }

    protected function getQueryByGender($params,$regionAndDistrict,$institutions)
    {

        if(!empty($regionAndDistrict)) {
            $__regionAndDistrictList    = implode($regionAndDistrict,"','");
            $regionAndDistrictSQL    = " AND areas.id IN ('{$__regionAndDistrictList}') ";
        } else {
            $regionAndDistrictSQL = '';
        }

        if(!empty($institutions)) {
            $__institutionsList   = implode($institutions,"','");
            $institutionsSQL    = " AND institutions.id IN ('{$__institutionsList}') ";
        } else {
            $institutionsSQL = '';
        }

        if( strlen($params['academic_periods_id']) > 0 ){
            $academic_periods_id = "AND academic_period_id = {$params['academic_periods_id']}";
        } else {
            $academic_periods_id = '';
        }

        if( strlen($params['institution_types_id']) > 0 ) {
            $institution_types_id = "AND institutions.institution_type_id = {$params['institution_types_id']}";
        } else {
            $institution_types_id = "";
        }

        $sql = "SELECT TIMESTAMPDIFF(YEAR,date_of_birth, now() ) AS year_old,
                    COUNT(DISTINCT security_users.id) AS count,
                    security_users.gender_id
                    FROM areas
                    INNER JOIN institutions
                    ON areas.id = institutions.area_id
                    INNER JOIN institution_students
                    ON institution_students.institution_id = institutions.id
                    INNER JOIN security_users
                    ON security_users.id = institution_students.student_id
                    WHERE TIMESTAMPDIFF(YEAR,date_of_birth, now() ) > 13
                    {$institutionsSQL}
                    {$regionAndDistrictSQL}
                    {$academic_periods_id}
                    {$institution_types_id}
                    GROUP BY security_users.gender_id,TIMESTAMPDIFF(YEAR,date_of_birth, now() )";

        return $sql;
    }

    public function getInstitutionType()
    {
        $connection = ConnectionManager::get('default');
        $query = $connection->newQuery();
        $query->select('*')->from('institution_types');

        $inst = array();
        foreach ($query as $row) {
            $inst[$row['id']] = $row['name'];
        }

        return $inst;
    }

    public function getTemplate($template_id,$fields = false) {
        $connection = $connection = ConnectionManager::get('default');

        if($fields) {
            $SELECT_FIELD = implode(', ',$fields);
            $sql = "SELECT {$SELECT_FIELD} FROM template_report WHERE id = ?";
            $result = $connection->execute($sql,[$template_id])->fetchAll(\PDO::FETCH_ASSOC);

            $temp = [];
            foreach ($fields as $field){
                $temp[$field] = $result[0][$field];
            }

            if(!empty($temp)) {
                return $temp;
            }
        } else {
            $sql = "SELECT content FROM template_report WHERE id = ?";
            $result = $connection->execute($sql,[$template_id])->fetchAll(\PDO::FETCH_ASSOC);

            if($result && !empty($result)) {
                if(isset($result[0]['content'])) {
                    return $result[0]['content'];
                }
            }
        }


        return '';
    }

    public function getRecord($plugin,$record) {
        $tableName =  substr(strtolower(implode('_', preg_split('/(?=[A-Z])/',$plugin))),1);
        $connection = ConnectionManager::get('default');

        $data = $connection->execute(
            "SELECT * FROM {$tableName} WHERE id = ?",
            [$record]
        )->fetchAll(\PDO::FETCH_ASSOC);

        return $data[0];
    }
}
