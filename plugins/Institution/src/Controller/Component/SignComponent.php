<?php
namespace Institution\Controller\Component;

use Cake\Controller\Component;
use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class SignComponent extends Component
{
    public $components = ['MonGeneratedStatisticReports.SignApi'];
    private $controller;
    private $model;
    private $modelAssociations;
    private $naming;

    public function startup(Event $event)
    {
        $this->controller = $event->subject();
        $modelClass = $this->request->data('sign_history_type');
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
        $this->naming = $modelClass . '_' . $this->controller->Auth->user('username');
    }

    public function sign(bool $insertToSignedDocs = true)
    {
        $controller = $this->controller;
        $data = $this->request->data;
        $signApi = $this->SignApi->setControllerName($controller->name);

        if (!isset($data['institution_id']) || !$data['institution_id']) {
            $this->signAlert(__('institution_id is empty'), 'error');
            return $controller->redirect($this->request->referer());
        }

        $user = TableRegistry::get('User.Users')->get($controller->Auth->user('id'));
        $institution = TableRegistry::get('Institution.Institutions')->find()
            ->where(['id' => $this->request->data['institution_id']])->first();

        if (!$user->is_acp || !$user->pin) {
            $this->signAlert(__('You does not have pin number or you does not have permissions'), 'error');
            return $controller->redirect($this->request->referer());
        }

        if (!$institution || $institution && !$institution->pin) {
            $this->signAlert(__('Organization does not have pin number'), 'error');
            return $controller->redirect($this->request->referer());
        }

        if (isset($data['checked_ids'])) {
            if (!isset($data['pin_code']) || !$data['pin_code']) {
                $this->signAlert(__('Please type your pin code'), 'error');
                return $controller->redirect($this->request->referer());
            }

            $getAuthMethod = json_decode($signApi->getAuthMethod($user->get('pin'), $institution->get('pin')), true);
            if (!$getAuthMethod || !$getAuthMethod['isActive']) {
                $text = __('Can not connect to Infocom. Please try later!');
                isset($getAuthMethod['errorMessage']) ? $text = $getAuthMethod['errorMessage'] : null;
                $this->signAlert($text, 'error');
                return $controller->redirect($this->request->referer());
            }

            $auth = json_decode($signApi->auth($user->get('pin'), $institution->get('pin'), $data['pin_code']), true);
            if (!$auth || !isset($auth['token']) || !$auth['token']) {
                $text = __('Can not authorize to Infocom. Please check your pin code for validity or try later!');
                isset($auth['errorMessage']) ? $text = $auth['errorMessage'] : null;
                $this->signAlert($text, 'error');
                return $controller->redirect($this->request->referer());
            }

            $finded = $this->model->find()->contain(array_values($this->modelAssociations))
                ->where([$this->model->aliasField('id') . ' IN' => array_keys($data['checked_ids'])])
                ->all();

            $directory = WWW_ROOT . 'SignedDocs/';
            $html = $this->getHtmlForPdf($controller->Page->getElements()->getArrayCopy(), $finded->toArray());

            if (!file_exists($directory)) {
                mkdir($directory, 0775);
            }

            $mpdf = new Mpdf();
            $mpdf->WriteHTML($html);
            $pdfFileName = $directory . $this->naming . '_pdf_' . time() . '.pdf';
            $mpdf->Output($pdfFileName, Destination::FILE);

            if (file_exists($pdfFileName)) {
                $getForHash = json_decode($signApi->getForHash(md5_file($pdfFileName), $auth['token']), true);
                if (!$getForHash || !isset($getForHash['sign']) || !$getForHash['sign']) {
                    $this->signAlert(__('Can not get signature from Infocom. Please try later!'), 'error');
                    unlink($pdfFileName);
                    return $controller->redirect($this->request->referer());
                }

                $signFileName = $directory . $this->naming . '_sign_' . time() . '.txt';
                file_put_contents($signFileName, $getForHash['sign']);

                if (file_exists($signFileName)) {
                    $docInserted = $insertToSignedDocs ? $this->insertToSignedDocuments($pdfFileName, $signFileName) : null;
                    if (is_null($docInserted) || $docInserted) {
                        $this->updateModelRecords($finded->toArray(), $docInserted);
                        $this->signAlert(__('Signing completed successfully'), 'success');
                    } else {
                        $this->signAlert(__('Can not insert to signed documents'), 'error');
                    }
                    unlink($signFileName);
                } else {
                    $this->signAlert(__('Can not create sign file'), 'error');
                }
                unlink($pdfFileName);
            } else {
                $this->signAlert(__('Can not create file'), 'error');
            }

        } else {
            $this->signAlert(__('Select items before signing'));
        }

        return $controller->redirect($this->request->referer());
    }

    private function insertToSignedDocuments(string $pdfFileName, string $signFileName)
    {
        $institutionId = isset($this->request->data['institution_id']) ? $this->request->data['institution_id'] : null;
        if (!$institutionId) {
            return false;
        }

        $nowDate = date('Y-m-d');
        $pdfFile = file_get_contents($pdfFileName);
        $signFile = file_get_contents($signFileName);
        $table = TableRegistry::get('Institution.InstitutionSignedDocuments');
        $entity = $table->newEntity();
        $entity->name = $this->naming . '_' . time();
        $entity->institution_id = $institutionId;
        $entity->date_on_file = $nowDate;
        $entity->file_name = basename($pdfFileName);
        $entity->file_content = $pdfFile;
        $entity->file_name_hash = basename($signFileName);
        $entity->file_content_hash = $signFile;
        $saved = $table->save($entity);
        if ($saved) {
            return $saved;
        }

        return false;
    }

    private function updateModelRecords(array $data, ?Entity $document)
    {
        if (!$document) {
            $documentId = null;
        } else {
            $documentId = $document->get('id');
        }

        foreach ($data as $key => $item) {
            if ($item instanceof Entity) {
                $item->is_signed = true;
                $item->signed_by_id = $this->controller->Auth->user('id');
                $item->signed_document_id = $documentId;
                $this->model->save($item);
            }
        }
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

    private function getHtmlForPdf(array $elements, array $data)
    {
        $customColumns = [__('Signed On') => date('Y-m-d')];
        $excludedFields = ['id', 'is_signed', 'signed_link', 'signed_document_id'];

        // styles
        $html = '<style>
            table {
                border: 1px solid #eee;
            }
            thead tr th {
                background-color: #eee;
            }
            th, td {
                padding: 5px 15px;
            }
        </style>';

        $html .= '<table>';

        // table header
        $html .= '<thead><tr>';
        foreach ($elements as $element) {
            if (in_array($element->getKey(), $excludedFields)) {
                $element->setVisible(false);
            }
            if ($element->isVisible()) {
                $html .= '<th>' . $element->getLabel() . '</th>';
            }
        }
        foreach ($customColumns as $columnName => $columnValue) {
            $html .= '<th>' . $columnName . '</th>';
        }
        $html .= '</tr></thead>';

        $modelAssociations = [];
        if ($this->modelAssociations) {
            foreach (array_keys($this->modelAssociations) as $modelAssociation) {
                $exploded = explode('.', $modelAssociation);
                if (isset($exploded[0]) && isset($exploded[1])) {
                    $modelAssociations[$exploded[0]] = $exploded[1];
                }
            }
        }

        // table body
        $html .= '<tbody>';
        foreach ($data as $item) {
            $html .= '<tr>';
            foreach ($elements as $element) {
                if ($element->isVisible()) {
                    $field = $element->getKey();
                    $value = $item->$field;

                    if (
                        $modelAssociations && in_array($field, array_keys($modelAssociations))
                        && $item->{$modelAssociations[$field]}
                    ) {
                        $value = $item->{$modelAssociations[$field]}->name;
                    }

                    if ($field === 'signed_by_id') {
                        $value = $this->controller->Auth->user('name');
                    }

                    if ($field === 'name') {
                        if ($item->security_user_id && isset($modelAssociations['security_user_id']) && $item->{$modelAssociations['security_user_id']}) {
                            $value = $item->{$modelAssociations['security_user_id']}->name;
                        } else if ($item->institution_id && isset($modelAssociations['institution_id']) && $item->{$modelAssociations['institution_id']}) {
                            $value = $item->{$modelAssociations['institution_id']}->name;
                        }
                    }

                    $html .= '<td>' . $value . '</td>';
                }
            }
            foreach ($customColumns as $columnName => $columnValue) {
                $html .= '<td>' . $columnValue . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody>';

        $html .= '</table>';

        return $html;
    }
}
