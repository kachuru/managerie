<?php

namespace Kachuru\Thread;

use Symfony\Component\Console\Output\OutputInterface;

class Signal
{
    private $config;

    private $output;

    private $threadsLimit;

    public function __construct(Config $config, OutputInterface $output)
    {
        $this->config = $config;
        $this->output = $output;

        $this->threadsLimit = $config->getStartThreads();

        foreach ([SIGTERM, SIGHUP, SIGINT, SIGUSR1, SIGUSR2] as $trapSignal) {
            pcntl_signal($trapSignal, array($this, 'trapSignal'));
        }
    }

    public function getThreadsLimit()
    {
        return $this->threadsLimit;
    }

    public function tick($sleepTime)
    {
        pcntl_signal_dispatch();
        usleep($sleepTime);
    }

    public function terminateAllThreads()
    {
        $this->writeln('Terminating all children');
        $this->stopAllThreads();
    }

    public function stopAllThreads()
    {
        $this->threadsLimit = 0;
        $this->writeln('Waiting for all children to finish');
    }

    protected function trapSignal(int $signal)
    {
        $conf = [
            SIGINT => [$this, 'terminateAllThreads'],
            SIGHUP => [$this, 'terminateAllThreads'],
            SIGTERM => [$this, 'terminateAllThreads'],
            SIGUSR1 => [$this, 'addThread'],
            SIGUSR2 => [$this, 'removeThread'],
        ];

        $conf[$signal]();
    }

    protected function addThread()
    {
        if ($this->threadsLimit < $this->config->getMaxThreads()) {
            $this->threadsLimit++;
            $this->writeln('Spawning a child');
        }
    }

    protected function removeThread()
    {
        if ($this->threadsLimit > $this->config->getMinThreads()) {
            $this->threadsLimit--;
            $this->writeln('Despawning a child');
        }
    }

    private function writeln($message)
    {
        $this->output->writeln(sprintf('[%d] %s', getmypid(), $message), OutputInterface::VERBOSITY_VERBOSE);
    }
}
