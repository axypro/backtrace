<?php
/**
 * @package axy\backtrace
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\backtrace\tests;

use PHPUnit\Framework\TestCase;
use axy\backtrace\ExceptionTrace;
use axy\backtrace\tests\hlp\GetExceptionTrace;

/**
 * coversDefaultClass axy\backtrace\ExceptionTrace
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class ExceptionTraceTest extends TestCase
{
    /**
     * covers ::__construct
     * covers ::__get
     */
    public function testConstruct(): void
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
    public function testConstructNull(): void
    {
        $t = new GetExceptionTrace();
        $trace = $t->trace;
        $this->assertCount(count($t->native), $trace->items);
        $this->assertEquals($t->native[0], $trace->items[0]);
        $this->assertSame($t->file, $trace->file);
        $this->assertSame($t->line, $trace->line);
    }

    /**
     * covers ::__construct
     * covers ::__get
     */
    public function testConstructFileNull(): void
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
    public function testMagicIsset(): void
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
    public function testTrimFilename(): void
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
    public function testTruncateByNamespace(): void
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
    public function testTruncateByNamespaceTop(): void
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
    public function testTruncateByNamespaceSkip(): void
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
    public function testTruncateByDirTop(): void
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
    public function testTruncateByDirSkip(): void
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

    /**
     * covers ::truncate
     * covers ::truncateByFile
     */
    public function testTruncateByFile(): void
    {
        $items = [
            ['file' => 'func.php', 'line' => 20, 'class' => 'my\ns\Class'],
            ['file' => 'index.php', 'line' => 10, 'function' => 'func',],
        ];
        $trace = new ExceptionTrace($items, 'my/ns/Class.php', 12);
        $this->assertTrue($trace->truncateByFile('func.php'));
        $this->assertEquals([$items[1]], $trace->items);
        $this->assertSame('index.php', $trace->file);
        $this->assertSame(10, $trace->line);
    }

    /**
     * covers ::truncate
     * covers ::truncateByFile
     */
    public function testTruncateByFileTop(): void
    {
        $items = [
            ['file' => 'func.php', 'line' => 20, 'class' => 'my\ns\Class'],
            ['file' => 'index.php', 'line' => 10, 'function' => 'func',],
        ];
        $trace = new ExceptionTrace($items, 'my/ns/Class.php', 12);
        $this->assertTrue($trace->truncateByFile('my/ns/Class.php'));
        $this->assertEquals($items, $trace->items);
        $this->assertSame('func.php', $trace->file);
        $this->assertSame(20, $trace->line);
    }

    /**
     * covers ::truncate
     * covers ::truncateByFile
     */
    public function testTruncateByFileSkip(): void
    {
        $items = [
            ['file' => 'func.php', 'line' => 20, 'class' => 'my\ns\Class'],
            ['file' => 'index.php', 'line' => 10, 'function' => 'func',],
        ];
        $trace = new ExceptionTrace($items, 'my/ns/Class.php', 12);
        $this->assertFalse($trace->truncateByFile('unk.php'));
        $this->assertEquals($items, $trace->items);
        $this->assertSame('my/ns/Class.php', $trace->file);
        $this->assertSame(12, $trace->line);
    }
}
