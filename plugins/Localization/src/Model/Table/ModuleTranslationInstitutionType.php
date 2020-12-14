<?php

namespace Localization\Model\Table;

use App\Model\Table\AppTable;

class ModuleTranslationInstitutionType extends AppTable
{
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->belongsTo('InstitutionTypes');
        $this->belongsTo('ModuleTranslations');

        $this->addBehavior('CompositeKey');
    }
}