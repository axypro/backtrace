<?php
/* Separate method, because test*() methods invoked via Reflection */

namespace axy\backtrace\tests\hlp;

use axy\backtrace\ExceptionTrace;

class GetExceptionTrace
{
    /**
     * @var array
     */
    public $native;

    /**
     * @var int
     */
    public $line;

    /**
     * @var string
     */
    public $file;

    /**
     * @var \axy\backtrace\ExceptionTrace
     */
    public $trace;

    public function __construct()
    {
        $this->native = debug_backtrace();
        $this->trace = new ExceptionTrace();
        $this->line = __LINE__ - 1;
        $this->file = __FILE__;
    }
}
