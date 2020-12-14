<?php declare(strict_types=1);

namespace App\Services\DataDumper;

class PinExporter
{
    public function export(): void
    {
        $validatorFrom = function($value): bool {
            if(!is_numeric($value) || strlen($value) !== 14) {
                return false;
            }
    
            return $value[0] == 1 || $value[0] == 2;
        };
    
        $validatorTo = function($value): bool {
            return empty($value);
        };
    
        $fieldExporter = new DataFieldExporter('security_users');
        $fieldExporter->setValidatorFrom($validatorFrom);
        $fieldExporter->setValidatorTo($validatorTo);
        $fieldExporter->export(['identity_number', 'postal_code', 'third_name'], 'pin');
        
        $fieldExporter = clone $fieldExporter;
        $fieldExporter->setFromTable('user_contacts');
        $fieldExporter->setToTable('security_users');
        $fieldExporter->export('value', 'pin');
    }
}