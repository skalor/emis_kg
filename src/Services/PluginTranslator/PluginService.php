<?php

namespace App\Services\PluginTranslator;

use Cake\Core\Configure;
use Cake\Routing\Router;
use Cake\Utility\Inflector;

class PluginService
{
    private $plugins = [];
    private $humanized = [];

    private $excluded = [
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

    private function getPlugins(): array
    {
        if($this->plugins) {
            return $this->plugins;
        }

        $plugins = array_keys(Configure::read('plugins'));
        return $this->plugins = array_diff($plugins, $this->excluded);
    }

    public function getCurrentPlugin(): ?string
    {
        $request = Router::getRequest();
        if($request && isset($request->params['plugin'])) {
            return $request->params['plugin'];
        }

        return null;
    }

    public function getHumanizedPlugins(): array
    {
        if($this->humanized) {
            return $this->humanized;
        }

        $plugins = $this->getPlugins();
        $humanized = [];

        foreach($plugins as $plugin) {
            $humanized[$plugin] = __(Inflector::humanize(Inflector::underscore($plugin)));
        }

        return $this->humanized = $humanized;
    }
}