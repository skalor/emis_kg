<?php
namespace OpenEmis\Model\Behavior;

use ArrayObject;
use Cake\ORM\Behavior;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\ResultSet;
use Cake\Event\Event;

class OpenEmisBehavior extends Behavior
{
    public function initialize(array $config)
    {
        parent::initialize($config);
    }

    public function implementedEvents()
    {
        $events = parent::implementedEvents();
        $events['ControllerAction.Model.beforeAction'] = ['callable' => 'beforeAction', 'priority' => 4];
        $events['ControllerAction.Model.afterAction'] = ['callable' => 'afterAction', 'priority' => 100];
        $events['ControllerAction.Model.index.afterAction'] = ['callable' => 'indexAfterAction', 'priority' => 4];
        $events['ControllerAction.Model.view.afterAction'] = ['callable' => 'viewAfterAction', 'priority' => 4];
        $events['ControllerAction.Model.add.afterSave'] = ['callable' => 'addAfterSave', 'priority' => 4];
        $events['ControllerAction.Model.edit.afterSave'] = ['callable' => 'editAfterSave', 'priority' => 4];
        $events['ControllerAction.Model.edit.afterAction'] = ['callable' => 'editAfterAction', 'priority' => 4];
        $events['ControllerAction.Model.delete.afterAction'] = ['callable' => 'deleteAfterAction', 'priority' => 4];
        $events['ControllerAction.Model.transfer.afterAction'] = ['callable' => 'transferAfterAction', 'priority' => 4];
        return $events;
    }

    public function beforeAction(Event $event, ArrayObject $extra)
    {
        $action = $this->_table->action;
        switch ($action) {
            case 'index':
                $extra['elements']['table'] = ['name' => 'OpenEmis.ControllerAction/index', 'order' => 5];
                $extra['elements']['pagination'] = ['name' => 'OpenEmis.pagination', 'order' => 8];
                break;
            case 'view':
                $extra['elements']['view'] = ['name' => 'OpenEmis.ControllerAction/view', 'order' => 5];
                break;
            case 'edit':
            case 'add':
            case 'remove':
            case 'transfer':
                $extra['config']['form'] = true;
                $extra['elements']['edit'] = ['name' => 'OpenEmis.ControllerAction/edit', 'order' => 5];
                break;
            default:
                break;
        }
        $form = false; // deprecated
        if ($action == 'index') {
            $form = true;
        } // deprecated
        $this->_table->controller->set('form', $form); // deprecated

        $this->initializeButtons($extra);
    }

    public function afterAction(Event $event, ArrayObject $extra)
    {
        $model = $this->_table;
        if ($model->action == 'index' || $model->action == 'view') {
            $modal = [];
            $modal['title'] = $model->getHeader($model->alias()); //$modal['title'] = $model->alias();
            $modal['content'] = __('All associated information related to this record will also be removed.');
            $modal['content'] .= '<br><br>';
            $modal['content'] .= __('Are you sure you want to delete this record?');

            $modal['form'] = [
                'model' => $model,
                'formOptions' => ['type' => 'delete', 'url' => $model->url('remove')],
                'fields' => ['primaryKey' => ['type' => 'hidden', 'id' => 'recordId', 'unlockField' => true]]
            ];

            $modal['buttons'] = [
                '<button type="submit" class="btn btn-default">' . __('Delete') . '</button>'
            ];
            $modal['cancelButton'] = true;

            if (!isset($model->controller->viewVars['modals'])) {
                $model->controller->set('modals', ['delete-modal' => $modal]);
            } else {
                $modals = array_merge($model->controller->viewVars['modals'], ['delete-modal' => $modal]);
                $model->controller->set('modals', $modals);
            }
        }
        // deprecated
        $model->controller->set('action', $this->_table->action);
        $model->controller->set('indexElements', []);
        // end deprecated
        $this->attachEntityInfoToToolBar($extra);

        $access = $model->AccessControl;
        $toolbarButtons = $extra['toolbarButtons'];
        foreach ($toolbarButtons->getArrayCopy() as $key => $buttons) {
            if (array_key_exists('url', $buttons)) {
                if (!$model->controller->request->is('ajax') && $buttons['url'] != '#' && !$access->check($buttons['url'])) {
                    $toolbarButtons->offsetUnset($key);
                }
            }
        }

        $indexButtons = $extra['indexButtons'];
        foreach ($indexButtons->getArrayCopy() as $key => $buttons) {
            if ($buttons['url'] != '#' && array_key_exists('url', $buttons)) {
                if (!$model->controller->request->is('ajax') && !$access->check($buttons['url'])) {
                    $indexButtons->offsetUnset($key);
                }
            }
        }

        $extra['toolbarButtons'] = $toolbarButtons;
        $extra['indexButtons'] = $indexButtons;

        if ($model->actions('reorder') && $indexButtons->offsetExists('edit')) {
            $model->controller->set('reorder', true);
        }

        if ($extra->offsetExists('indexButtons')) {
            $model->controller->set('indexButtons', $extra['indexButtons']);
        }
        if ($extra['toolbarButtons']->offsetExists('back')) {
            $model->controller->set('backButton', $extra['toolbarButtons']['back']);
        }
    }

