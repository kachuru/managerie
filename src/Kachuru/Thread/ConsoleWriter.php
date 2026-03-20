<?php

namespace Kachuru\Thread;

use Symfony\Component\Console\Output\ConsoleOutput;

class ConsoleWriter extends ConsoleOutput
{
    private int $aperture = 1;

    public function setAperture(int $lines = 1): void
    {
        for ($i = 1; $i <= $lines; $i++) {
            $this->writeln('');
        }
        $this->aperture = $lines;
    }

    public function writeOnRow(string $message, int $row, int $offset = 0): void
    {
        $this->up($this->aperture + 1 - $row);
        if ($offset > 0) {
            $this->right($offset);
        }
        printf("%s\r", $message);
        $this->down($this->aperture + 1 - $row);
    }

    public function up(int $n = 1): void
    {
        $this->move($n, 'A');
    }

    public function down(int $n = 1): void
    {
        $this->move($n, 'B');
    }

    public function right(int $n = 1): void
    {
        $this->move($n, 'C');
    }

    public function left(int $n = 1): void
    {
        $this->move($n, 'D');
    }

    private function move(int $n, string $d): void
    {
        echo "\033[".$n.$d;
    }
}
