<?php

namespace Kachuru\Thread;

interface ThreadsNumber
{
    public function getThreadsNumber(): int;

    public function endAllProcessing(): void;
}
