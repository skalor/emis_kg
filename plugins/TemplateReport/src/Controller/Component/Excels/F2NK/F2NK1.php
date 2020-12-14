<?php
namespace TemplateReport\Controller\Excels\Component;

use TemplateReport\Controller\Excels\Component\F2NK;


class F2NK1 extends F2NK {

    protected $rowHeaderTitles = [
        '3. Студенттердин жашы боюнча санынын болунушу (отчеттук-ж. 01.01. толук жашы жетилгендердин саны), адам',
        '3. Распределение численности студентов по возрасту (число полных лет на 01.01. отчетного года), человек'
    ];

    protected $rowHeaderTable = [
        '', 'Саптын коду Код строки', '14-жаш жана кичуу 14 лет и менее', '15 жаш 15 лет', '16 жаш 16 лет', '17 жаш 17 лет', '18 жаш 18 лет', '19 жаш 19 лет', '20 жаш 20 лет', '21 жаш 21 лет', '22 жаш 22 лет', '24 жаш 24 лет', '25-35 жаш 25-35 лет', '36 жаш жана улуу 36 лет и старше', 'Жыйынтыгы Итого',
    ];

    protected $rowSubHeaderTable = [
        '', 'А', 'Б', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14',
    ];

    protected $leftRow = [
        'Жашы боюнча студенттердин саны Численность студентов в возрасте',
        'алардын ичинен аялдар из них: женщины',
        ];

    protected function createExcelPart($sheet) {

//        add header title
        $this->positionX = 1;
        foreach ($this->rowHeaderTitles as $value) {
            $sheet->setCellValueByColumnAndRow($this->positionX,$this->positionY,$value);
            $this->positionX++;
        }
        $this->positionY++;

//        add header table
        $this->positionX = 1;
        foreach ($this->rowHeaderTable as $value) {
            $sheet->setCellValueByColumnAndRow($this->positionX,$this->positionY,$value);
            $this->positionX++;
        }
        $this->positionY++;

//        add sub header table
        $this->positionX = 1;
        foreach ($this->rowSubHeaderTable as $value) {
            $sheet->setCellValueByColumnAndRow($this->positionX,$this->positionY,$value);
            $this->positionX++;
        }
        $this->positionY++;

//        add count all value
        $this->positionX = 2;
        $sheet->setCellValueByColumnAndRow(1,$this->positionY,'Жашы боюнча студенттердин саны Численность студентов в возрасте');
        foreach ($this->rowSubHeaderTable as $value) {
            $sheet->setCellValueByColumnAndRow($this->positionX,$this->positionY,$value);
            $this->positionX++;
        }

//        add count by woman
        $this->positionX = 2;
        $sheet->setCellValueByColumnAndRow(1,$this->positionY,'алардын ичинен аялдар из них: женщины');
        foreach ($this->rowSubHeaderTable as $value) {
            $sheet->setCellValueByColumnAndRow($this->positionX,$this->positionY,$value);
            $this->positionX++;
        }
        $this->positionY++;
    }
}
?>