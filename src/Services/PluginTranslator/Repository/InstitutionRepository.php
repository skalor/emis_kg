<?php

namespace App\Services\PluginTranslator\Repository;

use App\Services\PluginTranslator\InstitutionTranslator;
use Cake\ORM\Entity;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;

class InstitutionRepository extends TranslationRepository
{
    /**
     * @var Table
     */
    private $institutionTypes;

    /**
     * @var Table
     */
    private $institutions;

    public function __construct()
    {
        parent::__construct();

        $this->institutionTypes = TableRegistry::get('InstitutionTypes');
        $this->institutions = TableRegistry::get('Institutions');
    }

    public function getTranslation(int $messageId, int $institutionTypeId): ?Entity
    {
        $translations = $this
            ->contain('InstitutionTypes')
            ->translation($messageId, [
                'plugin' => InstitutionTranslator::PLUGIN
            ])->all()->toList();

        foreach($translations as $translation) {
            $types = array_filter($translation->institution_types, function(Entity $type) use($institutionTypeId) {
                return $type->id === $institutionTypeId;
            });

            if(!empty($types)) {
                return $translation;
            }
        }

        return null;
    }

    public function getInstitution(int $id): ?Entity
    {
        return $this->institutions->find()->where([
            'id' => $id,
        ])->first();
    }

    protected function table(): Table
    {
        return TableRegistry::get('Localization.ModuleTranslations');
    }
}
