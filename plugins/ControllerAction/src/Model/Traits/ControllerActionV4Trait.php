<?php
namespace ControllerAction\Model\Traits;

use ArrayObject;

use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\Event\Event;
use Cake\Network\Response;
use Cake\Controller\Exception\MissingActionException;
use ControllerAction\View\Helper\ControllerActionHelper;
use Cake\Routing\Router;

//use ControllerAction\Model\Traits\ControllerActionV4Trait;
//use ControllerActionV4Trait; // extended functionality from v4

trait ControllerActionV4Trait {
	// implementing CA v4 functions in using Traits
	// when CA v3 is completely not in use, this trait will replace all logic in CAComponent

	private function _initComponents($model) {
		$model->controller = $this->controller;
		$model->request = $this->request;
		$model->Session = $this->request->session();

		// Copy all component objects from Controller to Model
		$components = $this->controller->components()->loaded();
		foreach ($components as $component) {
			$model->{$component} = $this->controller->{$component};
		}
	}

	private function _render($model) {
		list($plugin, $alias) = pluginSplit($model->registryAlias());

		if (empty($plugin)) {
			$path = APP . 'Template' . DS . $this->controller->name . DS;
		} else {
			$path = ROOT . DS . 'plugins' . DS . $plugin . DS . 'src' . DS . 'Template' . DS;
		}
		$this->ctpFolder = $model->alias();
		$ctp = $this->ctpFolder . DS . $model->action;

		if (file_exists($path . DS . $ctp . '.ctp')) {
			if ($this->autoRender) {
				$this->autoRender = false;
				$this->controller->render($ctp);
			}
		} else {
			if ($this->autoRender) {
				if (empty($this->view)) {
					$view = $model->action == 'add' ? 'edit' : $model->action;
					// $this->controller->render($this->templatePath . $view);
					$this->controller->render($this->templatePath . 'template');
				} else {
					$this->controller->render($this->view);
				}
			}
		}
	}

	private function _renderFields($model) {
		foreach ($model->fields as $key => $attr) {
			if ($key == $this->orderField) {
				$model->fields[$this->orderField]['visible'] = ['view' => false];
			}
			if (array_key_exists('options', $attr)) {
				if (in_array($attr['type'], ['string', 'integer'])) {
					$model->fields[$key]['type'] = 'select';
				}
				if (empty($attr['options']) && empty($attr['attr']['empty'])) {
					if (!array_key_exists('empty', $attr)) {
						$model->fields[$key]['attr']['empty'] = $this->Alert->getMessage('general.select.noOptions');
					}
				}

				// for automatic adding of '-- Select --' if there are no '' value fields in dropdown
				$addSelect = true;
				if ($attr['type'] == 'chosenSelect') {
                    $addSelect = false;
                }

				if (array_key_exists('select', $attr)) {
					if ($attr['select'] === false) {
						$addSelect = false;
					} else {
						$addSelect = true;
					}
				}
				if ($addSelect) {
					if (is_array($attr['options'])) {
						// need to check if options has any ''
						if (!array_key_exists('', $attr['options'])) {
							if ($attr['type'] != 'chosenSelect') {
                                if (in_array($model->action, ['edit', 'add'])) {
                                    $model->fields[$key]['options'] = ['' => __('-- Select --')] + $attr['options'];
                                }
							} else {
								$model->fields[$key]['options'] = ['' => __('-- Select --')] + $attr['options'];
							}
						}
					}
				}
			}

			// make field sortable by default if it is a string data-type
			if (!array_key_exists('type', $attr)) {
				pr('Please set a data type for ' . $key);
			}

			$sortableTypes = ['string', 'date', 'time', 'datetime'];
			if (in_array($attr['type'], $sortableTypes) && !array_key_exists('sort', $attr) && $model->hasField($key)) {
				$model->fields[$key]['sort'] = true;
			} else if ($attr['type'] == 'select' && !array_key_exists('options', $attr)) {
				if ($model->isForeignKey($key)) {
					$associatedObject = $model->getAssociatedModel($key);

					$query = $associatedObject->find();

					// need to include associated object
					$event = new Event('ControllerAction.Model.onPopulateSelectOptions', $this, [$query]);
					$event = $associatedObject->eventManager()->dispatch($event);
					if ($event->isStopped()) { return $event->result; }
					if (!empty($event->result)) {
						$query = $event->result;
					}

					if ($model->action != 'index') { // should not populate options for index page
						if ($query instanceof Query) {
							$query->limit(1000); // to prevent out of memory error, options should not be more than 500 records anyway
							$queryData = $query->toArray();
							$hasDefaultField = false;
							$defaultValue = false;
							$optionsArray = [];
							foreach ($queryData as $okey => $ovalue) {
								$optionsArray[$ovalue->id] = $ovalue->name;
								if ($ovalue->has('default')) {
									$hasDefaultField = true;
									if ($ovalue->default) {
										$defaultValue = $ovalue->id;
									}
								}
							}

							if (!empty($defaultValue) && !(is_bool($attr['default']) && !$attr['default'])) {
								$model->fields[$key]['default'] = $defaultValue;
							}
							if ($attr['type'] != 'chosenSelect') {
	                            if (in_array($model->action, ['edit', 'add'])) {
								    $optionsArray = ['' => __('-- Select --')] + $optionsArray;
	                            }
							}

							$model->fields[$key]['options'] = $optionsArray;
						} else {
							$model->fields[$key]['options'] = $query;
						}
					}
				}
			}

//            if(isset($model->fields[$key]['options'])) {
//                $options = $model->fields[$key]['options'];
//
//                if(empty($model->fields[$key]['default']) && $default = $this->getDefault($options)) {
//                    $model->fields[$key]['default'] = $default;
//                }
//
//                $model->fields[$key]['options'] = $this->sortOptions($this->getFormattedOptions($options));
//            }

			if (array_key_exists('onChangeReload', $attr)) {

				if (!array_key_exists('attr', $model->fields[$key])) {
					$model->fields[$key]['attr'] = [];
				}
				$onChange = '';
				if (is_bool($attr['onChangeReload']) && $attr['onChangeReload'] == true) {
					$onChange = "$('#reload').click();return false;";
				} else {
					$onChange = "$('#reload').val('" . $attr['onChangeReload'] . "').click();return false;";
				}
				$model->fields[$key]['attr']['onchange'] = $onChange;
			}
		}
	}

