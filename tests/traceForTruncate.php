<?php
/**
 * @package axy\backtrace
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

declare(strict_types=1);

namespace axy\backtrace\tests;

return [
    [
        'function' => 'placeholderClb',
        'class' => 'go\DB\Helpers\Templater',
    ],
    [
        'file' => '/test/go/DB/Helpers/Templater.php',
        'line' => 57,
        'function' => 'preg_replace_callback',
    ],
    [
        'file' => '/test/go/DB/DB.php',
        'line' => 312,
        'function' => 'parse',
        'class' => 'go\DB\Helpers\Templater',
    ],
    [
        'file' => '/test/go/DB/Adapters/Sqlite.php',
        'line' => 30,
        'function' => 'makeQuery',
        'class' => 'go\DB\DB',
    ],
    [
        'file' => '/test/go/DB/DB.php',
        'line' => 92,
        'function' => 'makeQuery',
        'class' => 'go\DB\Adapters\Sqlite',
    ],
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
