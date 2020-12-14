<?php
namespace MonGeneratedStatisticReports\Controller\Component;

use Cake\Controller\Component;
use Cake\I18n\Date;
use Cake\I18n\I18n;
use Cake\I18n\Time;
use Cake\Log\Log;
use Cake\ORM\Query;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;

class TemplatorComponent extends Component
{
    private $alias = '';
    private $template = '';
    private $snippets = [];
    private $variables = [];
    private $snippetVariables = [];
    private $translates = [];
    private $conditions = [];
    private $methods = [];
    private $errors = [];
    private $user = [];
    private $defaultValue = 0;
    private $defaultConditions = [
        [
            'column' => 'institution_id',
            'alias' => 'Institutions'
        ]
    ];
    private $functionPatterns = [
        [
            'name' => 'snippets',
            'field' => 'snippets',
            'pattern' => '/snippet\((?!snippet\(.*\);)[\s\S]*?\);/',
            'aliasing' => true,
        ],
        [
            'name' => 'snippetVariables',
            'field' => 'snippetVariables',
            'pattern' => '/snippetVariables\((?!snippetVariables\(.*\);)[\s\S]*?\);/',
            'aliasing' => false,
        ],
        [
            'name' => 'variables',
            'field' => 'variables',
            'pattern' => '/variable\((?!variable\(.*\);)[\s\S]*?\);/',
            'aliasing' => false,
            'cycling' => true,
            'run' => [
                'runGlobalVariables' => []
            ]
        ],
        [
            'name' => 'translates',
            'field' => 'translates',
            'pattern' => '/translate\((?!translate\(.*\);)[\s\S]*?\);/',
            'aliasing' => true,
        ],
        [
            'name' => 'conditions',
            'field' => 'conditions',
            'pattern' => '/condition\((?!condition\(.*\);)[\s\S]*?\);/',
            'aliasing' => true,
        ],
        [
            'name' => 'methods',
            'field' => 'methods',
            'pattern' => '/method\((?!method\(.*\);)[\s\S]*?\);/',
            'aliasing' => true,
            'run' => [
                'runForeach' => []
            ]
        ],
    ];

    public function trimArray(array $array)
    {
        $result = [];
        foreach ($array as $key => $item) {
            if (is_array($item)) {
                $result[trim($key)] = $this->trimArray($item);
            } else {
                $result[trim($key)] = trim($item);
            }
        }

        return $result;
    }

    public function checkArray(array $array, ?string $field = '')
    {
        if (
            $field && isset($array[$field]) && $array[$field]
            || !$field && is_array($array) && $array
        ) {
            return true;
        }

        return false;
    }

    public function isSubstrExists(string $name, ?string $string = '')
    {
        if (
            $string && strpos($string, trim($name)) !== false
            || strpos($this->template, trim($name)) !== false
        ) {
            return true;
        }

        return false;
    }

    public function replace($value, ?string $alias = '', ?string $string = '', ?bool $isRegex = false)
    {
        $alias ? $this->alias = $alias : null;
        if ($this->alias) {
            $string ? : $string = &$this->template;
            if ($isRegex) {
                $string = preg_replace($this->alias, $value, $string);
            } else {
                $string = str_replace($this->alias, $value, $string);
            }
        }

        return $string;
    }

    public function logging($message, string $fileName = 'MonTemplatorLog', $context = [], bool $logging = true)
    {
        if (!$logging) {
            return null;
        }

        Log::reset();
        Log::config('info', [
            'className' => 'Cake\Log\Engine\FileLog',
            'path' => LOGS,
            'levels' => ['notice', 'warning', 'info', 'debug', 'error'],
            'file' => $fileName
        ]);

        return Log::write('info', ['result' => $message], $context);
    }

    public function setTemplate(string $template, ?bool $htmlSpChDecode = true)
    {
        if ($htmlSpChDecode) {
            $template = htmlspecialchars_decode($template, ENT_QUOTES | ENT_HTML5);
        }

        $this->template = $template;

        return $this;
    }

