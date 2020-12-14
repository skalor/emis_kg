<?php
namespace StatisticReportHistory\Controller\Component;

use Cake\Controller\Component;
use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;

class SignComponent extends Component
{
    public $components = ['Institution.OepApi'];
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

    public function sign()
    {
        $controller = $this->controller;
        $data = $this->request->data;

        $user = TableRegistry::get('User.Users')->get($controller->Auth->user('id'));
        if (!$user->is_acp) {
            $this->signAlert(__('You does not have permissions'), 'error');
            return $controller->redirect($this->request->referer());
        }

        if (isset($data['checked_ids'])) {
            if (!isset($data['pin_code']) || !$data['pin_code']) {
                $this->signAlert(__('Please type your pin code'), 'error');
                return $controller->redirect($this->request->referer());
            }
            if (!$user->get('pin') || !$user->get('institution_pin')) {
                $this->signAlert(__('You does not have your pin or institution pin numbers'), 'error');
                return $controller->redirect($this->request->referer());
            }

            $getAuthMethod = json_decode($this->OepApi->getAuthMethod($user->get('pin'), $user->get('institution_pin')), true);
            if (!$getAuthMethod || !$getAuthMethod['isActive']) {
                $text = __('Can not connect to Infocom. Please try later!');
                isset($getAuthMethod['errorMessage']) ? $text = $getAuthMethod['errorMessage'] : null;
                $this->signAlert($text, 'error');
                return $controller->redirect($this->request->referer());
            }
            $auth = json_decode($this->OepApi->auth($user->get('pin'), $user->get('institution_pin'), $data['pin_code']), true);
            if (!$auth || !isset($auth['token']) || !$auth['token']) {
                $text = __('Can not authorize to Infocom. Please check your pin code for validity or try later!');
                isset($auth['errorMessage']) ? $text = $auth['errorMessage'] : null;
                $this->signAlert($text, 'error');
                return $controller->redirect($this->request->referer());
            }

            $finded = $this->model->find()->contain(array_values($this->modelAssociations))
                ->where([$this->model->aliasField('id') . ' IN' => array_keys($data['checked_ids'])])
                ->all();

            foreach ($finded as $key => $value) {
                if (!$value->file_content) {
                    continue;
                }

                $hash = md5(stream_get_contents($value->file_content));
                if (!$hash) {
                    $this->signAlert(__('Can not generate md5 hash. Please check your file!'), 'error');
                    return $controller->redirect($this->request->referer());
                }

                $getForHash = json_decode($this->OepApi->getForHash($hash, $auth['token']), true);
                if (!$getForHash || !isset($getForHash['sign']) || !$getForHash['sign']) {
                    $this->signAlert(__('Can not get signature from Infocom. Please try later!'), 'error');
                    return $controller->redirect($this->request->referer());
                }

                $value->is_signed = true;
                $value->signed_by_id = $user->get('id');
                $value->file_name_hash = $user->get('username') . '_' . time() . '.txt';
                $value->file_content_hash = $getForHash['sign'];
                $this->model->save($value);

                $this->signAlert(__('Signing completed successfully'), 'success');
            }
        } else {
            $this->signAlert(__('Select items before signing'));
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