    private function attachEntityInfoToToolBar($extra)
    {
        $model = $this->_table;
        // further modification on toolbar buttons attributes where entity information will be available
        if ($extra->offsetExists('toolbarButtons')) {
            $toolbarButtons = $extra['toolbarButtons'];
            $entity = $extra['entity'];
            $isViewPage = $model->action == 'view';

            if ($isViewPage) {
                $isDeleteButtonEnabled = $toolbarButtons->offsetExists('remove');
                $isNotTransferOperation = $model->actions('remove') != 'transfer';
                $isNotRestrictOperation = $model->actions('remove') != 'restrict';
                $primaryKey = $model->primaryKey();

                $ids = [];
                if (is_array($primaryKey)) {
                    foreach ($primaryKey as $key) {
                        $ids[$key] = $entity->getOriginal($key);
                    }
                } else {
                    $ids[$primaryKey] = $entity->getOriginal($primaryKey);
                }

                $encodedIds = $model->paramsEncode($ids);

                if ($isDeleteButtonEnabled && $isNotTransferOperation && $isNotRestrictOperation) {
                    // not checking existence of entity in $extra so that errors will be shown if entity is removed unexpectedly
                    // to attach primary key to the button attributes for delete operation
                    if (array_key_exists('remove', $toolbarButtons)) {
                        $toolbarButtons['remove']['attr']['field-value'] = $encodedIds;
                    }
                }

                $isDownloadButtonEnabled = $toolbarButtons->offsetExists('download');
                if ($isDownloadButtonEnabled) {
                    $model = $this->_table;
                    if ($download = $model->actions('download')) {
                        $determineShow = $download['show'];
                        if (is_callable($download['show'])) {
                            $determineShow = $determineShow();
                        }
                    }
                    if ($determineShow) {
                        $toolbarButtons['download']['url'][] = $encodedIds;
                    } else {
                        $toolbarButtons->offsetUnset('download'); // removes download button
                    }
                }
            }

            $actions = ['index', 'add', 'edit', 'remove'];
            $disabledActions = [];
            foreach ($toolbarButtons as $action => $attr) {
                if (in_array($action, $actions) && !$model->actions($action)) {
                    $disabledActions[] = $action;
                }
            }
            foreach ($disabledActions as $action) {
                $toolbarButtons->offsetUnset($action);
            }
            $model->controller->set('toolbarButtons', $toolbarButtons);
        }
    }

    public function indexAfterAction(Event $event, Query $query, $resultSet, ArrayObject $extra)
    {
        if (count($resultSet) == 0) {
            $this->_table->Alert->info('general.noData');
        }
        $extra['config']['form'] = ['class' => ''];
    }

    public function viewAfterAction(Event $event, Entity $entity, ArrayObject $extra)
    {
        if (!$entity) {
            $this->_table->Alert->warning('general.notExists');
        }
    }

    public function addAfterSave(Event $event, Entity $entity, ArrayObject $data, ArrayObject $extra)
    {
        $model = $this->_table;
        $errors = $entity->errors();
        if (empty($errors)) {
            $model->Alert->success('general.add.success');
        } else {
            $model->Alert->error('general.add.failed');
        }
    }

