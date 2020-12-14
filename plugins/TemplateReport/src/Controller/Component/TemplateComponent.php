<?php
namespace TemplateReport\Controller\Component;

use ArrayObject;
use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Page\Controller\Component\PageComponent;

class TemplateComponent extends PageComponent
{

    private $actionList = ['index','generated_index','statistic_index'];



    public function beforeRender(Event $event)
    {
        $controller = $this->controller;
        $request = $this->request;
        $requestQueries = $request->query;
        $session = $request->session();
        $action = $request->action;
        $isGet = $request->is(['get']);
        $isAjax = $request->is(['ajax']);

        $data = $this->getData();
//        var_dump($data);
//        die('here');

        if ( !is_null($data) ) {
            if ($action == 'view' || $action == 'delete') { // load the entity values into elements with events
                $this->loadDataToElements($data);
            } elseif ($action == 'edit' || $action == 'add') { // load the entity values into elements without events
                $this->loadDataToElements($data, false);
            } elseif (in_array($action,$this->actionList)) { // populate entities with action permissions

                foreach ($data as $entity) {
                    // disabled actions
                    $disabledActions = [];
                    $event = $controller->dispatchEvent('Controller.Page.getEntityDisabledActions', [$entity], $this);

                    if ($event->result) {
                        $disabledActions = $event->result;
                    }
                    if ($entity instanceof Entity) {
                        $entity->disabledActions = $disabledActions;
                    } else {
                        $entity['disabledActions'] = $disabledActions;
                    }
                    // end

                    // row actions
                    $rowActionsArray = $this->getRowActions($entity);
                    $rowActions = new ArrayObject($rowActionsArray);
                    $event = $controller->dispatchEvent('Controller.Page.getEntityRowActions', [$entity, $rowActions], $this);
                    $rowActionsArray = $rowActions->getArrayCopy();

                    if ($event->result) {
                        $rowActionsArray = $event->result;
                    }
                    if ($entity instanceof Entity) {
                        $entity->rowActions = $rowActionsArray;
                    } else {
                        $entity['rowActions'] = $rowActionsArray;
                    }
                    // end

                    foreach ($this->elements as $element) {
                        $key = $element->getKey();
                        $displayFrom = $element->getDisplayFrom();

                        if (is_null($displayFrom) && !$this->isExcluded($key)) {
                            $value = null;
                            $key = $element->getKey();
                            $controlType = $element->getControlType();
                            $prefix = 'Controller.Page.onRender';
                            $eventName = $prefix . ucfirst($controlType);
                            $eventParams = [$entity, $element];
                            $event = $controller->dispatchEvent($eventName, $eventParams, $this);
                            if ($event->result) { // trigger render<Format>
                                $value = $event->result;
                            } else {
                                $eventName = $prefix . Inflector::camelize($key);
                                $event = $controller->dispatchEvent($eventName, $eventParams, $this);
                                if ($event->result) { // trigger render<Field>
                                    $value = $event->result;
                                }
                            }
                            if (!is_null($value)) {
                                $entity->{$key} = $value;
                            } else {
                                if ($controlType == 'select') {
                                    $selectOptions = $element->getOptions();
                                    if (!$this->isForeignKey($this->mainTable, $key) && !empty($selectOptions)) { // to render values if set from predefined options
                                        $value = $entity->{$key};
                                        if (array_key_exists($value, $selectOptions) && strlen($value) > 0) {
                                            $entity->{$key} = $selectOptions[$entity->{$key}];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }


//        if ($isGet || $isAjax || $this->showElements()) {
//            $this->setVar('header', $this->getHeader());
//            $this->setVar('breadcrumbs', $this->breadcrumbs);
//
//            if ($this->elements->count() > 0) {
//                $elements = $this->elementsToJSON();
//                $this->setVar('elements', $elements);
//            }
//
//            if ($this->filters->count() > 0) {
//                $querystring = $this->getQueryString();
//
//                foreach ($this->filters as $filter) {
//                    $dependentOn = $filter->getDependentOn();
//                    if ($dependentOn && array_intersect_key(array_flip($dependentOn), $querystring)) {
//                        $filterOptions = $this->getFilterOptions($filter->getParams());
//                        $filter->setOptions($filterOptions);
//                    }
//                }
//                $this->setVar('filters', $this->filtersToJSON());
//            }
//
//            if ($this->toolbars->count() > 0) {
//                $this->setVar('toolbars', $this->toolbars->getArrayCopy());
//            }
//
//            if ($this->tabs->count() > 0) {
//                $this->setVar('tabs', $this->tabsToArray());
//            }
//
//            if ($this->hasMainTable()) {
//                $table = $this->getMainTable();
//                $columns = $table->schema()->columns();
//
//                if (array_key_exists('paging', $request->params)) {
//                    $paging = $request->params['paging'][$table->alias()];
//                    $paging['limitOptions'] = $this->limitOptions;
//                    $this->setVar('paging', $paging);
//                }
//
//                if (!in_array($this->config('sequence'), $columns) || !($this->isActionAllowed('reorder') && $this->isActionAllowed('edit'))) {
//                    $this->disable(['reorder']);
//                }
//            }
//
//            $disabledActions = [];
//            foreach ($this->actions as $action => $value) {
//                if ($value == false) {
//                    $disabledActions[] = $action;
//                }
//            }
//            $this->setVar('disabledActions', $disabledActions);
//        }
//
//        if ($session->check('alert')) {
//            $this->setVar('alert', $session->read('alert'));
//            $session->delete('alert');
//        }
//
//
//        if ($this->viewVars->count() > 0) {
//            $this->controller->set($this->viewVars->getArrayCopy());
//        }
//
//        $this->controller->set('status', $this->status->toArray());
//
//        if ($this->isDebugMode()) {
//            pr($this->controller->viewVars);
//            die;
//        }
    }

    public function getRowActions($entity,$prefix,$plugin=false)
    {
        $url = ['plugin' => $plugin, 'controller' => $this->request->params['controller']];
        $primaryKey = !is_array($entity) ? $entity->primaryKey : $entity['primaryKey']; // $entity may be Entity or array

        $view   = true;
        $edit   = true;
        $delete = true;

        // disabled actions for each row
        if (!is_array($entity)) {
            if ($entity->has('disabledActions')) {
                $view = !in_array('view', $entity->disabledActions);
                $edit = !in_array('edit', $entity->disabledActions);
                $delete = !in_array('delete', $entity->disabledActions);
            }
        } else {
            if (array_key_exists('disabledActions', $entity)) {
                $view = !in_array('view', $entity['disabledActions']);
                $edit = !in_array('edit', $entity['disabledActions']);
                $delete = !in_array('delete', $entity['disabledActions']);
            }
        }
        // end


        // disabled actions for a page
        $disabledActions = [];
        foreach ($this->Page->getActions() as $action => $value) {
            if ($value == false) {
                $disabledActions[] = $action;
            }
        }
        // end

        $rowActions = [];
        if (!in_array('view', $disabledActions) && $view == true) {
            $rowActions['view'] = [
                'url' => $this->Page->getUrl(array_merge($url, ['action' => $prefix.'view', $primaryKey])),
                'icon' => 'fa fa-eye',
                'title' => __('View')
            ];
        }

        if (!in_array('edit', $disabledActions) && $edit == true) {
            $rowActions['edit'] = [
                'url' => $this->Page->getUrl(array_merge($url, ['action' => $prefix.'edit', $primaryKey])),
                'icon' => 'fa fa-pencil',
                'title' => __('Edit')
            ];
        }

        if (!in_array('delete', $disabledActions) && $delete == true) {
            $rowActions['delete'] = [
                'url' => $this->Page->getUrl(array_merge($url, ['action' => $prefix.'delete', $primaryKey])),
                'icon' => 'fa fa-trash c',
                'title' => __('Delete')
            ];
        }
        var_dump($rowActions);
        die;
        return $rowActions;
    }
}
