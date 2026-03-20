<?php

namespace Kachuru\Thread;

use Symfony\Component\Console\Output\OutputInterface;

class SignalControlledThreadsNumber implements ThreadsNumber
{
    private int $threadsNumber;

    private const SIGNAL_ACTIONS = [
        SIGINT => 'terminateAllThreads',
        SIGHUP => 'terminateAllThreads',
        SIGTERM => 'terminateAllThreads',
        SIGUSR1 => 'addThread',
        SIGUSR2 => 'removeThread',
    ];

    public function __construct(
        private readonly VariableThreadsConfig $config,
        private readonly OutputInterface $output
    ) {
        $this->threadsNumber = $config->getStartThreads();

        foreach (array_keys(self::SIGNAL_ACTIONS) as $trapSignal) {
            pcntl_signal($trapSignal, array($this, 'trapSignal'));
        }
    }

    public function getThreadsNumber(): int
    {
        return $this->threadsNumber;
    }

    public function tick(): void
    {
        pcntl_signal_dispatch();
    }

    public function terminateAllThreads(): void
    {
        $this->writeln('Terminating all children');
        $this->endAllProcessing();
    }

    public function endAllProcessing(): void
    {
        $this->threadsNumber = 0;
        $this->writeln('Waiting for all children to finish');
    }

    protected function trapSignal(int $signal): void
    {
        call_user_func([$this, self::SIGNAL_ACTIONS[$signal]]);
    }

    protected function addThread(): void
    {
        if ($this->threadsNumber < $this->config->getMaxThreads()) {
            $this->threadsNumber++;
            $this->writeln('Spawning a child');
        }
    }

    protected function removeThread(): void
    {
        if ($this->threadsNumber > $this->config->getMinThreads()) {
            $this->threadsNumber--;
            $this->writeln('Despawning a child');
        }
    }

    private function writeln(string $message): void
    {
        $this->output->writeln(sprintf('[%d] %s', getmypid(), $message), OutputInterface::VERBOSITY_VERBOSE);
    }
}
