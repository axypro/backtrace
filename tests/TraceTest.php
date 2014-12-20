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
        $current = \debug_backtrace();
        $this->assertCount(\count($current), $trace->items);
        $this->assertEquals($current[0], $trace->items[0]);
        $this->assertEquals($trace->items, $trace->originalItems);
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
     * @covers ::trimFilename
     */
    public function testTrimFilename()
    {
        $items = [
            ['file' => '/var/www/file.php', 'line' => 10,],
            ['function' => 'eval',],
            ['file' => '/var/www/folder/f.php', 'line' => 20,],
            ['file' => '/var/share/index.php', 'line' => 30,],
        ];
        $expected = [
            ['file' => 'file.php', 'line' => 10,],
            ['function' => 'eval',],
            ['file' => 'folder/f.php', 'line' => 20,],
            ['file' => '/var/share/index.php', 'line' => 30,],
        ];
        $trace = new Trace($items);
        $this->assertTrue($trace->trimFilename('/var/www/'));
        $this->assertEquals($expected, $trace->items);
        $this->assertFalse($trace->trimFilename('/var/www/'));
        $this->assertEquals($expected, $trace->items);
        $this->assertEquals($items, $trace->originalItems);
    }

    /**
     * @covers ::truncate
     * @covers ::truncateByFilter
     */
    public function testTruncateByFilterLeft()
    {
        $trace = $this->getTraceForTruncate();
        $filter = function (array $item) {
            if ($item['function'] === 'get') {
                return Trace::FILTER_LEFT;
            }
            return Trace::FILTER_SKIP;
        };
        $expected = [
            [
                'file' => '/test/index.php',
                'line' => 15,
                'function' => 'func',
            ],
        ];
        $this->assertTrue($trace->truncateByFilter($filter));
        $this->assertEquals($expected, $trace->items);
        $this->assertFalse($trace->truncateByFilter($filter));
        $this->assertEquals($expected, $trace->items);
        $this->assertEquals($this->itemsForTruncate, $trace->originalItems);
    }

    /**
     * @covers ::truncate
     * @covers ::truncateByFilter
     */
    public function testTruncateByFilterLeave()
    {
        $trace = $this->getTraceForTruncate();
        $filter = function (array $item) {
            if ($item['function'] === 'get') {
                return Trace::FILTER_LEAVE;
            }
            return Trace::FILTER_SKIP;
        };
        $expected = [
            [
                'file' => '/test/index.php',
                'line' => 20,
                'function' => 'get',
                'class' => 'test\TestClass',
            ],
            [
                'file' => '/test/index.php',
                'line' => 15,
                'function' => 'func',
            ],
        ];
        $this->assertTrue($trace->truncateByFilter($filter));
        $this->assertEquals($expected, $trace->items);
        $this->assertTrue($trace->truncateByFilter($filter));
        $this->assertEquals($expected, $trace->items);
    }

    /**
     * @covers ::truncate
     * @covers ::truncateByFilter
     */
    public function testTruncateByFilterLeaveTop()
    {
        $trace = $this->getTraceForTruncate();
        $filter = function (array $item) {
            if ($item['function'] === 'placeholderClb') {
                return Trace::FILTER_LEAVE;
            }
            return Trace::FILTER_SKIP;
        };
        $this->assertTrue($trace->truncateByFilter($filter));
        $this->assertEquals($this->itemsForTruncate, $trace->items);
    }

    /**
     * @covers ::truncate
     * @covers ::truncateByFilter
     */
    public function testTruncateByFilterSkip()
    {
        $trace = $this->getTraceForTruncate();
        $filter = function () {
            return Trace::FILTER_SKIP;
        };
        $this->assertFalse($trace->truncateByFilter($filter));
        $this->assertEquals($this->itemsForTruncate, $trace->items);
    }

    /**
     * @covers ::truncate
     * @covers ::truncateByNamespace
     */
    public function testTruncateByNamespace()
    {
        $trace = $this->getTraceForTruncate();
        $this->assertFalse($trace->truncateByNamespace('go\Unk'));
        $this->assertTrue($trace->truncateByNamespace('go\DB'));
        $this->assertEquals($this->itemsForTruncate, $trace->originalItems);
        $expected = [
            [
                'file' => '/test/TestClass.php',
                'line' => 15,
                'function' => 'query',
                'class' => 'go\DB\DB',
            ],
            [
                'file' => '/test/TestClass.php',
                'line' => 10,
                'function' => 'calc',
                'class' => 'test\TestClass',
            ],
            [
                'file' => '/test/index.php',
                'line' => 20,
                'function' => 'get',
                'class' => 'test\TestClass',
            ],
            [
                'file' => '/test/index.php',
                'line' => 15,
                'function' => 'func',
            ],
        ];
        $this->assertEquals($expected, $trace->items);
        $this->assertTrue($trace->truncateByNamespace('go\DB'));
        $this->assertEquals($expected, $trace->items);
    }

    /**
     * @covers ::truncate
     * @covers ::truncateByClass
     */
    public function testTruncateByClass()
    {
        $trace = $this->getTraceForTruncate();
        $this->assertTrue($trace->truncateByClass('go\DB\DB'));
        $expected = [
            [
                'file' => '/test/TestClass.php',
                'line' => 15,
                'function' => 'query',
                'class' => 'go\DB\DB',
            ],
            [
                'file' => '/test/TestClass.php',
                'line' => 10,
                'function' => 'calc',
                'class' => 'test\TestClass',
            ],
            [
                'file' => '/test/index.php',
                'line' => 20,
                'function' => 'get',
                'class' => 'test\TestClass',
            ],
            [
                'file' => '/test/index.php',
                'line' => 15,
                'function' => 'func',
            ],
        ];
        $this->assertEquals($expected, $trace->items);
    }

    /**
     * @covert ::truncate
     * @covers ::truncateByFile
     */
    public function testTruncateByFile()
    {
        $trace = $this->getTraceForTruncate();
        $this->assertTrue($trace->truncateByFile('/test/TestClass.php'));
        $expected = [
            [
                'file' => '/test/index.php',
                'line' => 20,
                'function' => 'get',
                'class' => 'test\TestClass',
            ],
            [
                'file' => '/test/index.php',
                'line' => 15,
                'function' => 'func',
            ],
        ];
        $this->assertEquals($expected, $trace->items);
    }

    /**
     * @covers ::truncate
     * @covers ::truncateByDir
     */
    public function testTruncateByDir()
    {
        $trace = $this->getTraceForTruncate();
        $this->assertTrue($trace->truncateByDir('/test/go/DB'));
        $expected = [
            [
                'file' => '/test/TestClass.php',
                'line' => 15,
                'function' => 'query',
                'class' => 'go\DB\DB',
            ],
            [
                'file' => '/test/TestClass.php',
                'line' => 10,
                'function' => 'calc',
                'class' => 'test\TestClass',
            ],
            [
                'file' => '/test/index.php',
                'line' => 20,
                'function' => 'get',
                'class' => 'test\TestClass',
            ],
            [
                'file' => '/test/index.php',
                'line' => 15,
                'function' => 'func',
            ],
        ];
        $this->assertEquals($expected, $trace->items);
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
     * @covers ::__toString
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

    /**
     * @return \axy\backtrace\Trace
     */
    private function getTraceForTruncate()
    {
        if (!$this->itemsForTruncate) {
            $this->itemsForTruncate = include(__DIR__.'/traceForTruncate.php');
        }
        return new Trace($this->itemsForTruncate);
    }

    /**
     * @var array
     */
    private $itemsForTruncate;
}
