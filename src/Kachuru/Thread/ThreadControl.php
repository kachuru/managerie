<?php

namespace Kachuru\Thread;

use Symfony\Component\Console\Output\OutputInterface;

class ThreadControl
{
    private $threadsNumber;

    private $output;

    private $threads = [];

    private $finishedThreadCount = 0;

    public function __construct(int $threadsNumber, OutputInterface $output)
    {
        $this->threadsNumber = $threadsNumber;
        $this->output = $output;
    }

    public function run(ThreadableScript $runScript, int $timesToRun, int $sleepTime)
    {
        do {
            $this->handleStartingThreads($runScript, $timesToRun);
            $this->handleFinishedThreads($timesToRun);
            usleep($sleepTime);
        } while ($this->shouldBeRunning());
    }

    protected function handleStartingThreads(ThreadableScript $runScript, int $timesToRun): void
    {
        if ($this->threadsUnderAllowedNumber() && !$this->timesToRunReached($timesToRun)) {
            $this->spawn($runScript);
        }
    }

    protected function handleFinishedThreads($timesToRun): void
    {
        $finishedPid = pcntl_waitpid(0, $status, WNOHANG);
        if ($finishedPid > 0) {
            $this->writeln(sprintf('Thread %d has finished with status %d', $finishedPid, $status));

            unset($this->threads[array_search($finishedPid, $this->threads)]);
            ++$this->finishedThreadCount;

            if ($this->timesToRunReached($timesToRun) && $this->threadsNumber > 0) {
                $this->threadsNumber = 0;
            }
        }
    }

    protected function spawn(ThreadableScript $runScript)
    {
        $pid = pcntl_fork();

        if ($pid == -1) {
            throw new \RuntimeException('Cannot fork child process');
        }

        if ($pid == 0) {
            // Child does its stuff and then exits
            $runScript->run();
            exit;
        }

        // Parent continues the thread handling
        $this->threads[] = $pid;
        $this->writeln(
            sprintf('Forked %d; %d threads; %dB consumed', $pid, $this->getThreadCount(), memory_get_usage())
        );
        return $pid;
    }

    private function threadsUnderAllowedNumber(): bool
    {
        return $this->getThreadCount() < $this->threadsNumber;
    }

    private function getThreadCount(): int
    {
        return count($this->threads);
    }

    private function getTotalThreadCount()
    {
        return $this->getThreadCount() + $this->finishedThreadCount;
    }

    private function timesToRunReached($timesToRun): bool
    {
        return $timesToRun > 0 && $this->getTotalThreadCount() == $timesToRun;
    }

    private function shouldBeRunning(): bool
    {
        return $this->threadsNumber > 0 || $this->getThreadCount() > 0;
    }

    private function writeln($message)
    {
        $this->output->writeln(sprintf('[%d] %s', getmypid(), $message), OutputInterface::VERBOSITY_VERBOSE);
    }
}
