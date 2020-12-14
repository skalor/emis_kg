<?php
namespace TemplateReport\Controller\Excels\Component;


use DateInterval;
use DateTime;

class F2NKComponent {

    protected $templateClasses = [];
    protected $rootFolder = 'import';
    protected $positionStart = 'A1';
    protected $positionX = 1;
    protected $positionY = 1;
    protected $sheet;
    protected $PHPExcel;
    protected $request;
    protected $templateReportTable;


    function __construct($request,$templateReportTable)
    {
        $PHPExcel = new \PHPExcel();
        $PHPExcel->setActiveSheetIndex(0);
        $this->request              = $request;
        $this->templateReportTable  = $templateReportTable;
        $this->PHPExcel             = $PHPExcel;
        $this->sheet                = $PHPExcel->getActiveSheet();
    }

    function addExcel(\TemplateReport\Controller\F2NK $templateClass){
        $this->templateClasses[] = $templateClass;
    }

//    abstract protected function createExcelPart();
    function buildExcel() {
        if(empty($this->templateClasses)) {
            throw new \Exception('Not added to worksheet part elements');
        }

        foreach ($this->templateClasses as $templateClass) {
            $templateClass->createExcelPart($this->sheet);
            $this->prepareDownload();
        }
    }

    protected function prepareDownload()
    {
        $folder = WWW_ROOT . $this->rootFolder;
        if (!file_exists($folder)) {
            umask(0);
            mkdir($folder, 0777);
        } else {
            $fileList = array_diff(scandir($folder), array('..', '.'));
            $now = new DateTime();
            // delete all old files that are more than one hour old
            $now->sub(new DateInterval('PT1H'));

            foreach ($fileList as $file) {
                $path = $folder . DS . $file;
                $timestamp = filectime($path);
                $date = new DateTime();
                $date->setTimestamp($timestamp);

                if ($now > $date) {
                    if (!unlink($path)) {
                        $this->_table->log('Unable to delete ' . $path, 'export');
                    }
                }
            }
        }

        return $folder;
    }

    function download($excelFile) {
        $folder     = WWW_ROOT . $this->rootFolder;
        $excelPath  = $folder . DS . $excelFile;
        $filename   = basename($excelPath);

        $objWriter = new \PHPExcel_Writer_Excel2007($this->PHPExcel);
        $objWriter->save($excelPath);

        header("Pragma: public", true);
        header("Expires: 0"); // set expiration time
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Disposition: attachment; filename=" . $filename);
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: " . filesize($excelPath));
        echo file_get_contents($excelPath);
    }
}
?>