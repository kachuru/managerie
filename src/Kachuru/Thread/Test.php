<?php

namespace Kachuru\Thread;

use Symfony\Component\Console\Output\OutputInterface;

class Test implements ThreadableScript
{
    public function __construct(
        private readonly OutputInterface $output
    ) {
    }

    public function run(): void
    {
        $duration = mt_rand(3, 7);
        $this->output->writeln(sprintf('Sleeping for %d', $duration));
        sleep($duration);
    }
}
