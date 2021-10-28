<?php

namespace Kachuru\Thread;

class StaticThreadsNumber implements ThreadsNumber
{
    private $threadsNumber;

    public function __construct(int $threadsNumber)
    {
        $this->threadsNumber = $threadsNumber;
    }

    public function getThreadsNumber(): int
    {
        return $this->threadsNumber;
    }

    public function endAllProcessing(): void
    {
        $this->threadsNumber = 0;
    }
}
