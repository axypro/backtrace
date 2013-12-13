<?php
/**
 * @package axy\backtrace
 */

namespace axy\backtrace\tests;

use axy\backtrace\Trace;

/**
 * @coversDefaultClass axy\backtrace\Trace
 */
class TraceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @covers ::__get
     */
    public function testConstructByArray()
    {
        $items = [
            ['file' => '/test/index.php',],
            ['file' => '/test/package.php', 'line' => 10,],
        ];
        $trace = new Trace($items);
        $this->assertEquals($items, $trace->items);
        $this->assertEquals($items, $trace->originalItems);
    }

    /**
     * @covers ::__construct
     * @covers ::__get
     */
    public function testConstructByNull()
    {
        $trace = new Trace();
        $this->assertEquals(\debug_backtrace(), $trace->items);
        $this->assertEquals($trace->items, $trace->originalItems);
    }

    /**
     * @covers ::__construct
     * @covers ::__get
     */
    public function testConstructByTraceInstance()
    {
        $items = [
            ['file' => '/test/index.php',],
            ['file' => '/test/package.php', 'line' => 10,],
        ];
        $trace1 = new Trace($items);
        $trace2 = new Trace($trace1);
        $this->assertNotSame($trace1, $trace2);
        $this->assertEquals($trace1->items, $trace2->items);
        $this->assertEquals($trace1->originalItems, $trace2->originalItems);
    }

    /**
     * @covers ::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testConstructByInvalidArgumet()
    {
        return new Trace(5);
    }

    /**
     * @covers ::__isset
     */
    public function testMagicIsset()
    {
        $trace = new Trace([]);
        $this->assertTrue(isset($trace->items));
        $this->assertFalse(isset($trace->unknown));
    }

    /**
     * @covers ::__set
     * @expectedException \LogicException
     */
    public function testMagicSetForbidden()
    {
        $trace = new Trace([]);
        $trace->items = [];
    }

    /**
     * @covers ::__unset
     * @expectedException \LogicException
     */
    public function testMagicUnsetForbidden()
    {
        $trace = new Trace([]);
        unset($trace->items);
    }
}