	private function _sortByOrder($a, $b) {
 		if (!isset($a['order']) && !isset($b['order'])) {
 			return true;
 		} else if (!isset($a['order']) && isset($b['order'])) {
 			return true;
 		} else if (isset($a['order']) && !isset($b['order'])) {
 			return false;
 		} else {
 			return $a["order"] - $b["order"];
 		}
	}

	private function _validateOptions($options) {
		if (!array_key_exists('alias', $options)) {
			pr('There is no alias set for ' . $this->request->action);
			die;
		}

		if (!array_key_exists('className', $options)) {
			pr('There is no className set for ' . $this->request->action);
			die;
		}

		$className = $options['className'];
		$alias = $options['alias'];
		$model = $this->controller->loadModel($className);
		$model->alias = $alias;

		return $model;
	}

	public function process($options=[]) {
		$request = $this->request;

		$controller = $this->controller;

		$model = $this->_validateOptions($options);

		$this->_initComponents($model);

		$extra = new ArrayObject([
			'elements' => [],
			'config' => ['form' => false]
		]);

		$paramsPass = $request->params['pass'];
		$action = 'index';

		if (count($paramsPass) > 0) {
			if (!is_numeric($paramsPass[0])) { // this is an action
				$action = array_shift($paramsPass);
			}
		}

		$model->action = $action;
		$entity = null;

		$event = $controller->dispatchEvent('ControllerAction.Controller.onInitialize', [$model, $extra], $this);
		if ($event->isStopped()) { return $event->result; }

		$event = $model->dispatchEvent('ControllerAction.Model.beforeAction', [$extra], $this);
		if ($event->isStopped()) { return $event->result; }

		// dispatch event for specific action
		$event = $model->dispatchEvent("ControllerAction.Model.$action", [$extra], $this);
		if ($event->isStopped()) { return $event->result; }
		if ($event->result instanceof Entity) {
			$entity = $event->result;
		} else if ($event->result instanceof Response) {
			return $event->result;
		} else if (is_null($event->result)) {
			throw new MissingActionException([
                'controller' => $controller->name . "Controller",
                'action' => $action,
                'prefix' => '',
                'plugin' => $request->params['plugin'],
            ]);
		}

		$extra['entity'] = $entity;
		$event = $model->dispatchEvent('ControllerAction.Model.afterAction', [$extra], $this);
		if ($event->isStopped()) { return $event->result; }

		$elements = $extra['elements'];
		uasort($elements, [$this, '_sortByOrder']);

		$this->_renderFields($model);

        //$model->dispatchEvent('ControllerAction.Model.beforeRender', [$model, $extra], $this);

		uasort($model->fields, [$this, '_sortByOrder']);

        // MonPib
        if($this->request->is('ajax')){
            $this->getResponseAjax($action, $model, $controller->viewVars['data'], $extra, $entity);
            $extraJson = clone $extra;
            unset($extraJson['config']);
            $this->controller->set('getResponseAjax', $extraJson);
            //return $extra;
        }//*/

		$extra['config']['action'] = $model->action;
		$extra['config']['table'] = $model;
		$extra['config']['fields'] = $model->fields;

		$this->deprecatedFunctions(['model' => $model->alias()]);

		$controller->set('ControllerAction', $extra['config']);
		$controller->set('elements', $elements);
		$this->_render($model);
	}

