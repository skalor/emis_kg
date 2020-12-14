<?php
namespace Import\Model\Behavior;

use ArrayObject;
use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\Behavior;
use Cake\ORM\ResultSet;
use Cake\I18n\Time;
use Cake\Utility\Inflector;
use ControllerAction\Model\Traits\EventTrait;
use Cake\I18n\I18n;

class ImportLinkBehavior extends Behavior
{
    protected $_defaultConfig = [
    ];

    public function initialize(array $config)
    {
        $importModel = $this->config('import_model');
        if (empty($importModel)) {
            $this->config('import_model', 'Import'.$this->_table->alias());
        };
    }

    public function implementedEvents()
    {
        $events = parent::implementedEvents();
        $events['Model.custom.onUpdateToolbarButtons'] = ['callable' => 'onUpdateToolbarButtons', 'priority' => 1];

        if ($this->isCAv4()) {
            $events['ControllerAction.Model.index.afterAction'] = ['callable' => 'indexAfterActionImportv4'];
            $events['ControllerAction.Model.view.afterAction'] = ['callable' => 'viewAfterActionImportv4'];
        }

        return $events;
    }

    //using after action for ordering of toolbar buttons (because export also using afteraction)
    public function indexAfterActionImportv4(Event $event, Query $query, $data, ArrayObject $extra)
    {
        if ($this->_table->request->action != 'Surveys') {
            $attr = $this->_table->getButtonAttr();
            $customButton = [];
            $customButton['url'] = $this->_table->url('index');
            $customButton['url']['action'] = $this->config('import_model');
            $customButton['url'][0] = 'add';
            $this->generateImportButton($extra['toolbarButtons'], $attr, $customButton);
        }
    }

    public function viewAfterActionImportv4(Event $event, Entity $entity, ArrayObject $extra)
    {
        if ($this->_table->request->action == 'Surveys') {
            $attr = $this->_table->getButtonAttr();
            $customButton = [];
            $customButton['url'] = $this->_table->url('view');
            $customButton['url']['action'] = 'Import'.$this->_table->alias();
            $this->generateImportButton($extra['toolbarButtons'], $attr, $customButton);
        }
    }

    public function onUpdateToolbarButtons(Event $event, ArrayObject $buttons, ArrayObject $toolbarButtons, array $attr, $action, $isFromModel)
    {
        $customButton = [];
        switch ($action) {
            case 'index':
                if ($buttons['index']['url']['action']=='Surveys') {
                    break;
                }
                $customButton['url'] = $this->_table->ControllerAction->url('add');
                $customButton['url']['action'] = $this->config('import_model');

                $this->generateImportButton($toolbarButtons, $attr, $customButton);
                break;

            case 'view':
                if ($buttons['view']['url']['action']!='Surveys') {
                    break;
                }
                $customButton['url'] = $buttons['view']['url'];
                $customButton['url']['action'] = 'Import'.$this->_table->alias();

                $this->generateImportButton($toolbarButtons, $attr, $customButton);
                break;
        }
    }

    private function generateImportButton(ArrayObject $toolbarButtons, array $attr, array $customButton)
    {
        if (array_key_exists('_ext', $customButton['url'])) {
            unset($customButton['url']['_ext']);
        }
        if (array_key_exists('pass', $customButton['url'])) {
            unset($customButton['url']['pass']);
        }
        if (array_key_exists('paging', $customButton['url'])) {
            unset($customButton['url']['paging']);
        }
        if (array_key_exists('filter', $customButton['url'])) {
            unset($customButton['url']['filter']);
        }
        $customButton['url'][0] = 'add';

        $AccessControl = $this->_table->controller->AccessControl;
        $permission = $AccessControl->check($customButton['url']);
        if ($permission) {
            $customButton['type'] = 'button';
            $customButton['label'] = '<svg width="20" height="23" viewBox="0 0 20 23" fill="none" xmlns="http://www.w3.org/2000/svg"> <path fill-rule="evenodd" clip-rule="evenodd" d="M17.5 12.1667V19.6667C17.5 20.5871 16.7538 21.3333 15.8333 21.3333H4.16667C3.24619 21.3333 2.5 20.5871 2.5 19.6667V12.1667H4.16667V19.6667H15.8333V12.1667H17.5Z" fill="#293845"/> <path d="M6.8 8.64166L8.4 0.849998L11.6 0.849999L13.2 8.64166L6.8 8.64166Z" fill="#009966"/> <path d="M10 15.725L3.0718 8.28751L16.9282 8.28751L10 15.725Z" fill="#009966"/> </svg>';
            $customButton['attr'] = $attr;
            $customButton['attr']['title'] = __('Import');

            $toolbarButtons['import'] = $customButton;
        }
    }

    private function isCAv4()
    {
        return isset($this->_table->CAVersion) && $this->_table->CAVersion=='4.0';
    }
}
