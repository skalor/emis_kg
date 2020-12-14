<?php declare(strict_types=1);

namespace App\Services\DataDumper;


interface DumperInterface
{
    public function dump(int $fieldId, string $fieldName): void;
}