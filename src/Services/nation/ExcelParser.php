<?php

namespace App\Services\nation;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ExcelParser
{
    /** @var \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet */
    private $spreadsheet;

    public function __construct(string $filename)
    {
        $reader = new Xlsx();
        $this->spreadsheet = $reader->load($filename)->getActiveSheet();
        $reader->setReadDataOnly(true);

    }

    function getInfo(){
        return $this->spreadsheet->toArray();
    }
}