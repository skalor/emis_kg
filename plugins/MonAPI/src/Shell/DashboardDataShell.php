<?php
namespace MonAPI\Shell;

use Cake\Console\Shell;
use Cake\Controller\ComponentRegistry;
use MonAPI\Controller\Component\DashboardComponent;

class DashboardDataShell extends Shell
{
    private $dashboard;

    public function initialize()
    {
        parent::initialize();
        $this->dashboard = new DashboardComponent(new ComponentRegistry(), []);
    }

    public function main()
    {
        $this->out('Generating JSON file...');
        $result = [];
        if ($this->dashboard) {
            $result = $this->dashboard->generateJson();
        }

        if ($result) {
            $this->out('JSON file generated successfully!');
        } else {
            $this->out('Error: Can not generate JSON file');
        }
    }
}
