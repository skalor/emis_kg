<?php

namespace App\Services\PluginTranslator\Repository;

use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;

class LocaleContentRepository
{
    private $localeContents;

    public function __construct()
    {
        $this->localeContents = TableRegistry::get('LocaleContents');
    }

    public function getMessage(string $word): ?Entity
    {
        return $this->localeContents
            ->find('all')
            ->where(['en' => $word])
            ->first();
    }
}