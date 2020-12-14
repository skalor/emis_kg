<?php
namespace Institution\Controller\Component;

use Cake\ORM\Table;
use Page\Controller\Component\PageComponent;

class InstitutionPageComponent extends PageComponent
{
    public function autoConditions(Table $table)
    {
        $conditions = [];
        $columns = $table->schema()->columns();
        $querystring = $this->getQueryString();
        foreach ($querystring as $key => $value) {
            if (in_array($key, $columns)) {
                $conditions[$table->aliasField($key)] = $value;
            }

            if ($key === 'createdFrom') {
                $conditions[$table->aliasField('created') . ' >='] = $value;
            }
            if ($key === 'createdTo') {
                $conditions[$table->aliasField('created') . ' <='] = $value;
            }
        }
        if (!empty($conditions)) {
            $this->setQueryOption('conditions', $conditions);
        }
    }
}
