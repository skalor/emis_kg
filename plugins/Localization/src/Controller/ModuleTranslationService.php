<?php declare(strict_types=1);

namespace Localization\Controller;

use App\Exceptions\ErrorsException;
use App\Model\Table\LocalesTable;
use Cake\Network\Request;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Localization\Model\Table\ModuleTranslationsTable;
use Page\Controller\Component\PageComponent;

class ModuleTranslationService
{
    /** @var PageComponent */
    private $page;
    
    /**
     * @var LocalesTable
     */
    private $locales;
    
    /**
     * @var ModuleTranslationsTable
     */
    private $moduleTranslations;
    
    /**
     * @var Request
     */
    private $request;
    
    public function __construct(PageComponent $page)
    {
        $this->page = $page;
        $this->locales = TableRegistry::get('Locales');
        $this->moduleTranslations = TableRegistry::get('Localization.ModuleTranslations');
        $this->request = Router::getRequest();
    }
    
    public function addLanguages(): void
    {
        $locales = $this->locales->getAllExceptMain();
        $page = $this->page;
        $moduleTranslation = $page->getData();
        
        foreach($locales as $id => $locale) {
            $page
                ->addNew($id, [
                    'type' => 'string',
                    'length' => 250,
                ])
                ->setLabel($locale);
        }
        
        if(!$moduleTranslation) {
            return;
        }
    
        $translations = $this->getTranslations($moduleTranslation);

        foreach($translations as $localeId => $translation) {
            $page->get($localeId)
                ->setValue($translation->translation)
                ->setLabel($this->locales->getLocaleTitle($localeId));
        }
    }
    
    public function changeTranslations(?bool $reload = false): void
    {
        if(!$this->request->is(['post', 'put', 'patch'])) {
            return;
        }
        
        $translations = $this->getChangedTranslations();

        $moduleTranslations = $this->moduleTranslations;
        
        foreach($translations as $translation) {
            $entity = is_null($translation['id']) ? $moduleTranslations->newEntity() : $moduleTranslations->get($translation['id']);
    
            if(!$reload && empty($translation['translation'])) {
                if(!is_null($entity->id)) {
                    $moduleTranslations->delete($entity);
                }
        
                continue;
            }
            
            $entity = $moduleTranslations->patchEntity($entity, $translation, [
                'associated' => 'InstitutionTypes',
            ]);

            if($entity->errors() || !$moduleTranslations->save($entity)) {
                $this->page->setVar('data', $entity);
                throw new ErrorsException('The record is not updated due to errors encountered.', $entity->errors());
            }
        }
    }
    
    public function getChangedTranslations(): array
    {
        $data = $this->request->data('ModuleTranslations');
        $locales = $this->getIds($data);
        
        try {
            $moduleTranslation = $this->moduleTranslations->get($data['id']);
        } catch(\Exception $exception) {
            $moduleTranslation = null;
        }
        
        $existingTranslations = $this->getTranslations($moduleTranslation);
        
        unset($data['translation']);
        
        foreach($locales as $k => $v) {
            unset($data[$k]);
        }
        
        $translations = [];
        
        foreach($locales as $id => $translation) {
            $entity = $data;
            $entity['translation'] = $translation;
            $entity['locale_id'] = $id;
            $entity['id'] = empty($existingTranslations[$id]) ? null : $existingTranslations[$id]->id;
            
            $translations[] = $entity;
        }
        
        return $translations;
    }
    
    /**
     * @param array $data
     * @return array
     */
    public function getIds(array $data): array
    {
        return array_filter($data, function($translation, string $id) {
            return (string)(int)($id) === $id;
        }, ARRAY_FILTER_USE_BOTH);
    }
    
    /**
     * @return array
     */
    private function getTranslations(?Entity $moduleTranslation): array
    {
        if(is_null($moduleTranslation)) {
            return [];
        }
        
        $moduleTranslations = $this->moduleTranslations;
        
        $query = $moduleTranslations->find('all')->where([
            'locale_content_id' => $moduleTranslation->locale_content_id
        ]);
        
        foreach($moduleTranslation->institution_types as $k => $type) {
            $query->join([
                'table' => 'module_translation_institution_type',
                'alias' => "mtit{$k}",
                'type' => 'inner',
                'conditions' => [
                    "mtit{$k}.module_translation_id = " . $moduleTranslations->aliasField('id'),
                ]
            ]);
            
            $query->where([
                "mtit{$k}.institution_type_id" => $type->id
            ]);
        }
    
        $translations = $query->toArray();
        
        $withLocaleId = [];
        
        foreach($translations as $translation) {
            $withLocaleId[$translation->locale_id] = $translation;
        }
        
        return $withLocaleId;
    }
}