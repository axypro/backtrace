<?php
/**
 * @package axy\backtrace
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

declare(strict_types=1);

namespace axy\backtrace\tests\hlp;

use axy\backtrace\ExceptionTrace;

/**
 * Separate method, because test*() methods invoked via Reflection
 */
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
     * @var ExceptionTrace
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
