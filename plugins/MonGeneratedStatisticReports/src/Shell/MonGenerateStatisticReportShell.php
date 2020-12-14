<?php
namespace MonGeneratedStatisticReports\Shell;

use Cake\Console\Shell;
use Cake\Controller\ComponentRegistry;
use MonGeneratedStatisticReports\Controller\Component\GeneratorComponent;

class MonGenerateStatisticReportShell extends Shell
{
    private $generator;

    public function initialize()
    {
        parent::initialize();
        $this->generator = new GeneratorComponent(new ComponentRegistry(), []);
    }

    public function main()
    {
        error_reporting(E_ALL);
        set_time_limit(3600);
        ini_set('pcre.backtrack_limit', 10000000);
        $id = $this->args[0];
        if ($id) {
            $this->out(date('Y-m-d H:i:s') . ' - Start generating report for id: ' . $id . ' ...');
            try {
                $this->generator->generate($id);
            } catch (\Exception $exception) {
                $this->out($exception->getMessage());
            }
            $this->out(date('Y-m-d H:i:s') . ' - End generating report');
        }
    }
}
