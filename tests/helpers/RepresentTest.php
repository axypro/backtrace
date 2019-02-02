<?php
/**
 * @package axy\backtrace
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\backtrace\tests\helpers;

use PHPUnit\Framework\TestCase;
use axy\backtrace\helpers\Represent;

/**
 * coversDefaultClass axy\backtrace\helpers\Represent
 */
class RepresentTest extends TestCase
{
    /**
     * covers ::arg
     * @dataProvider providerArg
     * @param mixed $value
     * @param string $expected
     */
    public function testArg($value, string $expected): void
    {
        $this->assertSame($expected, Represent::arg($value));
    }

    /**
     * @return array
     */
    public function providerArg(): array
    {
        return [
            [null, 'NULL',],
            [true, 'true',],
            [false, 'false',],
            [-10, '-10'],
            ['this is string', "'this is string'"],
            ['this is very long string', "'this is very lo...'"],
            ['quot not \'escape\'', "'quot not 'escap...'"],
            ['строка в utf-8', "'строка в utf-8'"],
            ['длинная строка в utf-8', "'длинная строка ...'"],
            [[1, 2, 3], 'Array'],
            [(object)[1, 2, 3], 'Object(stdClass)'],
            [$this, 'Object(axy\backtrace\tests\helpers\RepresentTest)'],
        ];
    }

    /**
     * covers ::method
     * @dataProvider providerMethod
     * @param array $item
     * @param string $expected
     */
    public function testMethod(array $item, string $expected): void
    {
        $this->assertSame($expected, Represent::method($item));
    }

    /**
     * @return array
     */
    public function providerMethod(): array
    {
        return [
            [
                [
                    'function' => 'func'
                ],
                'func()',
            ],
            [
                [
                    'class' => 'MyClass',
                    'function' => 'method',
                    'type' => '::',
                    'args' => [1, 2],
                ],
                'MyClass::method(1, 2)',
            ],
            [
                [
                    'class' => 'my\ns\MyClass',
                    'function' => 'method',
                    'type' => '->',
                    'args' => ['this is very long string'],
                ],
                'my\ns\MyClass->method(\'this is very lo...\')',
            ],
            [
                [
                    'class' => 'x',
                ],
                '',
            ]
        ];
    }

    /**
     * covers ::point
     * @dataProvider providerPoint
     * @param array $item
     * @param string $expected
     */
    public function testPoint(array $item, string $expected): void
    {
        $this->assertSame($expected, Represent::point($item));
    }

    /**
     * @return array
     */
    public function providerPoint(): array
    {
        return [
            [
                [
                ],
                '[internal function]',
            ],
            [
                [
                    'file' => '/test/file.php',
                    'line' => 10,
                ],
                '/test/file.php(10)',
            ],
            [
                [
                    'file' => '/test/file.php',
                ],
                '/test/file.php',
            ],
        ];
    }

    /**
     * covers ::item
     * @dataProvider providerItem
     * @param array $item
     * @param int $number
     * @param string $expected
     */
    public function testItem(array $item, int $number, string $expected): void
    {
        $this->assertSame($expected, Represent::item($item, $number));
    }

    /**
     * @return array
     */
    public function providerItem(): array
    {
        return [
            [
                [
                    'file' => '/test/file.php',
                    'line' => 20,
                    'class' => 'MyClass',
                    'type' => '->',
                    'function' => 'plus',
                    'args' => [2, 2,],
                ],
                10,
                '#10 /test/file.php(20): MyClass->plus(2, 2)',
            ]
        ];
    }

    /**
     * covers ::trace
     * @dataProvider providerTrace
     * @param array $items
     * @param array $expectedLines
     */
    public function testTrace(array $items, array $expectedLines): void
    {
        $expected = implode('-', $expectedLines).'-';
        $actual = Represent::trace($items, '-');
        /* In HHVM Obhect(Closure) has extended representation */
        $actual = preg_replace('/Object\(Closure[^)]*\)/', 'Object(Closure)', $actual);
        $this->assertSame($expected, $actual);
    }

    /**
     * @return array
     */
    public function providerTrace(): array
    {
        return [
            [
                [
                    [
                        'function' => 'my\ns\{closure}',
                        'class' => 'my\ns\A',
                        'type' => '::',
                        'args' => [
                            ['a'],
                        ]
                    ],
                    [
                        'file' => '/test/e.php',
                        'line' => 17,
                        'function' => 'preg_replace_callback',
                        'args' => [
                            '~a~',
                            function ($m) {
                                return $m[0];
                            },
                            'str',
                        ],
                    ],
                    [
                        'file' => '/test/e.php',
                        'line' => 8,
                        'function' => 'method',
                        'class' => 'my\ns\A',
                        'type' => '::',
                        'args' => [3],
                    ],
                    [
                        'file' => '/test/e.php',
                        'line' => 23,
                        'function' => 'my\ns\f',
                        'args' => ['str'],
                    ]
                ],
                [
                    '#0 [internal function]: my\ns\A::my\ns\{closure}(Array)',
                    '#1 /test/e.php(17): preg_replace_callback(\'~a~\', Object(Closure), \'str\')',
                    '#2 /test/e.php(8): my\ns\A::method(3)',
                    '#3 /test/e.php(23): my\ns\f(\'str\')',
                    '#4 {main}',
                ],
            ]
        ];
    }
}
