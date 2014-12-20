<?php
/**
 * @package axy\backtrace
 */

namespace axy\backtrace\tests;

use axy\backtrace\ExceptionTrace;
use axy\backtrace\tests\hlp\GetExceptionTrace;

/**
 * coversDefaultClass axy\backtrace\ExceptionTrace
 */
class ExceptionTraceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * covers ::__construct
     * covers ::__get
     */
    public function testConstruct()
    {
        $items = [
            ['file' => 'index.php', 'line' => 10,],
            ['file' => 'index.php', 'line' => 1,],
        ];
        $trace = new ExceptionTrace($items, 'file.php', 25);
        $this->assertEquals($items, $trace->items);
        $this->assertSame('file.php', $trace->file);
        $this->assertSame(25, $trace->line);
        $this->assertEquals($items, $trace->originalItems);
        $this->assertSame('file.php', $trace->originalFile);
        $this->assertSame(25, $trace->originalLine);
    }

    /**
     * covers ::__construct
     * covers ::__get
     */
    public function testConstructNull()
    {
        $t = new GetExceptionTrace();
        $trace = $t->trace;
        $this->assertCount(\count($t->native), $trace->items);
        $this->assertEquals($t->native[0], $trace->items[0]);
        $this->assertSame($t->file, $trace->file);
        $this->assertSame($t->line, $trace->line);
    }

    /**
     * covers ::__construct
     * covers ::__get
     */
    public function testConstructFileNull()
    {
        $items = [
            ['file' => 'file.php', 'line' => 10,],
            ['file' => 'index.php', 'line' => 1,],
        ];
        $trace = new ExceptionTrace($items);
        $this->assertEquals($items, $trace->items);
        $this->assertSame('file.php', $trace->file);
        $this->assertSame(10, $trace->line);
    }

    /**
     * covers ::__isset
     */
    public function testMagicIsset()
    {
        $trace = new ExceptionTrace([]);
        $this->assertTrue(isset($trace->items));
        $this->assertTrue(isset($trace->file));
        $this->assertTrue(isset($trace->originalLine));
        $this->assertFalse(isset($trace->unknown));
    }

    /**
     * covers ::trimFilename
     */
    public function testTrimFilename()
    {
        $items = [
            ['file' => '/var/www/file.php', 'line' => 10,],
            ['file' => '/var/share/index.php', 'line' => 30,],
        ];
        $expected = [
            ['file' => 'file.php', 'line' => 10,],
            ['file' => '/var/share/index.php', 'line' => 30,],
        ];
        $trace = new ExceptionTrace($items, '/var/www/exc/e.php', 28);
        $this->assertTrue($trace->trimFilename('/var/www/'));
        $this->assertEquals($expected, $trace->items);
        $this->assertSame('exc/e.php', $trace->file);
        $this->assertTrue($trace->trimFilename('exc/'));
        $this->assertEquals($expected, $trace->items);
        $this->assertSame('e.php', $trace->file);
        $this->assertFalse($trace->trimFilename('exc/'));
    }

    /**
     * covers ::truncate
     * covers ::truncateByNamespace
     */
    public function testTruncateByNamespaceTrunc()
    {
        $items = [
            ['file' => 'my/ns/Class.php', 'line' => 30, 'class' => 'my\ns\Second'],
            ['file' => 'func.php', 'line' => 20, 'class' => 'my\ns\Class'],
            ['file' => 'index.php', 'line' => 10, 'function' => 'func',],
        ];
        $trace = new ExceptionTrace($items, 'my/ns/Second.php', 12);
        $this->assertTrue($trace->truncateByNamespace('my\ns'));
        $expected = [
            ['file' => 'func.php', 'line' => 20, 'class' => 'my\ns\Class'],
            ['file' => 'index.php', 'line' => 10, 'function' => 'func',],
        ];
        $this->assertEquals($expected, $trace->items);
        $this->assertSame('func.php', $trace->file);
        $this->assertSame(20, $trace->line);
        $this->assertEquals($items, $trace->originalItems);
        $this->assertSame('my/ns/Second.php', $trace->originalFile);
        $this->assertSame(12, $trace->originalLine);
    }

    /**
     * covers ::truncate
     * covers ::truncateByNamespace
     */
    public function testTruncateByNamespaceTop()
    {
        $items = [
            ['file' => 'func.php', 'line' => 20, 'class' => 'my\ns\Class'],
            ['file' => 'index.php', 'line' => 10, 'function' => 'func',],
        ];
        $trace = new ExceptionTrace($items, 'my/ns/Class.php', 12);
        $this->assertTrue($trace->truncateByNamespace('my\ns'));
        $this->assertEquals($items, $trace->items);
        $this->assertSame('func.php', $trace->file);
        $this->assertSame(20, $trace->line);
    }

    /**
     * covers ::truncate
     * covers ::truncateByNamespace
     */
    public function testTruncateByNamespaceSkip()
    {
        $items = [
            ['file' => 'func.php', 'line' => 20, 'class' => 'my\ns\Class'],
            ['file' => 'index.php', 'line' => 10, 'function' => 'func',],
        ];
        $trace = new ExceptionTrace($items, 'my/ns/Class.php', 12);
        $this->assertFalse($trace->truncateByNamespace('my\otherNS'));
        $this->assertEquals($items, $trace->items);
        $this->assertSame('my/ns/Class.php', $trace->file);
        $this->assertSame(12, $trace->line);
    }

    /**
     * covers ::truncate
     * covers ::truncateByDir
     */
    public function testTruncateByDirTop()
    {
        $items = [
            ['file' => 'func.php', 'line' => 20, 'class' => 'my\ns\Class'],
            ['file' => 'index.php', 'line' => 10, 'function' => 'func',],
        ];
        $trace = new ExceptionTrace($items, 'my/ns/Class.php', 12);
        $this->assertTrue($trace->truncateByDir('my/ns/'));
        $this->assertEquals($items, $trace->items);
        $this->assertSame('func.php', $trace->file);
        $this->assertSame(20, $trace->line);
        $this->assertFalse($trace->truncateByDir('my/ns/'));
    }

    /**
     * covers ::truncate
     * covers ::truncateByDir
     */
    public function testTruncateByDirSkip()
    {
        $items = [
            ['file' => 'func.php', 'line' => 20, 'class' => 'my\ns\Class'],
            ['file' => 'index.php', 'line' => 10, 'function' => 'func',],
        ];
        $trace = new ExceptionTrace($items, 'my/ns/Class.php', 12);
        $this->assertFalse($trace->truncateByDir('my/otherNS/'));
        $this->assertEquals($items, $trace->items);
        $this->assertSame('my/ns/Class.php', $trace->file);
        $this->assertSame(12, $trace->line);
    }
}
