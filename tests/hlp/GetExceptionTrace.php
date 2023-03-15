<?php

declare(strict_types=1);

namespace axy\backtrace\tests\hlp;

use axy\backtrace\ExceptionTrace;

/**
 * Separate method, because test*() methods invoked via Reflection
 */
class GetExceptionTrace
{
    public array $native;
    public int $line;
    public string $file;
    public ExceptionTrace $trace;

    public function __construct()
    {
        $this->native = debug_backtrace();
        $this->trace = new ExceptionTrace();
        $this->line = __LINE__ - 1;
        $this->file = __FILE__;
    }
}
