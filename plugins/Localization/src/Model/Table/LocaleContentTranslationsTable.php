<?php

namespace Localization\Model\Table;

use App\Model\Table\AppTable;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

class LocaleContentTranslationsTable extends AppTable
{
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->belongsTo('Locales');
        $this->belongsTo('LocaleContents');
    }
}