<?php

/**
 * @Author Asiro
 */

namespace App\Services\PluginTranslator;

use Cake\Core\Configure;
use Cake\I18n\I18n;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;

class PluginTranslator
{
    private static $hidden = [
        'ADmad/JwtAuth',
        'API',
        'Alert',
        'Angular',
        'Bake',
        'Error',
        'Rest',
        'MoodleApi',
        'Migrations',
        'Log',
        'OAuth',
        'OpenEmis',
        'Outcome',
        'Cache',
        'ControllerAction',
        'Installer',
        'InstitutionRepeater',
        'DebugKit',
        'CustomExcel',
        'Page',
        'SSO',
        'User',
        'Webhook',
        'Restful',
    ];

    private $locales = [
        'ar' => 1,
        'zh' => 2,
        'en' => 3,
        'en_US' => 3,
        'fr' => 4,
        'ru' => 5,
        'es' => 6,
        'kg' => 7,
    ];

    public function translate(string $word, array $args = []): string
    {
        $message = $this->getMessage($word);

        if(!$message) {
            return $word;
        }

        $translation = $this->getTranslation($message);

        if($translation) {
            return $translation->translation;
        }

        return I18n::translator()->translate($word, $args);
    }

    private function getPlugin(): ?string
    {
        $request = Router::getRequest();

        return $request->params['plugin'];
    }

    public static function getPlugins(): array
    {
        $plugins = array_keys(Configure::read('plugins'));
        $plugins = array_diff($plugins, self::$hidden);

        return $plugins;
    }

    private function getLocaleId(): int
    {
        return $this->locales[I18n::locale()];
    }

    private function getMessage(string $word): ?Entity
    {
        return TableRegistry::get('LocaleContents')
            ->find('all')
            ->where(['en' => $word])
            ->first();
    }

    private function getTranslation(?Entity $message): ?Entity
    {
        return TableRegistry::get('Localization.ModuleTranslations')
            ->find('all')
            ->where([
                'locale_content_id' => $message->id,
                'locale_id' => $this->getLocaleId(),
                'plugin' => $this->getPlugin(),
            ])->first();
    }
}