<?php

namespace Localization\Controller;

use Page\Controller\Component\PageComponent;
use Page\Model\Entity\PageElement;

class Selectable
{
    private $params = [
        'options',
        'attributes',
        'disabled',
    ];

    private $name, $options, $attributes = [], $disabled;

    public function __construct(string $name, array $options = [])
    {
        $this->name = $name;
        $this->options = $options;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): void
    {
        foreach($options as $name => $option) {
            $this->options[$name] = $option;
        }
    }

    public function setAttributes(array $attributes): void
    {
        foreach($attributes as $name => $attribute) {
            $this->attributes[$name] = $attribute;
        }
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function setDisabled(): self
    {
        $this->disabled = true;

        return $this;
    }

    public function multiple(): self
    {
        $this->setAttributes([
            'multiple' => true,
        ]);

        return $this;
    }

    public function searchable(): self
    {
        $this->setAttributes([
            'class' => 'selectpicker',
            'data-live-search' => 'true',
        ]);

        return $this;
    }

    public function apply(PageElement $element): void
    {
        /** @var PageElement $element */
        $element->setControlType('select');

        foreach($this->params as $param) {
            $param = ucfirst($param);

            if($this->changeable($element, $param)) {
                $this->change($element, $param);
            }
        }
    }

    private function changeable(PageElement $element, string $param): bool
    {
        $setter = "set$param";
        $getter = "get$param";

        return
            method_exists($element, $setter)
            && method_exists($this, $getter)
            && !empty(call_user_func([$this, $getter]))
            ;
    }

    private function change(PageElement $element, string $param)
    {
        $getter = "get$param";
        $getter = [$this, $getter];

        $setter = "set$param";
        $setter = [$element, $setter];

        if($param === 'Attributes') {
            foreach($this->attributes as $name => $value) {
                call_user_func_array($setter, [$name, $value]);
            }
        } else {
            call_user_func($setter, call_user_func($getter));
        }
    }
}