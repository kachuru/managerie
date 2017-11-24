<?php

namespace Kachuru\Thread;

class VariableThreadsConfig
{
    private $startThreads;

    private $maxThreads;

    private $minThreads;

    public function __construct(
        int $startThreads,
        int $maxThreads,
        int $minThreads = 1
    ) {
        $this->startThreads = $startThreads;
        $this->maxThreads = $maxThreads;
        $this->minThreads = $minThreads;
    }

    public function getStartThreads(): int
    {
        return $this->startThreads;
    }

    public function getMaxThreads(): int
    {
        return $this->maxThreads;
    }

    public function getMinThreads(): int
    {
        return $this->minThreads;
    }
}
