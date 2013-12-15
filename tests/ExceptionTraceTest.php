<?php
/**
 * @package axy\backtrace
 */

namespace axy\backtrace\tests;

use axy\backtrace\ExceptionTrace;

/**
 * @coversDefaultClass axy\backtrace\ExceptionTrace
 */
class ExceptionTraceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @covers ::__get
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
     * @covers ::__construct
     * @covers ::__get
     */
    public function testConstructCurrentFile()
    {
        $line = __LINE__ + 1;
        $trace = new ExceptionTrace();
        $this->assertSame(__FILE__, $trace->file);
        $this->assertSame($line, $trace->line);
    }

    /**
     * @covers ::__isset
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
     * @covers ::trimFilename
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
}