    public function getTemplate(?bool $htmlSpChEncode = true)
    {
        $template = $this->template;

        if ($htmlSpChEncode) {
            $template = htmlspecialchars($template, ENT_QUOTES | ENT_HTML5);
        }

        return $template;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function setUser(array $user)
    {
        $this->user = $user;

        return $this;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function snippet(array $params)
    {
        $result = '';
        !$this->checkArray($this->snippets, 'rendered') ? $this->snippets['rendered'] = [] : null;
        $table = TableRegistry::get('MonTemplateReports.MonTemplateReports');
        if ($table && $params) {
            $templates = $table->find('all', $params)->all();
            if ($templates) {
                foreach ($templates as $template) {
                    if ($template->content) {
                        $result .= htmlspecialchars_decode($template->content, ENT_QUOTES | ENT_HTML5);
                    }
                }
                $result = preg_replace('/<(\/?)html(.*?)>|<(\/?)body(.*?)>|<(\/?)head(.*?)>|<(\/?)title(.*?)>/', '', $result);
                array_push($this->snippets['rendered'], $result);
                $this->replace($result);
            }
        }

        return $result;
    }

    public function variable($variable, ?string $methodName = '', ?string $model = '', ?array $params = [])
    {
        $result = []; $prefix = '$'; $suffix = ''; $separator = '.';
        !$this->checkArray($this->variables, 'rendered') ? $this->variables['rendered'] = [] : null;
        if (is_array($variable)) {
            foreach ($variable as $name => $value) {
                $field = trim($name);
                $fieldPrefixed = $prefix . $field;
                $fieldSuffixed = $fieldPrefixed . $suffix;
                $isVariableUsed = $this->isSubstrExists($fieldSuffixed);
                if ($isVariableUsed) {
                    $result = [$field => $value];
                    array_push($this->variables['rendered'], $result);
                    $this->replace($value, $fieldSuffixed);
                }
            }
        } else if (is_string($variable) && $methodName && $model) {
            $field = trim($variable);
            $fieldPrefixed = $prefix . $field;
            $fieldSuffixed = $fieldPrefixed . $suffix;
            $isVariableUsed = $this->isSubstrExists($fieldPrefixed);
            if ($isVariableUsed) {
                $result = [$field => $this->$methodName($model, $params, $variable, $prefix, $suffix, $separator)];
                array_push($this->variables['rendered'], $result);
                if (is_array($result[$field]) || is_object($result[$field])) {
                    $this->objectVariables($result[$field], $variable, $prefix, $suffix, $separator);
                } else {
                    $value = $this->checkArray($result, $field) ? $result[$field] : $this->defaultValue;
                    $this->replace($value, $fieldSuffixed);
                }
            }
        }

        return $result;
    }

    public function objectVariables($object, string $variable, string $prefix = '', string $suffix = '', string $separator = '')
    {
        if (is_object($object) && method_exists($object, 'toArray')) {
            $object = $object->toArray();
        }

        if ($object && is_array($object)) {
            $variable = $prefix . $variable;
            foreach ($object as $key => $item) {
                $variable .= $separator . trim($key);
                if ($this->isSubstrExists($variable)) {
                    if (!is_array($item) && (!is_object($item) || $item instanceof Date || $item instanceof Time)) {
                        $this->alias = '/\Q' . $variable . $suffix . '\E(?![\\' . $separator . '\d\w])/';
                        $value = $item ? $item : $this->defaultValue;
                        $this->replace($value, '', '', true);
                    } else {
                        $this->objectVariables($item, $variable, '', $suffix, $separator);
                    }
                }
                $exploded = explode($separator, $variable);
                unset($exploded[count($exploded) - 1]);
                $variable = implode($separator, $exploded);
            }

            return true;
        }

        return false;
    }

    public function snippetVariables(array $params, bool $last = true)
    {
        $result = '';
        !$this->checkArray($this->snippetVariables, 'rendered') ? $this->snippetVariables['rendered'] = [] : null;
        $snippets = $this->snippet($params);
        if ($snippets) {
            $result = '<snippetVariables>' . trim($snippets) . '</snippetVariables>';
            array_push($this->snippetVariables['rendered'], $result);
            $this->template .= $result;
            if ($last) {
                $this->callFunctions('variables');
                $this->replace('', '/<snippetVariables>.*<\/snippetVariables>/si', '', true);
            }
        }

        return $result;
    }

    public function translate(string $string, string $locale = '')
    {
        $locale ? I18n::locale($locale) : null;
        $result = __($string);
        !$this->checkArray($this->translates, 'rendered') ? $this->translates['rendered'] = [] : null;
        if ($result) {
            array_push($this->translates['rendered'], [$string => $result]);
            $this->replace($result);
        }

        return $result;
    }

    public function condition($condition, string $ifValue, string $elseValue = '')
    {
        $result = false;
        !$this->checkArray($this->conditions, 'rendered') ? $this->conditions['rendered'] = [] : null;
        if ($condition) {
            $result = $condition;
            array_push($this->conditions['rendered'], [$condition => $ifValue]);
            $this->replace($ifValue);
        } else {
            array_push($this->conditions['rendered'], [$condition => $elseValue]);
            $this->replace($elseValue);
        }

        return $result;
    }

    public function method(string $methodName, string $model, array $params)
    {
        $result = $this->$methodName($model, $params);
        !$this->checkArray($this->methods, 'rendered') ? $this->methods['rendered'] = [] : null;
        array_push($this->methods['rendered'], $result);
        $this->replace($result);
        return $result;
    }

    public function runGlobalVariables(?array $variables = [], ?string $prefix = '$', ?string $suffix = '')
    {
        $prefix = trim($prefix); $suffix = trim($suffix);
        $user = $this->getUser();
        $variables = $variables ? $variables : $user;

        foreach ($variables as $key => $item) {
            $field = $prefix . trim($key) . $suffix;
            $isVariableUsed = $this->isSubstrExists($field);
            if ($isVariableUsed) {
                $modItem = $item;
                if ($modItem instanceof Date) {
                    $modItem = $modItem->format('Y-m-d');
                } else if ($modItem instanceof Time) {
                    $modItem = $modItem->format('Y-m-d H:i:s');
                } else if (is_object($modItem) || is_array($modItem)) {
                    continue;
                }
                $this->replace($modItem, $field);
            }
        }

        return $this;
    }

    public function runFunctions(string $regex, string $field, array $params = [], ?bool $isFirst = true)
    {
        if ($isFirst && $this->checkArray($params, 'run') && $this->checkArray($params['run'])) {
            foreach ($params['run'] as $function => $arguments) {
                $this->checkArray($arguments) ? : $arguments = [];
                call_user_func_array([$this, $function], $arguments);
            }
        }

        if (!$this->checkArray($this->$field, 'functions')) {
            preg_match_all($regex, $this->template, $this->$field['functions']);
        }

        $functions = $this->$field['functions'];
        if (isset($functions[0])) {
            $count = count($functions[0]);
            foreach ($functions[0] as $key => $function) {
                if ($this->checkArray($params, 'aliasing')) {
                    $this->alias = $function;
                } else {
                    $this->replace('', $function);
                }

                try {
                    eval('$this->' . $function);
                } catch (\ParseError $exception) {
                    array_push($this->errors, $exception->getMessage());
                }

                $this->alias = null;
                if ($key === $count-1) {
                    $log = array_merge($this->$field, $this->errors);
                    $this->logging(['field' => $field, 'value' => $log]);
                }

                if ($this->checkArray($params, 'cycling')) {
                    $this->$field['functions'] = [];
                    $this->runFunctions($regex, $field, $params, false);
                    break;
                }
            }
        }

        return $this;
    }

    public function callFunctions(string $name = '')
    {
        if ($this->functionPatterns) {
            foreach ($this->functionPatterns as $functionPattern) {
                if (
                    $this->checkArray($functionPattern, 'name')
                    && $name && $functionPattern['name'] !== $name
                ) {
                    continue;
                }

                if (
                    $this->checkArray($functionPattern, 'field')
                    && $this->checkArray($functionPattern, 'pattern')
                ) {
                    $params = [
                        'aliasing' => $this->checkArray($functionPattern, 'aliasing'),
                        'cycling' => $this->checkArray($functionPattern, 'cycling'),
                        'run' => $this->checkArray($functionPattern, 'run') ? $functionPattern['run'] : [],
                    ];
                    $this->runFunctions($functionPattern['pattern'], $functionPattern['field'], $params);
                }
            }
        }
    }

    public function getCondition(Table $table, string $column, string $alias, $value = '', ?string $operation = '')
    {
        $condition = [];
        $columns = $table->schema()->columns();
        $key = $alias === $table->alias() ? 'id' : $column;
        if (in_array($key, $columns)) {
            $operationIs = $operation ? $operation : '=';
            $user = $this->getUser();
            if ($value) {
                $condition[$table->aliasField($key) . ' ' . $operationIs] = $value;
            } else if ($this->checkArray($user, $column)) {
                $condition[$table->aliasField($key) . ' ' . $operationIs] = $user[$column];
            }
        }

        return $condition;
    }

    public function getConditions(Table $table, array $conditions, array $substrExists = [])
    {
        $result = [];
        foreach ($conditions as $condition) {
            $isSubstrExist = true;
            if ($substrExists) {
                foreach ($substrExists as $key => $value) {
                    $isSubstrExist = $isSubstrExist && !$this->isSubstrExists($condition[$key], $value);
                }
            }
            if ($this->checkArray($condition, 'column') && $this->checkArray($condition, 'alias') && $isSubstrExist) {
                $value = $this->checkArray($condition, 'value') ? $condition['value'] : '';
                $operation = $this->checkArray($condition, 'operation') ? $condition['operation'] : '';
                $result = array_merge($result, $this->getCondition($table, $condition['column'], $condition['alias'], $value, $operation));
            }
        }

        return $result;
    }

    public function getDefaultConditions(Table $table, ?array $conditions = [])
    {
        $result = [];
        if ($conditions) {
            foreach ($conditions as $condition) {
                if (
                    $this->checkArray($condition, 'column')
                    && $this->checkArray($condition, 'alias')
                    && $this->checkArray($condition, 'value')
                ) {
                    $operation = $this->checkArray($condition, 'operation') ? $condition['operation'] : '';
                    $result = array_merge($result, $this->getCondition($table, $condition['column'], $condition['alias'], $condition['value'], $operation));
                }
            }

            if ($result) {
                foreach ($result as $key => $item) {
                    $result = array_merge($result, $this->getConditions($table, $this->defaultConditions, ['column' => $key, 'alias' => $key]));
                }

                return $result;
            }
        }

        $result = array_merge($result, $this->getConditions($table, $this->defaultConditions));

        return $result;
    }

    public function getDefaultQuery(string $model, array $params)
    {
        $result = [];
        $table = TableRegistry::get($model);

        if ($table && $params) {
            $this->defaultValue = $this->checkArray($params, 'defaultValue') ? $params['defaultValue'] : 0;
            $defaultConditions = $this->checkArray($params, 'defaultConditions') ? $params['defaultConditions'] : [];
            $conditions = isset($params['conditions']) && is_array($params['conditions'])
                ? array_merge($this->getDefaultConditions($table, $defaultConditions), $params['conditions'])
                : [];

            if ($conditions) {
                $params['conditions'] = $conditions;
                $result = $table->find('all', $params);
            }
        }

        return $result;
    }

    public function render()
    {
        $this->callFunctions();
        $this->runTranslates();
        // clear br from start
        $this->template = preg_replace('/(<body.*?>)(\s*<br\s*\/>)+/i', '$1', $this->template);

        return $this->template;
    }


    /**
     * Template methods
     */

    public function array(string $model, array $params, string $variable, string $prefix, string $suffix, string $separator, ?Query $result = null)
    {
        if (!$result) {
            $variable = $prefix . $variable;
            $result = $this->getDefaultQuery($model, $params);
        }

        if ($result) {
            $isCount = isset($params['isCount']) && !$params['isCount'] ? false : true;
            if ($this->checkArray($params, 'subQueries') && $this->checkArray($params['subQueries'])) {
                $tmpResult = ['basic' => $isCount ? $result->count() : $result->first()];
                foreach ($params['subQueries'] as $name => $subParams) {
                    $arrayVariable = $variable . $separator . trim($name);
                    $isVariableUsed = $this->isSubstrExists($arrayVariable);
                    if ($isVariableUsed && $name && is_string($name) && $this->checkArray($subParams)) {
                        $clonedResult = clone $result;
                        $clonedResultTable = $clonedResult->repository()->callFinder('all', $clonedResult, $subParams);
                        if ($this->checkArray($subParams, 'subQueries') && $this->checkArray($subParams['subQueries'])) {
                            $tmpResult[$name] = $this->array($model, $subParams, $arrayVariable, $prefix, $suffix, $separator, $clonedResultTable);
                        } else {
                            $isCount = isset($subParams['isCount']) && !$subParams['isCount'] ? false : true;
                            $tmpResult[$name] = $isCount ? $clonedResultTable->count() : $clonedResultTable->first();
                        }
                    }
                }
            } else {
                $tmpResult = $isCount ? $result->count() : $result->first();
            }
            $result = $tmpResult;
        }

        return $result;
    }

    public function count(string $model, array $params)
    {
        $result = $this->getDefaultQuery($model, $params);
        $result = $result ? $result->count() : 0;

        return $result;
    }

    public function bool(string $model, array $params)
    {
        $result = $this->getDefaultQuery($model, $params);
        $result = $result ? $result->count() : '';
        $result = $result ? '+' : '-';

        return $result;
    }

    public function column(string $model, array $params)
    {
        $result = $this->getDefaultQuery($model, $params);
        if ($result && $this->checkArray($params, 'column')) {
            $result = $result->first();
            $exploded = explode('->', $params['column']);
            $explodedCount = count($exploded);
            if ($explodedCount > 1) {
                foreach ($exploded as $key => $item) {
                    if (isset($result->{$item})) {
                        $result = $result->{$item};
                    } else {
                        $result = $this->defaultValue;
                        break;
                    }
                }
            } else if ($result && isset($result->{$params['column']})) {
                $result = $result->{$params['column']}
                    ? $result->{$params['column']}
                    : $this->defaultValue;
            }

            if ($result && is_string($result)) {
                $result = __($result);
            } else if (!$result || is_object($result) || is_array($result)) {
                $result = $this->defaultValue;
                is_string($result) ? $result = __($result) : null;
            }
        }

        return $result;
    }

    public function table(string $model, array $params){
        $result = '';
        $table = TableRegistry::get($model);
        if ($table && $params) {
            $conditions = isset($params['conditions']) && is_array($params['conditions'])
                ? array_merge($this->getDefaultConditions($table), $params['conditions'])
                : [];
            if ($conditions) {
                $params['conditions'] = $conditions;
                $key = isset($params['key']) ? $params['key'] : false;
                $rows = $table->find('all', $params)->all()->toArray();
                $result = [];
                foreach ($rows as $k => $row) {
                    $item = $row->toArray();
                    $result[$key?$item[$key]:$k]=$item;
                }
                $result = var_export($result, true);
                //file_put_contents('/var/www/html/core_new/logs/MonGenerateStatisticReport.log', "\r\n --- custom --- \r\n".$result, FILE_APPEND | LOCK_EX);
                $this->replace($result);
            }
        }

        return $result;
    }

    public function runForeach(string $regex = '/foreach\s*\(\s*((?!as)[\s\S]+?)\s+as\s+(?:(\$[^\s\(\)]+)?\s+=>)?\s*(\$[^\s\(\)]+)\s*\)\s*:([\s\S]+?)endforeach\s*;/uim') {
        while(preg_match($regex, $this->template, $match)) {
            $temp = $this->template;
            $methods = $this->methods['functions'];
            $alias = $this->alias;
            $foreach_template = '';
            $this->methods['functions'] = [];
            $this->alias = false;
            $array = [];
            eval('$array = ' . $match[1].';');
            foreach ($array as $key => $row) {
                $this->template = trim($match[4]);
                $this->methods['functions'] = [];
                if (!empty($match[2]))
                    $this->replace($key, trim($match[2]));
                if (is_array($row) || is_object($row)) {
                    foreach ($row as $k => $v) {
                        if (is_array($v) || is_object($v)) {
                            foreach ($v as $k2 => $v2) {
                                $this->replace($v2, trim($match[3]) . '->' . $k . '->' .$k2);
                            }
                        }
                        else {
                            $this->replace($v, trim($match[3]) . '->' . $k);
                        }
                    }
                }
                else {
                    $this->replace($row, trim($match[3]));
                }
                $this->callFunctions('methods');
                $foreach_template.= $this->template;
            }
//            file_put_contents('/var/www/html/core_new/logs/MonGenerateStatisticReport.log', "\r\n --- custom --- \r\nreplace($v2, ".(trim($match[3]) . '->' . $k . '->' .$k2).")", FILE_APPEND | LOCK_EX);
//            file_put_contents('/var/www/html/core_new/logs/MonGenerateStatisticReport.log', "\r\n --- custom --- \r\n".$this->template, FILE_APPEND | LOCK_EX);
            $this->template = $temp;
            $this->replace($foreach_template, $match[0]);
            $this->methods['functions'] = $methods;
            $this->alias = $alias;
        }
    }

    public function runTranslates($regex = '/__\((\'([^\']*)\'|"([^"]*)"|[^()]*)\)/uim'){
        while (preg_match($regex, $this->template, $match)) {
            $text = $match[2] ?: ($match[3] ?: $match[1]);
            $this->replace(__($text), $match[0]);
        }
    }
}
