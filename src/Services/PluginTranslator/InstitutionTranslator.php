<?php

namespace App\Services\PluginTranslator;

use App\Services\PluginTranslator\Repository\InstitutionRepository;
use Cake\ORM\Entity;
use Cake\Routing\Router;

class InstitutionTranslator implements TranslatorInterace
{
    public const PLUGIN = 'Institution';

    /**
     * @var \Cake\Network\Request|null
     */
    private $request;

    /**
     * @var InstitutionRepository
     */
    private $institutionRepository;

    /**
     * @var PluginService
     */
    private $pluginService;

    public function __construct()
    {
        $this->institutionRepository = new InstitutionRepository();
        $this->pluginService = new PluginService();

        $this->request = Router::getRequest();
    }

    public function translate(int $messageId): ?Entity
    {
        if(!$this->isInstitution()) {
            return null;
        }

        $institution = $this->institutionRepository->getInstitution($this->getInstitutionId());

        return $this->institutionRepository->getTranslation($messageId, $institution->institution_type_id);
    }

    private function getInstitutionId(): ?int
    {
        return $this->request->session()->read('Institution.Institutions.id');
    }

    private function isInstitution(): bool
    {
        return $this->pluginService->getCurrentPlugin() === self::PLUGIN && $this->getInstitutionId() !== null;
    }
}