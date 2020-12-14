<?php
namespace MonAPI\Controller\Component;

use Cake\Event\Event;
use Cake\Controller\Component;
use Cake\Database\Expression\QueryExpression;
use Cake\I18n\Date;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;

class InstitutionComponent extends Component
{
    private $controller;
    private $session;
    private $restful;
    private $model;

    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->controller = $this->_registry->getController();
        $this->session = $this->request->session();
    }

    public function beforeFilter(Event $event)
    {
        $this->restful = $this->controller->Restful;
        $this->model = $this->restful->instantiateModel('Institution.Institutions');
    }

    public function startup(Event $event)
    {
        $this->controller->set('model', 'Institution.Institutions');
        $this->controller->set('error', null);
    }

    public function hasAccess(?string $code)
    {
        if (!$code) {
            return false;
        }

        $institutions = $this->get(['code' => $code]);
        $institutionTypeId = isset($institutions[0]) ? $institutions[0]->get('institution_type_id') : '';
        $institutionsTypes = $this->controller->Restful->accessParams['institutionsTypesIds'];
        $institutionsCodes = explode(', ', $this->controller->Restful->accessParams['institutionsCodes']);

        if (in_array($code, $institutionsCodes) || in_array('*', $institutionsCodes) && in_array($institutionTypeId, $institutionsTypes)) {
            return true;
        }

        return false;
    }

    public function add(bool $return = false)
    {
        $data = $this->request->data;
        $result = ['data' => $data, 'error' => __('Please check your code. Field "code" must not be empty')];

        if (isset($data['code']) && !empty($data['code'])) {
            $recordExist = $this->get(['code' => $data['code']]);

            if (isset($recordExist[0])) {
                $result = ['exist' => true];
            } else {
                $this->restful->model = $this->model;
                $result = $this->restful->add(false);
            }
        }

        $this->controller->Restful->logging('institutions_' . date('Y-m-d'), $result);

        if ($return) return $result;

        $this->restful->serialize->offsetSet('Result', $result);
    }

    public function addMulti()
    {
        $data = $this->request->data;
        $count = count($data);
        $errorMsg = $count <= 1000 ? null : __("Max JSON elements must be <= 1000");
        $this->controller->set('error', $errorMsg);
        $result = ['data' => $data, 'error' => __('Please check your data. Your data must be like this: [ {...}, {...}, ... ]')];

        if ($data && !$errorMsg) {
            $result = [];
            foreach ($data as $item) {
                if (is_array($item)) {
                    $this->request->data = $item;
                    $result[] = $this->add(true);
                } else {
                    $result[] = __('Please check your data. Your data must be like this: [ {...}, {...}, ... ]');
                }
            }
        }

        $this->restful->serialize->offsetSet('Result', $result);
    }

    public function update(bool $return = false, bool $update = true)
    {
        $data = $this->request->data;
        $result = ['data' => $data, 'error' => __('Please check your code. Field "code" must not be empty')];
        $hasAccess = $this->hasAccess($data['code']);

        if (!$hasAccess) {
            $result['error'] = __('You do not have access to this institution');
        }

        if (isset($data['code']) && !empty($data['code']) && $hasAccess) {
            $recordExist = $this->get(['code' => $data['code']]);

            if (isset($recordExist[0])) {
                $recordExist = $recordExist[0];
                foreach ($data as $column => $value) {
                    $lowerColumn = strtolower($column);
                    if (
                        $lowerColumn != 'code' && !$recordExist->$lowerColumn ||
                        $lowerColumn != 'code' && $this->controller->Auth->user('id') === $recordExist->get('created_user_id') && $update
                    ) {
                        $recordExist->$lowerColumn = $value;
                    }
                }
                $result = ['data' => $this->model->save($recordExist), 'error' => $recordExist->errors()];
            } else {
                $result = ['exist' => false];
            }
        }

        $this->controller->Restful->logging('institutions_' . date('Y-m-d'), $result);

        if ($return) return $result;

        $this->restful->serialize->offsetSet('Result', $result);
    }

    public function updateMulti()
    {
        $data = $this->request->data;
        $count = count($data);
        $errorMsg = $count <= 1000 ? null : __("Max JSON elements must be <= 1000");
        $this->controller->set('error', $errorMsg);
        $result = ['data' => $data, 'error' => __('Please check your data. Your data must be like this: [ {...}, {...}, ... ]')];

        if ($data && !$errorMsg) {
            $result = [];
            foreach ($data as $item) {
                if (is_array($item)) {
                    $this->request->data = $item;
                    $result[] = $this->update(true);
                } else {
                    $result[] = __('Please check your data. Your data must be like this: [ {...}, {...}, ... ]');
                }
            }
        }

        $this->restful->serialize->offsetSet('Result', $result);
    }

    public function get(array $where = [], bool $return = true)
    {
        $all = isset($where['_all']) && $where['_all'] ? true : false;
        $page = isset($where['_page']) && (int)$where['_page'] > 0 ? (int)$where['_page'] : 1;

        $conditions = [];

        isset($where['created_from']) && $where['created_from'] ? $conditions['Institutions.created >='] = $where['created_from'] : null;
        isset($where['created_to']) && $where['created_to'] ? $conditions['Institutions.created <='] = $where['created_to'] : null;
        isset($where['code']) && $where['code'] ? $conditions['Institutions.code'] = $where['code'] : null;
        isset($where['institution_type_id']) && $where['institution_type_id'] ? $conditions['Institutions.institution_type_id'] = $where['institution_type_id'] : null;

        $result = $this->model->find('all', [
            'order' => 'Institutions.created DESC',
            'contain' => $return ? [] : ['Types', 'Areas', 'AreaAdministratives'],
            'fields' => $return ? [] : [
                'Institutions.id',
                'Institutions.name',
                'Institutions.code',
                'Institutions.date_opened',
                'Areas.code',
                'Areas.name',
                'AreaAdministratives.code',
                'AreaAdministratives.name',
                'Types.name'
            ],
            'conditions' => $conditions
        ]);

        $total = $result->count();
        $result = $all ? $result->all() : $result->limit(30)->page($page);
        $result = $result->toArray();

        if ($return) return $result;

        $this->restful->serialize->offsetSet('Result', ['data' => $result, 'total' => $total]);
    }

    public function delete(array $where = [])
    {
        $result = [];

        $unknown = array_diff(array_keys($where), $this->model->schema()->columns());
        if ($unknown) {
            foreach ($unknown as $column) {
                unset($where[$column]);
            }
        }

        if ($where) {
            $result = $this->model->deleteAll($where);
        }

        return $result;
    }
}