    public function editAfterSave(Event $event, Entity $entity, ArrayObject $data, ArrayObject $options, ArrayObject $extra)
    {
        $model = $this->_table;
        $errors = $entity->errors();
        if (empty($errors)) {
            $model->Alert->success('general.edit.success');
        } else {
            $model->Alert->error('general.edit.failed');
        }
    }

    public function editAfterAction(Event $event, Entity $entity, ArrayObject $extra)
    {
        if (!$entity) {
            $this->_table->Alert->warning('general.notExists');
        }
    }

    public function deleteAfterAction(Event $event, Entity $entity, ArrayObject $extra)
    {
        $model = $this->_table;
        if ($model->request->is('delete') || $extra['forceDeleteRecord']) {
            if ($extra['result']) {
                if (isset($extra['Alert']['message'])) {
                    $model->Alert->success($extra['Alert']['message']);
                } else {
                    $model->Alert->success('general.delete.success');
                }
            } else {
                if (isset($extra['Alert']['message'])) {
                    $model->Alert->error($extra['Alert']['message']);
                } else {
                    $model->Alert->error('general.delete.failed');
                }
            }
        }
    }

    public function transferAfterAction(Event $event, Entity $entity, ArrayObject $extra)
    {
        $model = $this->_table;
        if ($model->request->is('delete')) {
            if ($extra['result']) {
                $model->Alert->success('general.delete.success');
            } else {
                if (empty($entity->convert_to)) {
                    $model->Alert->error('general.deleteTransfer.restrictDelete');
                    return $model->controller->redirect($model->url('transfer'));
                } else {
                    $model->Alert->error('general.delete.failed');
                }
            }
        }
    }

