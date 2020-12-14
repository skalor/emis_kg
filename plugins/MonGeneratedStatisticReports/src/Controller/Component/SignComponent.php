<?php
namespace MonGeneratedStatisticReports\Controller\Component;

use Cake\Controller\Component;
use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;

class SignComponent extends Component
{
    public $components = ['MonGeneratedStatisticReports.SignApi'];
    private $controller;
    private $model;
    private $modelAssociations;

    public function startup(Event $event)
    {
        $this->controller = $event->subject();
        $modelClass = $this->controller->modelClass;
        if ($this->request->action === 'sign' && !$modelClass) {
            $this->signAlert(__('Model not found'), 'error');
            return $this->controller->redirect($this->request->referer());
        }
        $this->model = TableRegistry::get($modelClass);
        $contain = [];
        foreach ($this->model->associations() as $association) {
            $contain[$association->foreignKey() . '.' . $association->property()] = $association->name();
        }
        $this->modelAssociations = $contain;
    }
    
    public function getUserData($userId)
    {
        $data = $this->request->data;
        $user = TableRegistry::get('User.Users')->get($userId);
        $staff = TableRegistry::get('Institution.Staff')->find()->where(['staff_id' => $user->id])->first();
        if ($staff) {
            $institution = TableRegistry::get('Institution.Institutions')->find()->where(['id' => $staff->institution_id])->first();
            $ipin = $institution ? $institution->pin : null;
        } else {
            $ipin = $user->institution_pin;
        }
        
        return [
            'uacp' => $user->is_acp,
            'upin' => $user->pin,
            'ipin' => $ipin,
            'pcode' => isset($data['pin_code']) ? $data['pin_code'] : null,
            'chids' => isset($data['checked_ids']) ? $data['checked_ids'] : null
        ];
    }
    
    public function checkPermissions(array $userData)
    {
        if (!$userData['uacp']) {
            $this->signAlert(__('You does not have permissions'), 'error');
            return false;
        } else if (!$userData['upin']) {
            $this->signAlert(__('You does not have pin number'), 'error');
            return false;
        } else if (!$userData['ipin']) {
            $this->signAlert(__('Organization does not have pin number'), 'error');
            return false;
        } else if (!$userData['pcode']) {
            $this->signAlert(__('Please type your pin code'), 'error');
            return false;
        } else if (!$userData['chids']) {
            $this->signAlert(__('Select items before signing'));
            return false;
        }
        
        return true;
    }

    public function sign()
    {
        $controller = $this->controller;
        $signApi = $this->SignApi->setControllerName($controller->name);
        $user = $controller->Auth->user();
        $userData = $this->getUserData($user['id']);
        $checkPermissions = $this->checkPermissions($userData);
        if (!$checkPermissions) {
            return $controller->redirect($this->request->referer());
        }
        
        $getAuthMethod = json_decode($signApi->getAuthMethod($userData['upin'], $userData['ipin']), true);
        if (!$getAuthMethod || !isset($getAuthMethod['isActive']) || !$getAuthMethod['isActive']) {
            $text = isset($getAuthMethod['errorMessage'])
                ? $getAuthMethod['errorMessage']
                : __('Can not connect to Infocom. Please try later!');
            $this->signAlert($text, 'error');
            return $controller->redirect($this->request->referer());
        }

        $auth = json_decode($signApi->auth($userData['upin'], $userData['ipin'], $userData['pcode']), true);
        if (!$auth || !isset($auth['token']) || !$auth['token']) {
            $text = isset($auth['errorMessage'])
                ? $auth['errorMessage']
                : __('Can not authorize to Infocom. Please check your pin code for validity or try later!');
            $this->signAlert($text, 'error');
            return $controller->redirect($this->request->referer());
        }

        $finded = $this->model->find()->contain(array_values($this->modelAssociations))
            ->where([$this->model->aliasField('id') . ' IN' => array_keys($userData['chids'])])
            ->all();

        foreach ($finded as $entity) {
            if (!$entity->file_content) {
                continue;
            }

            $hash = md5(stream_get_contents($entity->file_content));
            if (!$hash) {
                $this->signAlert(__('Can not generate md5 hash. Please check your file!'), 'error');
                return $controller->redirect($this->request->referer());
            }

            $getForHash = json_decode($signApi->getForHash($hash, $auth['token']), true);
            if (!$getForHash || !isset($getForHash['sign']) || !$getForHash['sign']) {
                $this->signAlert(__('Can not get signature from Infocom. Please try later!'), 'error');
                return $controller->redirect($this->request->referer());
            }

            $entity->is_signed = true;
            $entity->signed_by_id = $user['id'];
            $entity->file_name_hash = $user['username'] . '_' . time() . '.txt';
            $entity->file_content_hash = $getForHash['sign'];
            $this->model->beforeSaveCondition = false;
            $this->model->afterSaveCommitCondition = false;
            $this->model->save($entity);

            $this->signAlert(__('Signing completed successfully'), 'success');
        }

        return $controller->redirect($this->request->referer());
    }

    private function signAlert(string $message, string $type = 'info')
    {
        $session = $this->request->session();
        if (!$session->check('alert')) {
            $session->write('alert', []);
        }

        $alerts = $session->read('alert');
        array_push($alerts, ['type' => $type, 'message' => $message]);
        $session->write('alert', $alerts);
    }
}