	function getResponseAjax($action, $model, $data, $extra, $entity){
        $extra['data_response'] = $data;
        if ($entity != null) {
            $extra['data_response_errors'] = $entity->errors();
        }
        $extra['action'] = $action;
        if($action == 'index'){
            $dataKeys = [];
            $newData = array();
            $indexButtons = $extra['indexButtons'];
            $controllerActionHelper = new ControllerActionHelper(new \Cake\View\View());

            $tableHeaders = $controllerActionHelper->getTableHeaders($model->fields, $model->alias(), $dataKeys);
            $displayAction = is_array($indexButtons) ? count($indexButtons) : $indexButtons->count() > 0;
            $eventKey = 'Model.custom.onUpdateActionButtons';
            $controllerActionHelper->onEvent($model, $eventKey, 'onUpdateActionButtons');


            //trigger event to get which field need to be highlighted
            $searchableFields = new ArrayObject();
            $event = new Event('ControllerAction.Model.getSearchableFields', $controllerActionHelper, [$searchableFields]);
            $event = $model->eventManager()->dispatch($event);

            foreach($data as $entity){

                $row = $controllerActionHelper->getTableRow($entity, $dataKeys, $searchableFields->getArrayCopy());

                if ($displayAction) {
                    $buttons = $indexButtons->getArrayCopy();
                    $event = $controllerActionHelper->dispatchEvent($model, $eventKey, null, [$entity, $indexButtons->getArrayCopy()]);
                    $buttons = $event->result;

                    if (empty($buttons)) {
                        $row[] = '';
                    } else {
                        foreach ($buttons as &$button) {
                            $button['urlBuild'] = Router::url($button['url'], true);
                        }
                        $row[] = $buttons;
                    }

                }
                $newData[] = $row;
            }
            $extra['data_response_header'] = $tableHeaders;
            $extra['data_response'] = $newData;
            $extra['paging'] = null;
            if (!empty($model->request->params['paging']) && count($model->request->params['paging']))
                $extra['paging'] = array_values($model->request->params['paging'])[0];
        }
        if($action == 'edit'){
            $controllerActionHelper = new ControllerActionHelper(new \Cake\View\View());
            $data_response_attr = $controllerActionHelper->getEditElementsAjax($data, $model);
            $extra['data_response_attr'] = $data_response_attr;
            $formOptions = $controllerActionHelper->getFormOptions();
            $form = $controllerActionHelper->Form->create($model, $formOptions);
            $form.= $controllerActionHelper->Form->end();
            $extra['data_form'] = $form;
            $extra['data_form_action'] = Router::fullBaseUrl() . $this->request->here(true);// $controllerActionHelper->Form->context();
            $doc = new \DOMDocument();
            $doc->loadHTML($extra['data_form']);
            $xpath = new \DOMXPath($doc);
            $inputs = $xpath->query("//input[@type='hidden']");
            $params = [];
            foreach ($inputs as $input) {
                $name = $input->getAttribute('name');
                $value = $input->getAttribute('value');
                $value = $name == '_Token[unlocked]' && empty($value) ? 'submit' : urlencode($value);
                $params[]= $name.'='.$value;
            }
            parse_str(implode('&', $params), $extra['data_form_hidden']);
        }

        if(isset($this->controller->viewVars['tabElements'])){
            $extra['tabElements'] = $this->controller->viewVars['tabElements'];
            $selectedAction = $this->controller->viewVars['selectedAction'];
            foreach ($extra['tabElements'] as $key => &$tabElement) {
                $tabElement['active'] = $key == $selectedAction || $tabElement['url']['controller'] == $selectedAction || count($extra['tabElements']) == 1;
            }
        }

        $extra['navigations'] = $this->getNavigations($this->controller->viewVars['_navigations']);

        $_controller = $this->request->params['controller'];
        $_action = $this->request->params['action'];
        $_pass = [];
        if (!empty($this->request->pass)) {
            $_pass = $this->request->pass;
        } else {
            $_pass[0] = '';
        }
        foreach ($extra['navigations'] as $key => &$value) {
            $value['active'] = false;
            $linkName = $_controller.'.'.$_action;
            $controllerActionLink = $linkName;
            if (!empty($_pass[0])) {
                $linkName .= '.'.$_pass[0];
            }
            if ($linkName == $key || $controllerActionLink == $key) {
                $value['active'] = true;
            } elseif (isset($value['selected'])) {
                if (in_array($linkName, $value['selected']) || in_array($controllerActionLink, $value['selected'])) {
                    $value['active'] = true;
                }
            }
        }

        foreach ($extra['indexButtons'] as &$button) {
            $button['urlBuild'] = Router::url($button['url'], true);
        }
        foreach ($extra['toolbarButtons'] as &$button) {
            $button['urlBuild'] = Router::url($button['url'], true);
        }
        foreach ($extra['navigations'] as $key => &$button) {
            $button['title'] = __($button['title']);
            $button['urlBuild'] = Router::url($this->getLink($key, $button['params']), true);
        }
        foreach ($extra['tabElements'] as &$button) {
            $button['text'] = __($button['text']);
            $button['urlBuild'] = Router::url($button['url'], true);
        }

        $extra['tabSelected'] = $this->controller->viewVars['selectedAction'];

        $extra['ajax'] = 1;
    }

