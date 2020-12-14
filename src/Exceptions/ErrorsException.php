<?php declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class ErrorsException extends Exception
{
    /**
     * @var array
     */
    private $errors;
    
    public function __construct(string $message, array $errors = [])
    {
        parent::__construct($message);
        $this->errors = $errors;
    }
    
    public function getErrors(): array
    {
        return $this->errors;
    }
}