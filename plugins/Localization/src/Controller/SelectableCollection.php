<?php

namespace Localization\Controller;

use Page\Controller\Component\PageComponent;

class SelectableCollection
{
    /**
     * @var Selectable[]
     */
    private $selectable = [];

    /**
     * @param  Selectable[] $selectable
     */
    public function __construct(array $selectable = [])
    {
        $this->add($selectable);
    }

    public function get(string $name): Selectable
    {
        return $this->selectable[$name];
    }

    /** @return Selectable[] */
    public function all(): array
    {
        return array_values($this->selectable);
    }

    public function merge(SelectableCollection $selectableCollection): void
    {
        foreach($selectableCollection->all() as $selectable) {
            $this->selectable[$selectable->getName()] = $selectable;
        }
    }

    public function apply(PageComponent $page): void
    {
        foreach($this->all() as $selectable) {
            $selectable->apply($page->get($selectable->getName()));
        }
    }

    public function searchable($selectable): void
    {
        if(is_string($selectable)) {
            $selectable = [$selectable];
        }

        /** @var Selectable $item */
        foreach($selectable as $item) {
            $this->get($item)->searchable();
        }
    }

    public function add(array $selectable): void
    {
        foreach($selectable as $name => $options) {
            if(is_int($name)) {
                $name = $options;
                $options = [];
            }

            $this->selectable[$name] = new Selectable($name, $options);
        }
    }

    public function new(string $name, array $options = []): Selectable
    {
        $selectable = new Selectable($name, $options);
        $this->selectable[$name] = $selectable;

        return $selectable;
    }

}