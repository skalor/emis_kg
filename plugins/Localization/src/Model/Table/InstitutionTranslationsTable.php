<?php

namespace Localization\Model\Table;

use App\Model\Table\AppTable;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;

class InstitutionTranslationsTable extends AppTable
{
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->belongsTo('Locales');
        $this->belongsTo('LocaleContents');
        $this->belongsTo('InstitutionTypes');
    }

    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->isUnique([
            'locale_id',
            'locale_content_id',
            'institution_type_id'
        ], 'message'));

        return $rules;
    }
}