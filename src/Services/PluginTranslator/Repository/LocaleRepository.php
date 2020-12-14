<?php

namespace App\Services\PluginTranslator\Repository;

use Cake\I18n\I18n;

class LocaleRepository
{
    private $locales = [
        'en' => 3,
        'en_US' => 3,
        'ru' => 5,
        'kg' => 7,
    ];

    public function getLocaleId(string $locale): int
    {
        if(!array_key_exists($locale, $this->locales)) {
            throw new \Exception("This {$locale} doesn't exist.");
        }

        return $this->locales[$locale];
    }

    public function getCurrentLocaleId(): int
    {
        return $this->getLocaleId(I18n::locale());
    }

    public function getLocales(): array
    {
        return $this->locales;
    }
}