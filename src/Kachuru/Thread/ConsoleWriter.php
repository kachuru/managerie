<?php

namespace Kachuru\Thread;

use Symfony\Component\Console\Output\ConsoleOutput;

class ConsoleWriter extends ConsoleOutput
{
    private $aperture = 1;

    /**
     * @param int $lines
     */
    public function setAperture(int $lines = 1): void
    {
        for ($i = 1; $i <= $lines; $i++) {
            $this->writeln('');
        }
        $this->aperture = $lines;
    }

    public function writeOnRow($message, $row, $offset = 0)
    {
        $this->up($this->aperture + 1 - $row);
        if ($offset > 0) {
            $this->right($offset);
        }
        printf("%s\r", $message);
        $this->down($this->aperture + 1 - $row);
    }

    function up($n = 1)
    {
        $this->move($n, 'A');
    }

    function down($n = 1)
    {
        $this->move($n, 'B');
    }

    function right($n = 1)
    {
        $this->move($n, 'C');
    }

    function left($n = 1)
    {
        $this->move($n, 'D');
    }

    function move($n, $d)
    {
        echo "\033[".$n.$d;
    }
}
