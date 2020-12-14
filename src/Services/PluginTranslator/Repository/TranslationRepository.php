<?php

namespace App\Services\PluginTranslator\Repository;

use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\Table;

abstract class TranslationRepository
{
    private $localeRepository;
    private $contains = [];

    public function __construct()
    {
        $this->localeRepository = new LocaleRepository();
    }

    protected function translation(int $messageId, array $conditions): Query
    {
        $localeId = [
            'locale_id' => $this->localeRepository->getCurrentLocaleId(),
            'locale_content_id' => $messageId,
        ];

        $conditions = array_merge($localeId, $conditions);

        $table = $this->table()
            ->find('all')
            ->where($conditions);

        if(!empty($this->contains)) {
            $table->contain($this->contains);
        }

        return $table;
    }

    protected function contain(string $table): self
    {
        $this->contains[] = $table;

        return $this;
    }

    abstract protected function table(): Table;
}