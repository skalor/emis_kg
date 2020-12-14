<?php

namespace HiddenField\Model\Table;

use Cake\ORM\Entity;

class HiddenEntity extends Entity
{
    public function __get($n)
    {
        return '';
    }
    
    public function __set($n, $v)
    {
    
    }
}