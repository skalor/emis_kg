<?php
namespace Institution\Model\Behavior;

use ArrayObject;
use Cake\Event\Event;
use Cake\ORM\Behavior;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Mpdf\Mpdf;
use Mpdf\QrCode\QrCode;
use Mpdf\QrCode\Output;
use Mpdf\Output\Destination;
use Cake\Datasource\ConnectionManager;

class UserReferenceGenerateBehavior extends Behavior
{
    private $referenceTemplateModel;
    private $userAttachmentsModel;
    private $classesModel;
    private $studentsModel;
    private $staffModel;
    private $type;
    private $file_number;

    private function isCAv4()
    {
        return isset($this->_table->CAVersion) && $this->_table->CAVersion == '4.0';
    }

    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->referenceTemplateModel = TableRegistry::get('MonTemplateReports.MonTemplateReports');
        $this->userAttachmentsModel = TableRegistry::get('User.Attachments');
        $this->classesModel = TableRegistry::get('Institution.InstitutionClasses');
        $this->studentsModel = TableRegistry::get('Institution.Students');
        $this->staffModel = TableRegistry::get('Institution.Staff');
        $this->type = $this->config('type');
    }

    public function implementedEvents()
    {
        $events = parent::implementedEvents();
        if ($this->isCAv4()) {
            $events['ControllerAction.Model.beforeAction'] = 'beforeAction';
            $events['ControllerAction.Model.referenceGenerate'] = 'referenceGenerate';
        }
        return $events;
    }

    public function beforeAction(Event $event, ArrayObject $extra)
    {
        $action = $this->_table->action;
        if (in_array($action, $this->config('pages') ? $this->config('pages') : ['view'])) {
            $url = $this->_table->url($action);
            $url[0] = 'referenceGenerate';
            $url['referenceType'] = 0;
            $toolbarButtons = isset($extra['toolbarButtons']) ? $extra['toolbarButtons'] : [];
            $typeId = 0;
            if ($this->type === 'student') {
                $typeId = 3;
            } else if ($this->type === 'staff') {
                $typeId = 2;
            }
            $refTemplates = $this->referenceTemplateModel->find()->where(['type_id' => $typeId])->all();
            $referenceTypes = ['empty' => __('References not found')];
            if ($refTemplates) {
                $referenceTypes = [];
                foreach ($refTemplates as $refTemplate) {
                    $referenceTypes[$refTemplate->id] = $refTemplate->name;
                }
            }

            $toolbarButtons['referenceGenerate'] = [
                'type' => 'element',
                'element' => 'Institution.referenceGenerate',
                'data' => [
                    'url' => $url,
                    'referenceTypes' => $referenceTypes
                ],
                'options' => []
            ];
            $extra['toolbarButtons'] = $toolbarButtons;
        }
    }

    public function referenceGenerate(Event $event, ArrayObject $extra)
    {
        $alert = $this->_table->Alert;
        $alertOptions = ['type' => 'string', 'reset' => true];
        $controller = $event->subject()->_registry->getController();
        $request = $controller->request;
        $action = $request->action;
        $referer = $controller->referer();
        $queryString = $request->param('pass');
        $referenceType = $request->query('referenceType');

        if (
            $referenceType && $referenceType !== 'empty'
            && isset($queryString[1]) && $queryString[1]
        ) {
            $queryString = $this->_table->paramsDecode($queryString[1]);
            if (!isset($queryString['id']) || !$queryString['id']) {
                $alert->error(__('User ID not found in request'), $alertOptions);
                return $controller->redirect($referer);
            }

            $template = $this->referenceTemplateModel->get($referenceType);
            if (!$template) {
                $alert->error(__('Template not found'), $alertOptions);
                return $controller->redirect($referer);
            }

            $userData = [];
            if ($action === 'StudentUser') {
                $userData = $this->getStudent(['Students.student_id' => $queryString['id']]);
                if ($userData) {
                    if (is_null($userData['user']->InstitutionClassStudents['institution_class_id']) && $template->code == "CERTIFICATE FROM THE PLACE OF STUDY" ){
                        $alert->error(__('The student is not listed in any class, please put him in the appropriate class'), $alertOptions);
                        return $controller->redirect($referer);
                    }
                }
            } else if ($action === 'StaffUser') {
                $userData = $this->getStaff(['Staff.staff_id' => $queryString['id']]);
            }

            $user = null;
            if (isset($userData['user']) && $userData['user']) {
                $user = $userData['user'];
                isset($userData['referer']) && $userData['referer'] ? $referer = $userData['referer'] : null;
            }

            if (!$user) {
                $alert->error(__('User not found'), $alertOptions);
                return $controller->redirect($referer);
            }
            $this->file_number = $this->generateCode(5);
            $template->content = $this->appendVariables($template->content, $user);
            $result = $this->createUserPdfFile($template, $user);
            if ($result) {
                $referer = array_merge($referer, $result);
            } else {
                $alert->error(__('Can not create PDF file'), $alertOptions);
                $referer = $controller->referer();
            }
        } else {
            $alert->error(__('Empty reference type or empty pass arguments'), $alertOptions);
        }

        return $controller->redirect($referer);
    }

    private function getStudent(array $where)
    {
        $user = $this->studentsModel->find()->autoFields(true)->contain([
            'Users', 'Institutions'
        ])->join([
            'table' => 'institution_class_students',
            'alias' => 'InstitutionClassStudents',
            'type' => 'LEFT',
            'conditions' => 'InstitutionClassStudents.student_id = Students.student_id'
        ])->select([
            'InstitutionClassStudents.institution_class_id',
        ])->join([
            'table' => 'institution_classes',
            'alias' => 'InstitutionClasses',
            'type' => 'LEFT',
            'conditions' => 'InstitutionClasses.id = InstitutionClassStudents.institution_class_id'
        ])->select([
            'InstitutionClasses.name',
            'InstitutionClasses.capacity',
            'InstitutionClasses.total_male_students',
            'InstitutionClasses.total_female_students'
        ])->where($where)->first();

        return [
            'referer' => ['plugin' => 'Student', 'controller' => 'Students', 'action' => 'Attachments'],
            'user' => $user
        ];
    }

    private function getStaff(array $where)
    {
        $user = $this->staffModel->find()->autoFields(true)->contain([
            'Users', 'Institutions',
        ])->join([
            'table' => 'institution_positions',
            'alias' => 'InstitutionPositions',
            'type' => 'LEFT',
            'conditions' => 'InstitutionPositions.id = Staff.institution_position_id'
        ])->select([
            'InstitutionPositions.staff_position_title_id',
            'InstitutionPositions.staff_position_grade_id'
        ])->join([
            'table' => 'staff_position_titles',
            'alias' => 'StaffPositionTitles',
            'type' => 'LEFT',
            'conditions' => 'StaffPositionTitles.id = InstitutionPositions.staff_position_title_id'
        ])->select([
            'StaffPositionTitles.name',
        ])->join([
            'table' => 'institution_classes',
            'alias' => 'InstitutionClasses',
            'type' => 'LEFT',
            'conditions' => 'InstitutionClasses.staff_id = Staff.staff_id'
        ])->select([
            'InstitutionClasses.name',
            'InstitutionClasses.capacity',
            'InstitutionClasses.total_male_students',
            'InstitutionClasses.total_female_students'
        ])->where($where)->first();

        return [
            'referer' => ['plugin' => 'Staff', 'controller' => 'Staff', 'action' => 'Attachments'],
            'user' => $user
        ];
    }

    private function appendVariables(string $template, Entity $user)
    {
        preg_match_all('/\$__([a-zA-Z]+)__/', $template, $matches);
        $templateVariables = isset($matches[0]) ? $matches[0] : [];
        $templateVariablesNames = isset($matches[1]) ? $matches[1] : [];
        foreach ($templateVariables as $key => $templateVariable) {
            switch ($templateVariablesNames[$key]) {
                case 'name':
                    $template = $this->variableReplace($template, $templateVariable, $user->user);
                    break;
                case 'institutionName':
                    $template = $this->variableReplace($template, $templateVariable, $user->institution);
                    break;
                case 'className':
                    $template = $this->variableReplace($template, $templateVariable, $user->InstitutionClasses);
                    break;
                case 'positionName':
                    $template = $this->variableReplace($template, $templateVariable, $user->StaffPositionTitles);
                    break;
                case 'startDate':
                    $template = $this->variableReplace($template, $templateVariable, $user->start_date->format('Y-m-d'));
                    break;
                case 'fullNameDirector':
                    $template = $this->variableReplace($template, $templateVariable, 'Иванов Иван Иванович');
                    break;
                case 'endDate':
                    $template = $this->variableReplace($template, $templateVariable, $user->end_date->format('Y-m-d'));
                    break;
                case 'fileNumber':
                    $template = $this->variableReplace($template, $templateVariable, $this->file_number);
                    break;
                case 'QR':
                    $qrCode = '<barcode class="barcode" code="'.htmlspecialchars(Router::url('/CertCheck/'.$this->file_number, true)).'" disableborder="1" error="M" size="1.2" type="QR"></barcode>';
                    $template = $this->variableReplace($template, $templateVariable, $qrCode);
                    break;
                default:
                    $template = $this->variableReplace($template, $templateVariable, new Entity());
            }
        }

        return $template;
    }

    private function variableReplace(string $template, string $templateVariable, $entity = null, string $entityKey = 'name', string $variableDefaultValue = '&nbsp;')
    {
        $variableValue = $variableDefaultValue;
        if ($entity instanceof Entity && $entity->$entityKey) {
            $variableValue = $entity->$entityKey;
        } else if (is_array($entity) && isset($entity[$entityKey])) {
            $variableValue = $entity[$entityKey];
        } else if (is_string($entity) && $entity) {
            $variableValue = $entity;
        }

        return str_replace($templateVariable, $variableValue, $template);
    }

    private function createUserPdfFile(Entity $template, Entity $user)
    {
        $directory = WWW_ROOT . 'References/';
        if (!file_exists($directory)) {
            mkdir($directory, 0775);
        }

        $name = $template->name . '_' . time();
        $pdfFileName = $directory . $name . '.pdf';

        $mpdf = new Mpdf();
        $mpdf->SetTitle($name);
        $mpdf->WriteHTML($template->content);
        $mpdf->Output($pdfFileName, Destination::FILE);

        if (file_exists($pdfFileName)) {
            $entity = $this->userAttachmentsModel->newEntity();
            $entity->security_user_id = $user->user->id;
            $entity->file_number = $this->file_number;
            $entity->name = $name;
            $entity->date_on_file = date('Y-m-d');
            $entity->file_name = basename($pdfFileName);
            $entity->file_content = file_get_contents($pdfFileName);
            unlink($pdfFileName);
            $result = $this->userAttachmentsModel->save($entity);
            if (!$result['errors'] && $result->get('id')) {
                return [
                    'view',
                    $this->_table->paramsEncode(['id' => $result->get('id')])
                ];
            }
        }

        return false;
    }

    private function generateCode($code_length = 5){
        $connection = ConnectionManager::get('default');
        $results = $connection->execute("SELECT GENERATE_CODE($code_length) as `code`")->fetchAll('assoc');
        return !empty($results[0]['code']) ? $results[0]['code'] : '';
    }
}
