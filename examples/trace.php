<?php

declare(strict_types=1);

namespace axy\backtrace\examples;

use axy\backtrace\Trace;

require_once __DIR__ . '/../index.php';

class TraceExample
{
    public function one(int $level = 5): Trace
    {
        if ($level <= 0) {
            return new Trace();
        }
        return $this->two($level - 1);
    }

    private function two(int $level): Trace
    {
        return $this->one($level - 1);
    }
}

$trace = (new TraceExample())->one();

foreach ($trace as $item) {
    print_r($item);
}
