<?php
namespace Institution\Model\Table;

use ArrayObject;

use Cake\ORM\Query;
use Cake\ORM\Entity;
use Cake\Event\Event;
use Cake\Validation\Validator;

use App\Model\Table\ControllerActionTable;
use App\Model\Traits\MessagesTrait;

class InstitutionSignedDocumentsTable extends ControllerActionTable
{
    use MessagesTrait;

    public function initialize(array $config)
    {

        parent::initialize($config);

        $this->belongsTo('Institutions', ['className' => 'Institution.Institutions', 'foreignKey' => 'institution_id']);

        $this->addBehavior('ControllerAction.FileUpload', [
            'size' => '2MB',
            'contentEditable' => false,
            'allowable_file_types' => 'all',
            'useDefaultName' => true
        ]);

        $this->addBehavior('ControllerAction.FileUploadHash', [
            'size' => '2MB',
            'contentEditable' => false,
            'allowable_file_types' => 'all',
            'useDefaultName' => true
        ]);


        if ($this->behaviors()->has('ControllerAction')) {
            $this->behaviors()->get('ControllerAction')->config([
                'actions' => [
                    'download' => ['show' => true] // to show download on toolbar
                ]
            ]);
        }

        $this->toggle('edit', false);
        $this->toggle('remove', false);
    }

    public function indexBeforeAction(Event $event, ArrayObject $extra)
    {
        $this->field('file_name', ['visible' => false]);
        $this->field('created_user_id', ['visible' => true]);
        $this->field('file_name_hash', ['visible' => false]);
        $this->field('created', [
            'type' => 'datetime',
            'visible' => true
        ]);

        $this->setFieldOrder([
            'name',
            'date_on_file',
            'created',
            'created_user_id'
        ]);
    }

    public function viewAfterAction(Event $event, Entity $entity, ArrayObject $extra)
    {
        $this->field('file_name', ['visible' => false]);
        $this->field('file_name_hash', ['visible' => false]);
        //$this->field('file_content', ['visible' => false]);
        //$this->field('file_content_hash', ['visible' => false]);


    }

    public function addEditAfterAction(Event $event, Entity $entity, ArrayObject $extra)
    {
        $this->field('file_name', ['visible' => false]);
        $this->field('file_name_hash', ['visible' => false]);
    }

    public function onGetFileType(Event $event, Entity $entity)
    {
        return $this->getFileTypeForView($entity->file_name);
    }

    public function onUpdateActionButtons(Event $event, Entity $entity, array $buttons)
    {
        $buttons = parent::onUpdateActionButtons($event, $entity, $buttons);

        $indexAttr = ['role' => 'menuitem', 'tabindex' => '-1', 'escape' => false];
        $downloadUrl = [
            'plugin' => 'Institution',
            'controller' => 'Institutions',
            'action' => $this->alias,
            'institutionId' => $this->paramsEncode(['id' => $entity->institution_id]),
            '0' => 'download',
            '1' => $this->paramsEncode(['id' => $entity->id])
        ];

        $downloadHashUrl = [
            'plugin' => 'Institution',
            'controller' => 'Institutions',
            'action' => $this->alias,
            'institutionId' => $this->paramsEncode(['id' => $entity->institution_id]),
            '0' => 'download_hash',
            '1' => $this->paramsEncode(['id' => $entity->id])
        ];

        $buttons['download'] = [
            'label' => '<i class="fa kd-download"></i>'.__('Download'),
            'attr' => $indexAttr,
            'url' => $downloadUrl
        ];

        $buttons['download_hash'] = [
            'label' => '<i class="fa kd-download"></i>'.__('Download Hash'),
            'attr' => $indexAttr,
            'url' => $downloadHashUrl
        ];

        return $buttons;
    }
}
