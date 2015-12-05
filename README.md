# axy\backtrace: tracing in PHP

[![Latest Stable Version](https://img.shields.io/packagist/v/axy/backtrace.svg?style=flat-square)](https://packagist.org/packages/axy/backtrace)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%205.4-8892BF.svg?style=flat-square)](https://php.net/)
[![Build Status](https://img.shields.io/travis/axypro/backtrace/master.svg?style=flat-square)](https://travis-ci.org/axypro/backtrace)
[![Coverage Status](https://coveralls.io/repos/axypro/backtrace/badge.svg?branch=master&service=github)](https://coveralls.io/github/axypro/backtrace?branch=master)

Backtrace helper library.

 * GitHub: [axypro/backtrace](https://github.com/axypro/backtrace)
 * Composer: [axy/backtrace](https://packagist.org/packages/axy/backtrace)

Tested on PHP 5.4+, PHP 7, HHVM (on Linux).

The library does not require any dependencies.

It contains some tools to simplify the work with the call stack.

The library is intended primarily for debug.
For example, it used in [axypro/errors](https://github.com/axypro/errors) for cut uninformative part of the stack
(when displaying an exception).

## The library classes

 * [Trace](doc/Trace.md): the call stack.
 * [ExceptionTrace](doc/ExceptionTrace.md): the point of an exception.
