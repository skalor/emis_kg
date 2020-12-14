<?php

namespace App\Services\PluginTranslator\Repository;

use App\Services\PluginTranslator\InstitutionTranslator;
use Cake\ORM\Entity;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;

class ModuleRepository extends TranslationRepository
{
    public function getTranslation(int $messageId, string $plugin): ?Entity
    {
        if($plugin === InstitutionTranslator::PLUGIN) {
            return null;
        }

        return $this->translation($messageId, [
            'plugin' => $plugin,
        ])->first();
    }

    protected function table(): Table
    {
        return TableRegistry::get('Localization.ModuleTranslations');
    }
}