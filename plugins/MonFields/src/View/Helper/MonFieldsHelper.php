<?php
namespace MonFields\View\Helper;

use ArrayObject;
use Cake\View\View;
use Page\View\Helper\PageHelper;

class MonFieldsHelper extends PageHelper
{
    public $helpers = ['Form', 'Html', 'Paginator', 'Url'];
    public $buttons;

    public function __construct(View $View, array $config = [])
    {
        parent::__construct($View, $config);
        $this->buttons = new ArrayObject([
            [
                'name' => '<i class="fa fa-check"></i> ' . __('Save'),
                'attr' => ['class' => 'btn btn-default btn-save', 'div' => false, 'name' => 'submit', 'value' => 'save']
            ],
            [
                'name' => 'reload',
                'attr' => ['id' => 'reload', 'class' => 'hidden', 'div' => false, 'name' => 'submit', 'value' => 'reload']
            ]
        ]);
    }

    public function getFormButtons(array $buttons = [])
    {
        if (!$buttons) {
            $buttons = $this->buttons;
        }
        
        if (isset($this->_View->viewVars['elements']['field_type'])) {
            $fieldType = $this->_View->viewVars['elements']['field_type']['attributes']['value'];
            if ($fieldType === 'dropdown') {
                $buttons->offsetSet(2, [
                    'name' => '<i class="fa fa-plus"></i> ' . __('Add item'),
                    'attr' => ['id' => 'dropdownAdd', 'class' => 'btn btn-default', 'div' => false, 'name' => 'submit', 'value' => 'dropdownAdd']
                ]);
                $buttons->offsetSet(3, [
                    'name' => '<i class="fa fa-minus"></i> ' . __('Remove item'),
                    'attr' => ['id' => 'dropdownRemove', 'class' => 'btn btn-outline btn-cancel', 'div' => false, 'name' => 'submit', 'value' => 'dropdownRemove']
                ]);
            }
        }

        $html = '';
        if ($buttons->count() > 0) {
            $html = '<div class="form-buttons"><div class="button-label"></div>';
            foreach ($buttons as $btn) {
                if (!array_key_exists('url', $btn)) {
                    $html .= $this->Form->button($btn['name'], $btn['attr']);
                } else {
                    $html .= $this->Html->link($btn['name'], $btn['url'], $btn['attr']);
                }
            }
            $html .= $this->_View->element('Page.cancel');
            $html .= '</div>';
        }
        return $html;
    }

    public function renderInputElements()
    {
        //dd('yes');

        return parent::renderInputElements();
    }
}