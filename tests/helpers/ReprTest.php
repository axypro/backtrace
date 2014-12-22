<?php
/**
 * @package axy\backtrace
 */

namespace axy\backtrace\tests\helpers;

use axy\backtrace\helpers\Repr;

/**
 * coversDefaultClass axy\backtrace\helpers\Repr
 */
class ReprTest extends \PHPUnit_Framework_TestCase
{
    /**
     * covers ::arg
     * @dataProvider providerArg
     * @param mixed $value
     * @param string $expected
     */
    public function testArg($value, $expected)
    {
        $this->assertSame($expected, Repr::arg($value));
    }

    /**
     * @return array
     */
    public function providerArg()
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
            [$this, 'Object(axy\backtrace\tests\helpers\ReprTest)'],
        ];
    }

    /**
     * covers ::method
     * @dataProvider providerMethod
     * @param array $item
     * @param string $expected
     */
    public function testMethod(array $item, $expected)
    {
        $this->assertSame($expected, Repr::method($item));
    }

    /**
     * @return array
     */
    public function providerMethod()
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
    public function testPoint(array $item, $expected)
    {
        $this->assertSame($expected, Repr::point($item));
    }

    /**
     * @return array
     */
    public function providerPoint()
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
    public function testItem(array $item, $number, $expected)
    {
        $this->assertSame($expected, Repr::item($item, $number));
    }

    /**
     * @return array
     */
    public function providerItem()
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
    public function testTrace(array $items, array $expectedLines)
    {
        $expected = implode('-', $expectedLines).'-';
        $this->assertSame($expected, Repr::trace($items, '-'));
    }

    /**
     * @return array
     */
    public function providerTrace()
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
