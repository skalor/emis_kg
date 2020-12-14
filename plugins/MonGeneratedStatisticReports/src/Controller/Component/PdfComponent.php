<?php
namespace MonGeneratedStatisticReports\Controller\Component;

use Cake\Controller\Component;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class PdfComponent extends Component
{
    private $writer;
    private $template = '';

    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->writer = new Mpdf(['orientation' => 'L']);
    }

    public function setTemplate(string $template)
    {
        $this->template = $template;

        return $this;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function render()
    {
        $this->writer->WriteHTML($this->getTemplate());

        return $this->writer->Output(time() . '.pdf', Destination::STRING_RETURN);
    }
}
