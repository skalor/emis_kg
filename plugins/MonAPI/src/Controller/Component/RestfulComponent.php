<?php
namespace MonAPI\Controller\Component;

use ArrayObject;
use Cake\Utility\Inflector;
use Exception;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;
use Firebase\JWT\JWT;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use Restful\Controller\Component\RestfulV2Component;

class RestfulComponent extends RestfulV2Component
{
    public $components = ['MonAPI.Table', 'Auth'];
    public $model;
    public $controller;
    public $session;
    public $extra;
    public $serialize;
    public $accessParams;

    public $userId = 0;

    public $actions = [
        // All public methods from RestfulController
        // which contains words 'get', 'add', 'update', 'delete', 'parse' added to this list automatically.
        // For example: 'getInstitution', 'addInstitution', 'updateInstitution', 'deleteInstitution', 'parseInstitution'.
        // If you want to add new method, add him to the end after all MonAPI methods in RestfulController
    ];

    public $models = [
        'AcademicPeriod.AcademicPeriods',
        'Area.Areas',
        'Area.AreaAdministratives',
        'Education.EducationGrades',
        'Education.EducationProgrammes',
        'Education.EducationStages',
        'Education.EducationLevels',
        'Education.EducationCycles',
        'Education.EducationFieldOfStudies',
        'Education.EducationSpecialization',
        'Education.EducationFormOfTraining',
        'FieldOption.Nationalities',
        'FieldOption.NationalitiesUsers',
        'Institution.Sectors',
        'Institution.Providers',
        'Institution.Types',
        'Institution.Ownerships',
        'Institution.Genders',
        'Institution.Localities',
        'Institution.StaffTypes',
        'Institution.StaffPositionTitles',
        'Institution.InstitutionPositions',
        'User.Genders',
        'User.IdentityTypes',
        'Languages',
        'User.FormOfPayment'
    ];


    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->controller = $this->_registry->getController();
        $this->session = $this->request->session();
        $this->extra = new ArrayObject([]);
        $this->serialize = new ArrayObject([]);
        $this->actions = $this->getActions('MonAPI\\Controller\\RestfulController');
    }

    public function startup(Event $event)
    {
        $this->model = $this->instantiateModel($this->request->model);
        $this->extra['user'] = null;
        $this->extra['action'] = $this->request->params['action'];
    }

    public function beforeRender(Event $event)
    {
        $controller = $this->controller;
        $serialize = $this->serialize;

        if ($this->schema == true) {
            $schema = $this->model->getSchema();
            $serialize['schema'] = $schema->toArray();
            if ($schema->hasFilters()) {
                $serialize['filters'] = $schema->getFilters();
            }
        }

        if (array_key_exists('_serialize', $controller->viewVars)) {
            $_serialize = $controller->viewVars['_serialize'];
            foreach ($_serialize as $key) {
                $serialize->offsetSet($key, $controller->viewVars[$key]);
            }
        }
        $serialize['_serialize'] = array_keys($serialize->getArrayCopy());
        $controller->set($serialize->getArrayCopy());
    }

    public function isAuthorized($user = null)
    {
        $userAccess = TableRegistry::get('MonAPI.MonApi')->find()->where(['user_id' => $user['id']])->first();
        if ($userAccess) {
            $params = $this->accessParams = unserialize($userAccess->params);
            $model = $this->request->param('model');
            $action = $this->request->param('action');
            $models = $this->getValues($this->models, $params['modelsIds']);
            $actions = $this->getValues($this->actions, $params['actionsIds']);

            if (
                in_array($model, $models)
                || in_array($action, $actions)
                || $user['id'] === $this->userId
            ) {
                $this->request->data = $this->prepareData($this->request->data);
                //$this->request->query = $this->prepareData($this->request->query);
                return true;
            }
        }

        return false;
    }

    public function getValues(array $arrayAll, array $arraySelected)
    {
        $result = [];

        foreach ($arrayAll as $arrayAllKey => $arrayAllItem) {
            foreach ($arraySelected as $arraySelectedKey => $arraySelectedItem) {
                if ($arraySelectedItem == $arrayAllKey) {
                    $result[] = $arrayAllItem;
                }
            }
        }

        return $result;
    }

    public function getActions(string $className)
    {
        $result = [];
        $allowedPrefixes = ['get', 'add', 'update', 'delete', 'parse'];

        try {
            $class = new ReflectionClass($className);
            $actions = $class->getMethods(ReflectionMethod::IS_PUBLIC);

            foreach ($actions as $action) {
                if ($action->class != $className) {
                    continue;
                }

                foreach ($allowedPrefixes as $allowedPrefix) {
                    if (strpos($action->name, $allowedPrefix) === 0) {
                        array_push($result, $action->name);
                    }
                }
            }
        } catch (ReflectionException $exception) {
            // nothing
        }

        return $result;
    }

    public function generateToken(bool $return = false)
    {
        $token = '';

        $user = $this->Auth->identify();
        if ($user) {
            $token = JWT::encode([
                'sub' => $user['id'],
                'exp' => time() + 86400
            ], Configure::read('Application.private.key'), 'RS256');
        }

        if ($return) {
            return $token;
        }

        if ($token) {
            $this->controller->set('error', '');
        } else {
            $this->controller->set('error', __('Please send correct username and password fields in JSON'));
        }

        $this->serialize->offsetSet('result', $token);
    }

    public function updateOpenSSLKeys()
    {
        $privateKeyHandle = fopen(CONFIG . 'private.key', 'w');
        $publicKeyHandle = fopen(CONFIG . 'public.key', 'w');

        $res = openssl_pkey_new(['private_key_bits' => 1024]);
        openssl_pkey_export($res, $privKey);
        fwrite($privateKeyHandle, $privKey);
        fclose($privateKeyHandle);

        $pubKey = openssl_pkey_get_details($res);
        fwrite($publicKeyHandle, $pubKey['key']);
        fclose($publicKeyHandle);

        $this->controller->set('error', '');
        $this->serialize->offsetSet('result', 'done');
    }

    public function logging(string $fileName, $message, $context = [], bool $logging = true)
    {
        if (!$logging) {
            return null;
        }

        Log::reset();
        Log::config('info', [
            'className' => 'Cake\Log\Engine\FileLog',
            'path' => LOGS,
            'levels' => ['info'],
            'file' => $fileName
        ]);

        return Log::write('info', [
            'userId' => $this->Auth->user('id'),
            'userName' => $this->Auth->user('username'),
            'url' => $this->request->url,
            'result' => $message
        ], $context);
    }

    public function add(bool $serialize = true, bool $multi = false)
    {
        try {
            $table = $this->model;
            $extra = $this->extra;

            $options = ['extra' => $extra];
            $data = $this->request->data;

            if ($multi) {
                $entities = $table->newEntities($data, $options);
                $errors = [];
                foreach ($entities as $entity) {
                    $table->save($entity, $options);
                    $errors[] = $entity->errors();
                }
            } else {
                $entities = $table->newEntity($data, $options);
                $table->save($entities, $options);
                $errors = $entities->errors();
            }

            if ($serialize) {
                $this->serialize->offsetSet('data', $entities);
                $this->serialize->offsetSet('error', $errors);
            } else {
                $data = ['data' => $entities, 'error' => $errors];
                return $data;
            }

        } catch (Exception $e) {
            $this->_outputError($e->getMessage());
        }
    }

    public function instantiateModel($model)
    {
        $model = str_replace('-', '.', $model);
        if (Configure::read('debug')) {
            $_connectionName = $this->request->query('_db') ? $this->request->query('_db') : 'default';
            $target = TableRegistry::get($model, ['connectionName' => $_connectionName]);
        } else {
            $target = TableRegistry::get($model);
        }

        try {
            $target->find('all')->limit('1');
            return $target;
        } catch (Exception $e) {
            $this->_outputError();
            return false;
        }
    }

    public function _outputError(string $message = 'Requested Plugin-Model does not exists')
    {
        $this->controller->set([
            'model' => method_exists($this->model, 'alias') ? $this->model->alias() : 'no-model',
            'error' => $message,
            'request_method' => $this->request->method(),
            'action' => $this->request->params['action'],
            '_serialize' => ['request_method', 'action', 'model', 'error']
        ]);
    }

    public function curl(string $url = '', array $params = [], string $method = 'GET')
    {
        if (!$url) {
            return false;
        }

        $ch = curl_init();
        $fields = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true
        ];

        if ($this->getIfExists($params)) {
            if ($method == 'GET') {
                $fields[CURLOPT_URL] .= '?' . http_build_query($params);
            } else if ($method == 'POST') {
                $fields[CURLOPT_POST] = true;
                $fields[CURLOPT_POSTFIELDS] = $params;
            }
        }

        curl_setopt_array($ch, $fields);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    public function getIfExists($var, $value = null, string $func = 'is_string') {
        return isset($var) && !empty($var) /*&& $func($var)*/ ? $var : $value;
    }

    public function prepareData(array $data)
    {
        $result = [];
        foreach ($data as $field => $item) {
            if (is_array($item)) {
                $result[trim($field)] = $this->prepareData($item);
            } else {
                $result['superAdmin'] = 1;
                $result['userId'] = null;
                $result[trim($field)] = trim($item);
            }
        }

        return $result;
    }

    public function joinSubArray(array $data)
    {
        $ids = [];
        foreach ($data as $key => $item) {
            if (isset($item['id'])) {
                $cloneId = array_search($item['id'], $ids);
                foreach ($item->toArray() as $fieldName => $fieldValues) {
                    if (ctype_upper($fieldName[0]) && is_array($fieldValues)) {
                        if ($cloneId !== false) {
                            if (!in_array($fieldValues, $data[$cloneId][$fieldName])) {
                                array_push($data[$cloneId][$fieldName], $fieldValues);
                                unset($data[$key]);
                            }
                        } else {
                            $ids[$key] = $item['id'];
                            $data[$key][$fieldName] = [$fieldValues];
                        }
                    }
                }
            }
        }

        return array_values($data);
    }

    public function join(array $data, string $table, string $name, array $condition, array $select = [], array $contain = [])
    {
        $table = TableRegistry::get($table);
        if ($table && $data) {
            foreach ($data as $key => $entity) {
                $conditions = [];
                foreach ($condition as $field => $operation) {
                    $conditions[$table->aliasField($field) . ' ' . $operation] = $entity->$field;
                }
                $tableComponent = $this->Table->clearAll()->setTable($table);
                $tableComponent->setTableConditions($conditions)->excludeTableContains(null, true)->setReturn(false);
                $data[$key][$name] = $tableComponent->getTableData()->select($select)->contain($contain)->all()->toArray();
            }
        }

        return $data;
    }

    public function getJson(string $url, int $page = null)
    {
        $cond = false;
        if (!$url) {
            return $cond;
        }

        $page ? $suffix = '?page=' . $page : $suffix = '';

        $json = file_get_contents($url . $suffix);

        if (!$this->isJson($json)) {
            return $cond;
        }

        return $json;
    }

    public function isJson(string $string)
    {
        return json_decode($string, true) ? true : false;
    }
}