    function getLink($controllerActionModelLink, $params = [])
    {
        $url = ['plugin' => null, 'controller' => null, 'action' => null];
        if (isset($params['plugin'])) {
            $url['plugin'] = $params['plugin'];
            unset($params['plugin']);
        }

        $link = explode('.', $controllerActionModelLink);

        if (isset($link[0])) {
            $url['controller'] = $link[0];
        }
        if (isset($link[1])) {
            $url['action'] = $link[1];
        }
        if (isset($link[2])) {
            $url['0'] = $link[2];
        }
        if (!empty($params)) {
            $url = array_merge($url, $params);
        }
        return $url;
    }

    function getNavigations($navigations){
        $childrenNodes = [];
        foreach ($navigations as $key=>$navigation){
            if (isset($navigation['parent'])){
                if($navigation['parent'] == 'Institutions.Students.index'){
                    $childrenNodes[$key] = $navigation;
                }
            }
        }
        return $childrenNodes;
    }

    public function processGetEditButton($options=[]){
        $request = $this->request;
        $controller = $this->controller;
        $model = $this->_validateOptions($options);
        $this->_initComponents($model);

        $extra = new ArrayObject([
            'elements' => [],
            'config' => ['form' => false]
        ]);

        $paramsPass = $request->params['pass'];
        $action = 'index';


        if (count($paramsPass) > 0) {
            if (!is_numeric($paramsPass[0])) { // this is an action
                $action = array_shift($paramsPass);
            }
        }

        $model->action = $action;
        $entity = null;

        $event = $controller->dispatchEvent('ControllerAction.Controller.onInitialize', [$model, $extra], $this);

        if ($event->isStopped()) {
            return $event->result;
        }


        $toolbarButtons = new ArrayObject([]);
        $toolbarAttr = [
            'class' => 'btn btn-xs btn-default',
            'data-toggle' => 'tooltip',
            'data-placement' => 'bottom',
            'escape' => false
        ];

        if ($model->actions('edit')) {
            $toolbarButtons['edit']['url'] = $model->url('edit');
            $toolbarButtons['edit']['type'] = 'button';
            $toolbarButtons['edit']['label'] = '<i class="fa kd-edit"></i>';
            $toolbarButtons['edit']['attr'] = $toolbarAttr;
            $toolbarButtons['edit']['attr']['title'] = __('Edit');
        }
        return $toolbarButtons;
    }//*/

	private function deprecatedFunctions($params) {
		$this->controller->set('model', $params['model']);
	}

    private function sort(array $options): array
    {
       $options = array_map('trim', $options);
       asort($options);

       return $options;
    }

    private function sortOptions(array $options) {
        if(empty($options)) {
            return $options;
        }

        $first = $this->getFirst($options);

        if(!is_array($first)) {
            return $this->sort($options);
        }

        ksort($options);

        return array_map(function($values) {
            if(is_array($values)) {
                $values = $this->sort($values);
            }

            return $values;
        }, $options);
    }

    private function getFormattedOptions(array $options): array
    {
        $first = $this->getFirst($options);

        if(!$first || !isset($first['value']) || !isset($first['text'])) {
            return $options;
        }

        $standardised = [];

        foreach($options as $option) {
            $standardised[$option['value']] = $option['text'];
        }

        return $standardised;
    }

    private function getFirst(array $options)
    {
        $first = key($options);
        $first = $first ? current($options) : next($options);

        return $first;
    }

    private function getDefault(array $options): ?string
    {
        $first = current($options);

        if(!is_array($first)) {
            return null;
        }

        foreach($options as $option) {
            if(isset($option[0])) {
                return $option['value'];
            }
        }

        return null;
    }
}
