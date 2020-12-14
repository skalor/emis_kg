<?php

/**
 * @Author Asiro
 */

namespace App\Services\PluginTranslator;

use App\Services\PluginTranslator\Repository\ModuleRepository;
use Cake\ORM\Entity;

class ModuleTranslator implements TranslatorInterace
{
    /**
     * @var ModuleRepository
     */
    private $moduleRepository;

    /**
     * @var PluginService
     */
    private $pluginService;

    public function __construct()
    {
        $this->moduleRepository = new ModuleRepository();
        $this->pluginService = new PluginService();
    }

    public function translate(int $messageId): ?Entity
    {
        $plugin = $this->pluginService->getCurrentPlugin();

        if(!$plugin) {
            return null;
        }

        return $this->moduleRepository->getTranslation($messageId, $plugin);
    }
}