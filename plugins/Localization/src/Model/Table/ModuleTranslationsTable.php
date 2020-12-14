<?php

namespace Localization\Model\Table;

use App\Model\Table\AppTable;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\Utility\Inflector;
use Cake\Validation\Validator;

class ModuleTranslationsTable extends AppTable
{
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->belongsTo('Locales');
        $this->belongsTo('LocaleContents');

        $this->belongsToMany('InstitutionTypes', [
            'foreignKey' => 'module_translation_id',
            'targetForeignKey' => 'institution_type_id',
            'through' => 'ModuleTranslationInstitutionType',
            'dependent' => true,
            'cascadeCallbacks' => true
        ]);
    }
    
    public function buildRules(RulesChecker $rules)
    {
        $generals = [
            'translation',
            'locale_id',
            'plugin',
        ];

        $rules->add(function($entity, $options) use($rules, $generals) {
            if($entity->plugin !== 'Institution' && !empty($entity->institution_types)) {
                return false;
            }

            $message = implode(array_map(function(string $field) {
                $field = str_replace('_id', '', $field);
                $field = Inflector::humanize($field);

                return __($field);
            }, $generals), ', ');
            $message .= ' combination must be unique!';

            $rule = $rules->isUnique($generals, $message);
            return $rule($entity, $options);
        }, [
            'message' => 'You must set a plugin institution for an institution type'
        ]);

        return $rules;
    }
    
    public function findView(Query $query)
    {
        $query->contain(['InstitutionTypes']);

        return $query;
    }

    public function findEdit(Query $query)
    {
        $query->contain(['InstitutionTypes']);
    
        return $query;
    }
}