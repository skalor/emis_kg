<?php

namespace Localization\Controller;

use App\Exceptions\ErrorsException;
use App\Model\Table\LocaleContentsTable;
use App\Services\PluginTranslator\InstitutionTranslator;
use App\Services\PluginTranslator\PluginService;
use Cake\Event\Event;
use Cake\ORM\Entity;
use Institution\Model\Table\TypesTable;
use Localization\Model\Table\ModuleTranslationsTable;

/**
 * @property TypesTable InstitutionTypes
 * @property LocaleContentsTable LocaleContents
 * @property ModuleTranslationsTable ModuleTranslations
 */
class ModuleTranslationsController extends PageController
{
    /**
     * @var ModuleTranslationService
     */
    private $service;
    
    public function initialize()
    {
        parent::initialize();
        
        $this->loadModel('InstitutionTypes');
        $this->loadModel('LocaleContents');
        $this->loadModel('Localization.ModuleTranslations');
    }

    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);

        if(in_array($this->request->param('action'), ['add', 'edit'])) {
            $this->viewBuilder()->template('Localization.Page/add');
        }
        
        $this->isEditable(function() {
            $this->service = new ModuleTranslationService($this->Page);
            $this->searchablePlugins();
            $this->searchableLocaleContents();
            $this->addSelectableInstitutionTypes();
            $this->hiddenFields = ['locale_id', 'translation'];
        });
    }

    public function beforeRender(Event $event)
    {
        parent::beforeRender($event);
        
        $this->isEditable(function() {
            $this->service->addLanguages();
        });
    }

    public function implementedEvents()
    {
        $events = parent::implementedEvents();
        $events['Controller.Page.onRenderPlugin'] = 'onRenderPlugin';

        return $events;
    }

    public function onRenderPlugin(Event $event, Entity $entity)
    {
        $this->Page->get('plugin')->setAttributes(['onchange' => '$("#reload").click();']);

        $session = $this->request->session();
        if (in_array($this->request->data('submit'), ['reload']) && $session->check('alert')) {
            $session->delete('alert');
        }

        if ($entity->plugin === 'Institution') {
            $this->addSelectableInstitutionTypes(true);
        }
    }

    public function index(): void
    {
        $this->Page->get('locale_content_id')->setSortable(true);
        
        parent::index();
    }
    
    public function view($id): void
    {
        parent::view($id);

        $translation = $this->Page->getData();
        
        if($translation->plugin === InstitutionTranslator::PLUGIN) {
            $this->addSelectableInstitutionTypes();
        }
    }

    public function edit($id): void
    {
        $id = $this->Page->decode($id)['id'];
        $this->Page->setVar('data', $this->ModuleTranslations->get($id, ['contain' => 'InstitutionTypes']));

        try {
            $reload = $this->request->data('submit') === 'reload' ? true : false;
            $this->service->changeTranslations($reload);
            $this->redirectWith('The record has been updated successfully.');
        } catch(ErrorsException $e) {
            $this->Page->setVar('error', $e->getErrors());
            $this->redirectWith('The record is not updated due to errors encountered.', 'error');
        }
    }

    public function add(): void
    {
        $this->Page->setVar('data', $this->ModuleTranslations->newEntity());
        try {
            $reload = $this->request->data('submit') === 'reload' ? true : false;
            $this->service->changeTranslations($reload);
            $this->redirectWith('The record has been added successfully.', 'success');
        } catch(ErrorsException $e) {
            $this->Page->setVar('error', $e->getErrors());
            $this->redirectWith('The record is not created due to errors encountered.', 'error');
        }
    }
    
    private function addSelectableInstitutionTypes(bool $isInstitution = false): void
    {
        $page = $this->Page;

        if(!$isInstitution) {
            return;
        }

        $page
            ->addNew('institution_types')
            ->setLabel('Institution Types')
            ->setControlType('select')
            ->setAttributes('multiple', true);

        $page->move('institution_types')->after('plugin');

        if($this->request->action === 'view') {
            return;
        }
        
        $institutionTypes = $this->InstitutionTypes
            ->find('list')
            ->toArray();

        $page->get('institution_types')->setOptions($institutionTypes, false);
    }
    
    private function searchableLocaleContents(): void
    {
        $localeContents = $this->LocaleContents->find('list', [
            'valueField' => 'en',
            'order' => 'en',
        ])->toArray();
        
        array_walk($localeContents, function($locale, $id) {
            $localeContents[$id] = ['value' => $id, 'text' => $locale, 'data-tokens' => $locale];
        });
        
        $this->selectable
            ->new('locale_content_id', $localeContents)
            ->searchable()
        ;
    }
    
    private function searchablePlugins(): void
    {
        $plugins = (new PluginService())->getHumanizedPlugins();
        
        $selectable = [];
        
        foreach($plugins as $plugin => $humanized) {
            $selectable[] = ['value' => $plugin, 'text' => $humanized, 'data-tokens' => $humanized];
        }
        
        $this->selectable->new('plugin', $selectable)->searchable();
    }
}