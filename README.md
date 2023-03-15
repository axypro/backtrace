# axy\backtrace

Backtrace helper library (PHP).

[![Latest Stable Version](https://img.shields.io/packagist/v/axy/backtrace.svg?style=flat-square)](https://packagist.org/packages/axy/pkg-tpl)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%208.0-8892BF.svg?style=flat-square)](https://php.net/)
[![Tests](https://github.com/axypro/backtrace/actions/workflows/test.yml/badge.svg)](https://github.com/axypro/pkg-tpl/actions/workflows/test.yml)
[![Coverage Status](https://coveralls.io/repos/github/axypro/backtrace/badge.svg?branch=master)](https://coveralls.io/github/axypro/pkg-tpl?branch=master)
[![License](https://poser.pugx.org/axy/pkg-tpl/license)](LICENSE)

### Documentation

This library contains some tools to simplify the work with the call stack.

The library is intended primarily for debug.
For example, it is used in [axypro/errors](https://github.com/axypro/errors) library for cut uninformative part of the stack.
(when displaying an exception).

#### The library classes

 * [Trace](doc/Trace.md): the call stack.
 * [ExceptionTrace](doc/ExceptionTrace.md): the point of an exception.
