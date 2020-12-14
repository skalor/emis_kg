<?php
namespace MonAPI\Controller\Component;

use Cake\Event\Event;
use Cake\Controller\Component;

class UserComponent extends Component
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
        $this->model = $this->restful->instantiateModel('User.Users');
    }
    
    public function add(array $where = null)
    {
        if (!$this->request->data || !$where) {
            return [];
        }

        $recordExist = $this->get($where);

        if ($recordExist) {
            return ['data' => $recordExist[0], 'error' => []];
        }

        $this->restful->model = $this->model;
        $oeID = $this->model->getUniqueOpenemisId();
        $oePassw = $this->model->generatePassword();
        $this->request->data['openemis_no'] = $oeID;
        $this->request->data['username'] = $oeID;
        $this->request->data['password'] = $oePassw;
        $result = $this->restful->add(false);

        if (!$result['error']) {
            $this->controller->Restful->logging(
                'users_' . date('Y-m-d'),
                "username: $oeID, password: $oePassw, first_name: {$this->request->data['first_name']}, last_name: {$this->request->data['last_name']}, date_of_birth: {$this->request->data['date_of_birth']}, code_okpo: {$this->request->data['code_okpo']}"
            );
        }

        return $result;
    }
    
    public function update(array $where = null, bool $update = false)
    {
        if (!$this->request->data || !$where) {
            return [];
        }

        $recordExist = $this->get($where);

        if ($recordExist) {
            $recordExist = $recordExist[0];
            foreach ($this->request->data as $column => $value) {
                $lowerColumn = strtolower($column);
                if (
                    $lowerColumn != 'openemis_no' && $lowerColumn != 'username' && !$recordExist->$lowerColumn ||
                    $lowerColumn != 'openemis_no' && $lowerColumn != 'username' && $this->controller->Auth->user('id') === $recordExist->get('created_user_id') && $update
                ) {
                    $recordExist->$lowerColumn = $value;
                }
            }
            return ['data' => $this->model->save($recordExist), 'error' => $recordExist->errors()];
        }
        
        return ['exist' => false];
    }
    
    public function get(array $where = null)
    {
        $result = [];
        
        if ($where) {
            $result = $this->model->find()->where($where)->all()->toArray();
        }
        
        return $result;
    }
    
    public function delete(array $where = null)
    {
        $result = [];
        
        if ($where) {
            $result = $this->model->deleteAll($where);
        }
        
        return $result;
    }
    
    public function addContact(int $userId, bool $update = false, int $contactTypeId = 1, int $contactOptionId = 1)
    {
        $data = $this->request->data;
        $result = [];
        
        if ($userId && $contactTypeId && $contactOptionId && $data) {
            $contacts = $this->model->contacts;
            $contact = $contacts->find()->where([
                'security_user_id' => $userId,
                'contact_type_id' => $contactTypeId
            ])->first();
            
            if ($contact) {
                if (!$contact->value || $this->controller->Auth->user('id') === $contact->get('created_user_id') && $update) {
                    $contact->value = $data['mobile'];
                    $result = ['data' => $contacts->save($contact), 'error' => $contact->errors()];
                }
            } else {
                $this->restful->model = $contacts;
                $this->request->data['security_user_id'] = $userId;
                $this->request->data['contact_type_id'] = $contactTypeId;
                $this->request->data['contact_option_id'] = $contactOptionId;
                $this->request->data['value'] = $data['mobile'];
                $this->request->data['preferred'] = 1;
                $result = $this->restful->add(false);
            }
        }
        
        return $result;
    }
}
