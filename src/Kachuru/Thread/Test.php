<?php

namespace Kachuru\Thread;

use Symfony\Component\Console\Output\OutputInterface;

class Test implements ThreadableScript
{
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function run()
    {
        $duration = mt_rand(3, 7);
        $this->output->writeln(sprintf('[%d] Sleeping for %d', getmypid(), $duration));
        sleep($duration);
    }
}
