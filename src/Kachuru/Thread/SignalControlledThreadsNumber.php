<?php

namespace Kachuru\Thread;

use Symfony\Component\Console\Output\OutputInterface;

class SignalControlledThreadsNumber implements ThreadsNumber
{
    private $config;

    private $output;

    private $threadsNumber;

    private $signalActions = [
        SIGINT => 'terminateAllThreads',
        SIGHUP => 'terminateAllThreads',
        SIGTERM => 'terminateAllThreads',
        SIGUSR1 => 'addThread',
        SIGUSR2 => 'removeThread',
    ];

    public function __construct(VariableThreadsConfig $config, OutputInterface $output)
    {
        $this->config = $config;
        $this->output = $output;

        $this->threadsNumber = $config->getStartThreads();

        foreach (array_keys($this->signalActions) as $trapSignal) {
            pcntl_signal($trapSignal, array($this, 'trapSignal'));
        }
    }

    public function getThreadsNumber(): int
    {
        return $this->threadsNumber;
    }

    public function tick()
    {
        pcntl_signal_dispatch();
    }

    public function terminateAllThreads()
    {
        $this->writeln('Terminating all children');
        $this->endAllProcessing();
    }

    public function endAllProcessing(): void
    {
        $this->threadsNumber = 0;
        $this->writeln('Waiting for all children to finish');
    }

    protected function trapSignal(int $signal)
    {
        call_user_func([$this, $this->signalActions[$signal]]);
    }

    protected function addThread()
    {
        if ($this->threadsNumber < $this->config->getMaxThreads()) {
            $this->threadsNumber++;
            $this->writeln('Spawning a child');
        }
    }

    protected function removeThread()
    {
        if ($this->threadsNumber > $this->config->getMinThreads()) {
            $this->threadsNumber--;
            $this->writeln('Despawning a child');
        }
    }

    private function writeln($message)
    {
        $this->output->writeln(sprintf('[%d] %s', getmypid(), $message), OutputInterface::VERBOSITY_VERBOSE);
    }
}