    private function initializeButtons(ArrayObject $extra)
    {
        $model = $this->_table;
        $controller = $model->controller;

        $toolbarButtons = new ArrayObject([]);
        $indexButtons = new ArrayObject([]);

        $backActions = ['add' => 'index', 'view' => 'index', 'edit' => 'view'];

        $toolbarAttr = [
            'class' => 'btn btn s',
            'data-toggle' => 'tooltip',
            'data-placement' => 'bottom',
            'escape' => false
        ];

        $indexAttr = ['role' => 'menuitem', 'tabindex' => '-1', 'escape' => false];

        $action = $model->action;

        $backAction = array_key_exists($action, $backActions) ? $backActions[$action] : $action;

        if ($action != 'index' && $model->actions($action)) {
            $toolbarButtons['back']['type'] = 'button';
            $toolbarButtons['back']['label'] = '<svg class="backSvg" width="23" height="24" viewBox="0 0 23 24" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M23 10.4999H5.50563L13.5412 2.11501L11.5 0L0 12L11.5 24L13.5268 21.8849L5.50563 13.4999H23V10.4999Z" fill="#004A51"/> </svg>';
            $toolbarButtons['back']['attr'] = $toolbarAttr;
            $toolbarButtons['back']['attr']['title'] = __('Back');

            if ($action == 'remove' && ($model->actions('remove') == 'transfer' || $model->actions('remove') == 'restrict')) {
                $toolbarButtons['list']['url'] = $model->url('index', 'QUERY');
                $toolbarButtons['list']['type'] = 'button';
                $toolbarButtons['list']['label'] = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M2.75 6C2.75 5.17 3.42 4.5 4.25 4.5C5.08 4.5 5.75 5.17 5.75 6C5.75 6.83 5.08 7.5 4.25 7.5C3.42 7.5 2.75 6.83 2.75 6ZM2.75 12C2.75 11.17 3.42 10.5 4.25 10.5C5.08 10.5 5.75 11.17 5.75 12C5.75 12.83 5.08 13.5 4.25 13.5C3.42 13.5 2.75 12.83 2.75 12ZM4.25 16.5C3.42 16.5 2.75 17.18 2.75 18C2.75 18.82 3.43 19.5 4.25 19.5C5.07 19.5 5.75 18.82 5.75 18C5.75 17.18 5.08 16.5 4.25 16.5ZM21.25 19H7.25V17H21.25V19ZM7.25 13H21.25V11H7.25V13ZM7.25 7V5H21.25V7H7.25Z" fill="#FF6C6C"/></svg>';
                $toolbarButtons['list']['attr'] = $toolbarAttr;
                $toolbarButtons['list']['attr']['title'] = __('List');
            }
        }

        if ($action == 'index') {
            if ($model->actions('add')) {
                $toolbarButtons['add']['url'] = $model->url('add');
                $toolbarButtons['add']['type'] = 'button';
                $toolbarButtons['add']['label'] = '<svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg"> <path fill-rule="evenodd" clip-rule="evenodd" d="M12.0418 9.95792H21.4168V12.0413H12.0418V21.4162H9.95847V12.0413H0.583496V9.95792H9.95847V0.582947H12.0418V9.95792Z" fill="white"></path> </svg>';
                $toolbarButtons['add']['attr'] = $toolbarAttr;

                $toolbarButtons['add']['attr']['class'] = "btn  ng-scope customAdd";
                $toolbarButtons['add']['attr']['title'] = __('Add');
            }
            if ($model->actions('search')) {
                $toolbarButtons['search'] = [
                    'type' => 'element',
                    'element' => 'OpenEmis.search',
                    'data' => ['url' => $model->url('index')],
                    'options' => []
                ];
            }
        } elseif ($action == 'add' || $action == 'edit') {
            $toolbarButtons['back']['url'] = $model->url($backAction, 'QUERY');
            if ($action == 'edit' && $model->actions('index')) {
                $toolbarButtons['back']['url'] = $model->url($backAction);
                $toolbarButtons['list']['url'] = $model->url('index', 'QUERY');
                $toolbarButtons['list']['type'] = 'button';
                $toolbarButtons['list']['label'] = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M2.75 6C2.75 5.17 3.42 4.5 4.25 4.5C5.08 4.5 5.75 5.17 5.75 6C5.75 6.83 5.08 7.5 4.25 7.5C3.42 7.5 2.75 6.83 2.75 6ZM2.75 12C2.75 11.17 3.42 10.5 4.25 10.5C5.08 10.5 5.75 11.17 5.75 12C5.75 12.83 5.08 13.5 4.25 13.5C3.42 13.5 2.75 12.83 2.75 12ZM4.25 16.5C3.42 16.5 2.75 17.18 2.75 18C2.75 18.82 3.43 19.5 4.25 19.5C5.07 19.5 5.75 18.82 5.75 18C5.75 17.18 5.08 16.5 4.25 16.5ZM21.25 19H7.25V17H21.25V19ZM7.25 13H21.25V11H7.25V13ZM7.25 7V5H21.25V7H7.25Z" fill="#FF6C6C"/></svg>
';
                $toolbarButtons['list']['attr'] = $toolbarAttr;
                $toolbarButtons['list']['attr']['title'] = __('List');
            }
        } elseif ($action == 'view') {
            // edit button
            $toolbarButtons['back']['url'] = $model->url($backAction, 'QUERY');
            if ($model->actions('edit')) {
                $toolbarButtons['edit']['url'] = $model->url('edit');
                $toolbarButtons['edit']['type'] = 'button';
                $toolbarButtons['edit']['label'] = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0)"><path d="M2.4 16.5601L0 24.0001L7.44 21.6001L2.4 16.5601Z" fill="#009966"/><path d="M15.7952 3.12597L4.08569 14.8354L9.17678 19.9265L20.8863 8.21706L15.7952 3.12597Z" fill="#009966"/><path d="M23.64 3.72L20.28 0.36C19.8 -0.12 19.08 -0.12 18.6 0.36L17.52 1.44L22.56 6.48L23.64 5.4C24.12 4.92 24.12 4.2 23.64 3.72Z" fill="#009966"/></g><defs><clipPath id="clip0"><rect width="24" height="24" fill="white"/></clipPath></defs></svg>';
                $toolbarButtons['edit']['attr'] = $toolbarAttr;
                $toolbarButtons['edit']['attr']['title'] = __('Edit');
            }

            if ($model->actions('remove') && $model->actions('remove') != 'transfer') {
                $toolbarButtons['remove']['url'] = $model->url('remove');
                $toolbarButtons['remove']['type'] = 'button';
                $toolbarButtons['remove']['label'] = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 21.3335C4 22.8069 5.19331 24.0002 6.66669 24.0002H17.3334C18.8067 24.0002 20 22.8069 20 21.3335V5.3335H4V21.3335Z" fill="#C71100"/><path d="M16.6667 1.33331L15.3334 0H8.66675L7.33337 1.33331H2.66675V4H21.3334V1.33331H16.6667Z" fill="#C71100"/></svg>';
                $toolbarButtons['remove']['attr'] = $toolbarAttr;
                $toolbarButtons['remove']['attr']['title'] = __('Delete');
                if ($model->actions('remove') != 'restrict') {
                    $toolbarButtons['remove']['attr']['data-toggle'] = 'modal';
                    $toolbarButtons['remove']['attr']['data-target'] = '#delete-modal';
                    $toolbarButtons['remove']['attr']['field-target'] = '#recordId';
                    $toolbarButtons['remove']['attr']['onclick'] = 'ControllerAction.fieldMapping(this)';
                }
            }

            if ($download = $model->actions('download')) {
                if ($download['show']) {
                    $toolbarButtons['download']['url'] = $model->url('download', false);
                    $toolbarButtons['download']['type'] = 'button';
                    $toolbarButtons['download']['label'] = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M18 12L24 12L12 24L-5.24537e-07 12L6 12L6 -7.86805e-07L18 -2.62268e-07L18 12ZM12 19.755L16.755 15L15 15L15 3L8.99997 3L8.99997 15L7.24497 15L12 19.755Z" fill="#009966"></path></svg>';
                    $toolbarButtons['download']['attr'] = $toolbarAttr;
                    $toolbarButtons['download']['attr']['title'] = __('Download');
                }
            }
        } elseif ($action == 'transfer' || ($action == 'remove' && $model->actions('remove') == 'restrict')) {
            $toolbarButtons['back']['url'] = $model->url('index', 'QUERY');
            $toolbarButtons['back']['type'] = 'button';
            $toolbarButtons['back']['label'] = '<svg class="backSvg" width="23" height="24" viewBox="0 0 23 24" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M23 10.4999H5.50563L13.5412 2.11501L11.5 0L0 12L11.5 24L13.5268 21.8849L5.50563 13.4999H23V10.4999Z" fill="#004A51"/> </svg>';
            $toolbarButtons['back']['attr'] = $toolbarAttr;
            $toolbarButtons['back']['attr']['title'] = __('Back');
        }

