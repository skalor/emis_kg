<?php
namespace MonAPI\Controller\Component;

use Cake\Controller\Component;

class QueueComponent extends Component
{
    private $session;
    private $queues;

    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->session = $this->request->session();
    }

    public function getQueue(string $item = null)
    {
        if ($this->session->check('MonAPI.Queues')) {dd($this->session->read('MonAPI.Queues'));
            $this->queues = $this->session->read('MonAPI.Queues');
        }

        if ($item && isset($this->queues[$item])) {
            return $this->queues[$item];
        }

        return $this->queues;
    }

    public function setQueue(string $item = null, array $value = [])
    {
        $this->queues = $this->getQueue();

        if ($item) {
            $this->queues[$item] = $value;
        }

        $this->session->write('MonAPI.Queues', $this->queues);

        return true;
    }

    public function removeQueue(string $item = null)
    {
        $this->queues = $this->getQueue();

        if ($item && isset($this->queues[$item])) {
            unset($this->queues[$item]);
        }

        $this->session->write('MonAPI.Queues', $this->queues);

        return true;
    }
}