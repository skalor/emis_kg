<?php
namespace Institution\View\Helper;

use Cake\I18n\Date;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use ControllerAction\Model\Traits\SecurityTrait;
use Page\View\Helper\PageHelper;

class SigningHelper extends PageHelper
{
    use SecurityTrait;

    public function getTableData()
    {
        $tableData = [];
        $data = $this->_View->get('data');
        $fields = $this->_View->get('elements');
        $SDModel = TableRegistry::get('Institution.InstitutionSignedDocuments');

        foreach ($data as $entity) {
            $row = [];
            foreach ($fields as $field => $attr) {
                // Adding html for specific fields
                if ($field === 'is_signed') {
                    $value = $this->getValue($entity, $attr);
                    $row[] = $value ? '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"> <path fill-rule="evenodd" clip-rule="evenodd" d="M10 0C4.48 0 0 4.48 0 10C0 15.52 4.48 20 10 20C15.52 20 20 15.52 20 10C20 4.48 15.52 0 10 0ZM10 18C5.59 18 2 14.41 2 10C2 5.59 5.59 2 10 2C14.41 2 18 5.59 18 10C18 14.41 14.41 18 10 18ZM8 12.17L14.59 5.58L16 7L8 15L4 11L5.41 9.59L8 12.17Z" fill="#1A8753"/> </svg>' : '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"> <path fill-rule="evenodd" clip-rule="evenodd" d="M10 0C4.47 0 0 4.47 0 10C0 15.53 4.47 20 10 20C15.53 20 20 15.53 20 10C20 4.47 15.53 0 10 0ZM12.59 6L10 8.59L7.41 6L6 7.41L8.59 10L6 12.59L7.41 14L10 11.41L12.59 14L14 12.59L11.41 10L14 7.41L12.59 6ZM2 10C2 14.41 5.59 18 10 18C14.41 18 18 14.41 18 10C18 5.59 14.41 2 10 2C5.59 2 2 5.59 2 10Z" fill="#F44336"/> </svg>';

                    if ($entity->$field) {
                        array_unshift($row, '<input type="checkbox" class="signCheck" name="checked_ids['. $entity->id .']" style="opacity:1;margin: 0 !important; transform: translate(-50%, -75%); position: absolute; top: 50%;" disabled checked>');
                    } else {
                        array_unshift($row, '<input type="checkbox" class="signCheck" name="checked_ids['. $entity->id .']" style="opacity:1;margin: 0 !important; transform: translate(-50%, -75%); position: absolute; top: 50%;">');
                    }

                    continue;
                }
                if ($field === 'signed_link' && $entity->signed_document_id) {
                    $primaryKeys = $SDModel->primaryKey();
                    $document = $SDModel->get($entity->signed_document_id);

                    $primaryKeyValue = [];
                    if (is_array($primaryKeys)) {
                        foreach ($primaryKeys as $key) {
                            $primaryKeyValue[$key] = $document->getOriginal($key);
                        }
                    } else {
                        $primaryKeyValue[$primaryKeys] = $document->getOriginal($primaryKeys);
                    }

                    $encodedKeys = $this->paramsEncode($primaryKeyValue);
                    $institutionId = $this->request->param('institutionId');
                    $documentUrl = $this->getUrl(['controller' => 'Institutions', 'action' => 'SignedDocuments', 'institutionId' => $institutionId, 'view', $encodedKeys]);
                    $row[] = '<a style=" display: flex; justify-content: center; " href="' . $documentUrl . '" class="signedDocumentLink" target="_blank"><i class="fa fa-share-square-o " style=" font-size: 23px; "></i></a>';

                    continue;
                }

                if (($attr['controlType'] == 'string' || $attr['controlType'] == 'text') && !$this->isRTL($this->getValue($entity, $attr))) {
                    $row[] = '<div style = "direction: ltr !important">' . $this->getValue($entity, $attr) . '</div>';
                } else {
                    $row[] = $this->getValue($entity, $attr);
                }
            }
            $disabledActions = $this->_View->get('disabledActions');
            $actionButtons = ['view', 'edit', 'delete'];
            if (count(array_intersect($actionButtons, $disabledActions)) < count($actionButtons)) {
                $row[] = $this->_View->element('Page.actions', ['data' => $entity]);
            }
            if (!in_array('reorder', $disabledActions)) {
                $model = TableRegistry::get($entity->source());
                $primaryKeys = $model->primaryKey();

                $primaryKeyValue = [];
                if (is_array($primaryKeys)) {
                    foreach ($primaryKeys as $key) {
                        $primaryKeyValue[$key] = $entity->getOriginal($key);
                    }
                } else {
                    $primaryKeyValue[$primaryKeys] = $entity->getOriginal($primaryKeys);
                }

                $encodedKeys = $this->encode($primaryKeyValue);
                $row[] = [$this->_View->element('Page.reorder'), ['class' => 'sorter', 'data-row-id' => $encodedKeys]];
            }

            $tableData[] = $row;
        }
        return $tableData;
    }

    public function getValue($entity, $field)
    {
        $controlType = $field['controlType'];

        $array = $entity instanceof Entity ? $entity->toArray() : $entity;
        $data = Hash::flatten($array);
        $value = array_key_exists($field['key'], $data) ? $data[$field['key']] : '';
        if (array_key_exists('displayFrom', $field)) { // if displayFrom exists, always get value based on displayFrom
            $key = $field['displayFrom'];
            if (array_key_exists($key, $data)) {
                $value = $data[$key];
            }
        } else {
            $isDropdownType = $controlType == 'dropdown';
            $isOptionsExists = array_key_exists('options', $field);
            if ($isDropdownType && $isOptionsExists) {
                $options = $field['options'];
                $valueExistsInOptions = array_key_exists($value, $options);
                if ($valueExistsInOptions) {
                    $value = $options[$value];
                }
            }
        }

        $isDateTimeType = in_array($controlType, ['date', 'time']);
        $isStringType = in_array($controlType, ['string', 'textarea']);
        $hasDateTimeFormat = array_key_exists('format', $field);
        $valueIsNotEmpty = !empty($value);
        $action = !is_null($this->request->param('action')) ? $this->request->param('action') : 'index';

        if ($isDateTimeType && $hasDateTimeFormat && $valueIsNotEmpty) {
            $valueIsDateObject = $value instanceof Date;
            if ($valueIsDateObject) {
                $value = $value->i18nFormat($field['format']);
            } else {
                $value = (new Date($value))->i18nFormat($field['format']);
            }
        } elseif (($action == 'index') && ($isStringType || $field['foreignKey'] != false) && $valueIsNotEmpty) {
            $value = $this->highlight($value);
        }
        return __($value);
    }
}
