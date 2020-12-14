<?php
namespace MonFields\Listeners;

use App\Controller\PageController;
use App\Model\Table\ControllerActionTable;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\Validation\Validator;

class AddEventsListener implements EventListenerInterface
{
    public function implementedEvents()
    {
        return [
            'Model.initialize' => 'modelRegister',
            'Model.buildValidator' => 'modelValidate',
            'Controller.initialize' => 'controllerRegister'
        ];
    }

    public function modelRegister(Event $event)
    {
        /** @var AppTable $table */
        $table = $event->subject();

        if (
            $table instanceof ControllerActionTable
        ) {
            if (!$table->hasBehavior('Section')) {
                $table->addBehavior('OpenEmis.Section');
            }

            if (!$table->hasBehavior('MonFields')) {
                $table->addBehavior('MonFields.MonFields');
            }
        }
    }

    public function modelValidate(Event $event)
    {
        $table = $event->subject();
        $tablePlugin = explode('.', $table->registryAlias());
        $request = Router::getRequest();
        $params = $request ? $request->params : [];
        if (isset($tablePlugin[0]) && isset($params['plugin']) && $tablePlugin[0] === $params['plugin']) {
            $fields = TableRegistry::get('MonFields.MonFields')->getFields($table->registryAlias());
            if ($fields) {
                foreach ($fields as $field) {
                    if ($field->is_mandatory) {
                        $event->data['validator']->allowEmpty($field->name);
                    }

                    $params = $field->params ? unserialize($field->params) : [];
                    if ($field->field_type === 'number') {
                        $this->setNumberValidation($event->data['validator'], $field, $params);
                    } else if($field->field_type === 'decimal') {
                        $this->setDecimalValidation($event->data['validator'], $field, $params);
                    } else if($field->field_type === 'string') {
                        $this->setStringValidation($event->data['validator'], $field, $params);
                    } else if($field->field_type === 'date') {
                        $this->setDateValidation($event->data['validator'], $field, $params);
                    } else if($field->field_type === 'time') {
                        $this->setTimeValidation($event->data['validator'], $field, $params);
                    }
                }
            }
        }
    }

    public function controllerRegister(Event $event)
    {
        $controller = $event->subject();

        if ($controller instanceof PageController) {
            $controller->loadComponent('MonFields.MonFields');
        }
    }

    private function setNumberValidation(Validator $validator, Entity $field, array $params = [])
    {
        $validator->add($field->name, 'custom', [
            'rule' => function ($value, $context) use($params) {
                if (!$params && $value > 2147483647) {
                    return __("Should not be greater than 2147483647");
                } else if (!$params && $value < 0) {
                    return __("Should not be lesser than 0");
                }

                if (isset($params['range']['max_value']) && $value > $params['range']['max_value']) {
                    return __("Should not be greater than").' '.$params['range']['max_value'];
                }

                if (isset($params['range']['min_value']) && $value < $params['range']['min_value']) {
                    return __("Should not be lesser than").' '.$params['range']['min_value'];
                }

                return true;
            }
        ]);
    }

    private function setDecimalValidation(Validator $validator, Entity $field, array $params = [])
    {
        $validator->add($field->name, 'custom', [
            'rule' => function ($value, $context) use($params) {
                if (!$params && $value > 999999999.9999) {
                    return __("Should not be greater than 999999999.9999");
                } else if (!$params && $value < 0) {
                    return __("Should not be lesser than 0");
                }

                $decimalLength = explode('.', $value);
                if (isset($params['decimal_length']) && isset($decimalLength[0]) && strlen($decimalLength[0]) > $params['decimal_length']) {
                    return __("Should not exceed {$params['decimal_length']} characters before '.'");
                }
                if (isset($params['decimal_place']) && isset($decimalLength[1]) && strlen($decimalLength[1]) > $params['decimal_place']) {
                    return __("Should not exceed {$params['decimal_place']} characters after '.'");
                }

                return true;
            }
        ]);
    }

    private function setStringValidation(Validator $validator, Entity $field, array $params = [])
    {
        if (isset($params['url'])) {
            $validator->add($field->name, 'valid', ['rule' => 'url', 'message' => 'Must be an URL format']);
        } else if (isset($params['validation_format'])) {

        } else if (isset($params['range'])) {
            $validator->add($field->name, 'custom', [
                'rule' => function ($value, $context) use($params) {
                    if (isset($params['range']['max_length']) && strlen($value) > $params['range']['max_length']) {
                        return __("Should not exceed {$params['range']['max_length']}");
                    }

                    if (isset($params['range']['min_length']) && strlen($value) < $params['range']['min_length']) {
                        return __("Should be at least {$params['range']['min_length']}");
                    }

                    return true;
                }
            ]);
        }
    }

    private function setDateValidation(Validator $validator, Entity $field, array $params = [])
    {
        $validator->add($field->name, 'custom', [
            'rule' => function ($value, $context) use($params) {
                $value = $value ? date('Y-m-d', strtotime($value)) : null;
                $startDate = isset($params['range']['start_date']) ? date('Y-m-d', strtotime($params['range']['start_date'])) : null;
                $endDate = isset($params['range']['end_date']) ? date('Y-m-d', strtotime($params['range']['end_date'])) : null;

                if ($value && $endDate && $value > $endDate) {
                    return __("Should not be later than {$params['range']['end_date']}");
                }

                if ($value && $startDate && $value < $startDate) {
                    return __("Should not be earlier than {$params['range']['start_date']}");
                }

                return true;
            }
        ]);
    }

    private function setTimeValidation(Validator $validator, Entity $field, array $params = [])
    {
        $validator->add($field->name, 'custom', [
            'rule' => function ($value, $context) use($params) {
                $value = $value ? date('H:i:s', strtotime($value)) : null;
                $startTime = isset($params['range']['start_time']) ? date('H:i:s', strtotime($params['range']['start_time'])) : null;
                $endTime = isset($params['range']['end_time']) ? date('H:i:s', strtotime($params['range']['end_time'])) : null;

                if ($value && $endTime && $value > $endTime) {
                    return __("Should not be later than {$params['range']['end_time']}");
                }

                if ($value && $startTime && $value < $startTime) {
                    return __("Should not be earlier than {$params['range']['start_time']}");
                }

                return true;
            }
        ]);
    }
}