        if ($model->actions('view')) {
            $indexButtons['view']['url'] = $model->url('view');
            $indexButtons['view']['label'] = '<i class="fa fa-eye"></i>' . __('View');
            $indexButtons['view']['attr'] = $indexAttr;
        }

        if ($model->actions('edit')) {
            $indexButtons['edit']['url'] = $model->url('edit');
            $indexButtons['edit']['label'] = '<i class="fa fa-pencil"></i>' . __('Edit');
            $indexButtons['edit']['attr'] = $indexAttr;
        }

        if ($model->actions('remove')) {
            $indexButtons['remove']['strategy'] = $model->actions('remove');
            $removeUrl = 'remove';
            if ($model->actions('remove') == 'transfer') {
                $removeUrl = 'transfer';
            }
            $indexButtons['remove']['url'] = $model->url($removeUrl);
            $indexButtons['remove']['label'] = '<i class="fa fa-trash fg"></i>' . __('Delete');
            $indexButtons['remove']['attr'] = $indexAttr;
        }

        $backButton = $extra->offsetExists('back') ? $extra['back'] : false;
        if ($backButton) {
            $toolbarButtons['back']['url'] = $backButton;
            $toolbarButtons['back']['type'] = 'button';
            $toolbarButtons['back']['label'] = '<i class="fa kd-back trt"></i>';
            $toolbarButtons['back']['attr'] = $toolbarAttr;
            $toolbarButtons['back']['attr']['title'] = __('Back');
        }

        $extra['toolbarButtons'] = $toolbarButtons;
        $extra['indexButtons'] = $indexButtons;

        // entity information will be attached to toolbar in afterAction()
        // refer to attachEntityInfoToToolBar() in afterAction()
    }

    public function getButtonTemplate()
    {
        $btnAttr = [
            'type' => 'button',
            'attr' => [
                'class' => 'btn btn-xs btn-default',
                'data-toggle' => 'tooltip',
                'data-placement' => 'bottom',
                'escape' => false
            ]
        ];
        return $btnAttr;
    }

    private function isCAv4()
    {
        return isset($this->_table->CAVersion) && $this->_table->CAVersion=='4.0';
    }
}
