<?php

namespace App\Services\PluginTranslator;

use App\Services\PluginTranslator\Repository\LocaleContentRepository;
use Aura\Intl\Translator;
use Cake\I18n\I18n;

class TranslatorService
{
    /**
     * @var string[]
     */
    private $translators = [
        InstitutionTranslator::class,
        ModuleTranslator::class
    ];

    /**
     * @var Translator
     */
    private $defaultTranslator;

    /**
     * @var LocaleContentRepository
     */
    private $localeContentRepository;

    public function __construct()
    {
        $this->defaultTranslator = I18n::translator();
        $this->localeContentRepository = new LocaleContentRepository();
    }

    public function translate($word, array $args = [])
    {
        if (!is_string($word)){
            return $word;
        }
        if(!$message = $this->localeContentRepository->getMessage($word)) {
            return $word;
        }

        foreach($this->translators as $translator) {
            /** @var TranslatorInterace $translator */
            $translator = new $translator();

            if($translation = $translator->translate($message->id)) {
                return $translation->translation;
            }
        }

        $translation = $this->defaultTranslator->translate($word, $args);

        return $translation;
    }
}
