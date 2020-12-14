<?php

namespace App\Services\PluginTranslator;


use Cake\ORM\Entity;

interface TranslatorInterace
{
    public function translate(int $messageId): ?Entity;
}