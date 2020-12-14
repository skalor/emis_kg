<?php

namespace App\Services\PersonalCreator;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ExcelParser
{
    /** @var \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet */
    private $spreadsheet;

    public function __construct(string $filename)
    {
        try {
            $reader = new Xlsx();
            $this->spreadsheet = $reader->load($filename)->getActiveSheet();
            $reader->setReadDataOnly(true);
        } catch(\Exception $e) {
            die($e->getMessage());
        }
    }
    public function getCodes(): array
    {
        $fields = $this->spreadsheet->toArray();

        $codes = [];

        for($i = 1; $i < count($fields); $i++) {
            $code = $fields[$i][$code = 2];
            if($code) {
                $codes[] = $code;
            }
        }

        return $codes;
    }
}