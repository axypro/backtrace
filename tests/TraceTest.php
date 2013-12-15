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
     * @covers ::normalize
     */
    public function testNormalize()
    {
        $items = [
            ['function' => 'preg_replace_callback',],
            ['file' => '/test/package.php', 'line' => 10,],
        ];
        $expected = [
            [
                'function' => 'preg_replace_callback',
                'line' => null,
                'file' => null,
                'class' => null,
                'object' => null,
                'type' => null,
                'args' => [],
            ],
            [
                'function' => null,
                'line' => 10,
                'file' => '/test/package.php',
                'class' => null,
                'object' => null,
                'type' => null,
                'args' => [],
            ],
        ];
        $trace = new Trace($items);
        $trace->normalize();
        $this->assertEquals($expected, $trace->items);
        $this->assertEquals($items, $trace->originalItems);
    }

    /**
     * @covers ::truncateByLimit
     */
    public function testTruncateByLimit()
    {
        $items = [
            ['function' => 'preg_replace_callback',],
            ['file' => '/test/package.php', 'line' => 10,],
            ['function' => 'preg_replace_callback',],
        ];
        $trace = new Trace($items);
        $this->assertFalse($trace->truncateByLimit(5));
        $this->assertEquals($items, $trace->items);
        $this->assertFalse($trace->truncateByLimit(3));
        $this->assertEquals($items, $trace->items);
        $this->assertTrue($trace->truncateByLimit(2));
        $expected2 = [
            ['function' => 'preg_replace_callback',],
            ['file' => '/test/package.php', 'line' => 10,],
        ];
        $this->assertEquals($expected2, $trace->items);
        $this->assertFalse($trace->truncateByLimit(2));
        $this->assertEquals($expected2, $trace->items);
        $this->assertTrue($trace->truncateByLimit(1));
        $expected1 = [
            ['function' => 'preg_replace_callback',],
        ];
        $this->assertEquals($expected1, $trace->items);
        $this->assertEquals($items, $trace->originalItems);
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

    /**
     * @covers ::count
     */
    public function testCountable()
    {
        $items = [
            ['file' => 'one.php'],
            ['file' => 'two.php'],
            ['file' => 'three.php'],
        ];
        $trace = new Trace($items);
        $this->assertCount(3, $trace);
        $trace->truncateByLimit(1);
        $this->assertCount(1, $trace);
    }

    /**
     * @covers ::getIterator
     */
    public function testTraversable()
    {
        $items = [
            ['file' => 'one.php'],
            ['file' => 'two.php'],
            ['file' => 'three.php'],
        ];
        $trace = new Trace($items);
        $this->assertEquals($items, \iterator_to_array($trace));
    }

    /**
     * @covers ::offsetExists
     * @covers ::offsetGet
     */
    public function testArrayAccess()
    {
        $items = [
            ['file' => 'one.php'],
            ['file' => 'two.php'],
            ['file' => 'three.php'],
        ];
        $trace = new Trace($items);
        $this->assertEquals(['file' => 'one.php'], $trace[0]);
        $this->assertEquals(['file' => 'three.php'], $trace[2]);
        $this->assertTrue(isset($trace[1]));
        $this->assertFalse(isset($trace[10]));
        $this->setExpectedException('OutOfRangeException');
        return $trace[10];
    }

    /**
     * @covers ::toString
     */
    public function testToString()
    {
        $items = [
            ['function' => 'preg_replace_callback'],
            ['file' => 'index.php', 'line' => 5, 'function' => 'func', 'args' => [1]],
        ];
        $expected = '#0 [internal function]: preg_replace_callback()'.\PHP_EOL.
            '#1 index.php(5): func(1)'.\PHP_EOL.
            '#2 {main}'.\PHP_EOL;
        $trace = new Trace($items);
        $this->assertSame($expected, ''.$trace);
    }
}
