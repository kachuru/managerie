<?php

namespace Kachuru\Thread;

class ThreadControl
{
    private $threadsNumber;

    private $output;

    private $threads = [];

    private $finishedThreadCount = 0;

    private $lastRowPosition = 0;

    public function __construct(ThreadsNumber $threadsNumber, ConsoleWriter $output)
    {
        $this->threadsNumber = $threadsNumber;
        $this->output = $output;
        $this->output->setAperture($this->threadsNumber->getThreadsNumber());
    }

    public function run(ThreadableScript $runScript, int $timesToRun, int $sleepTime): void
    {
        do {
            $this->handleStartingThreads($runScript, $timesToRun);
            $this->handleFinishingThreads($timesToRun);
            usleep($sleepTime);
        } while ($this->shouldBeRunning());
    }

    protected function handleStartingThreads(ThreadableScript $runScript, int $timesToRun): void
    {
        if ($this->threadsUnderAllowedNumber() && !$this->timesToRunReached($timesToRun)) {
            $this->spawn($runScript);
        }
    }

    protected function handleFinishingThreads($timesToRun): void
    {
        $finishedPid = pcntl_waitpid(0, $status, WNOHANG);
        if ($finishedPid > 0) {
            $this->lastRowPosition = array_search($finishedPid, $this->threads) + 1;
            $this->writeOnRow(
                sprintf('Thread %d has finished with status %d', $finishedPid, $status),
                $this->lastRowPosition
            );

            unset($this->threads[array_search($finishedPid, $this->threads)]);
            ++$this->finishedThreadCount;

            if ($this->timesToRunReached($timesToRun) && $this->threadsNumber->getThreadsNumber() > 0) {
                $this->threadsNumber->endAllProcessing();
            }
        }
    }

    protected function spawn(ThreadableScript $runScript): int
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

        $row = $this->findRow($pid);
        $this->writeOnRow(
            sprintf('Forked %d; %d threads; %dB consumed', $pid, $this->getThreadCount(), memory_get_usage()),
            $row
        );
        return $pid;
    }

    private function threadsUnderAllowedNumber(): bool
    {
        return $this->getThreadCount() < $this->threadsNumber->getThreadsNumber();
    }

    private function getThreadCount(): int
    {
        return count($this->threads);
    }

    private function getTotalThreadCount(): int
    {
        return $this->getThreadCount() + $this->finishedThreadCount;
    }

    private function timesToRunReached($timesToRun): bool
    {
        return $timesToRun > 0 && $this->getTotalThreadCount() == $timesToRun;
    }

    private function shouldBeRunning(): bool
    {
        return $this->threadsNumber->getThreadsNumber() > 0 || $this->getThreadCount() > 0;
    }

    private function writeOnRow($message, $row = 1): void
    {
        $this->output->writeOnRow(sprintf('[%d] %s', getmypid(), $message), $row, 0);
    }

    protected function findRow($pid): int
    {
        return ($this->lastRowPosition > 0) ? $this->lastRowPosition : $this->getThreadCount();
    }
}
