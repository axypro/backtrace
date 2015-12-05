# `ExceptionTrace`: point of exception

The class `axy\backtrace\ExceptionTrace` extends the class [Trace](Trace.md).

An instance of this class, in addition to the stack, also contains a reference to the line where the exception occurred.

### `__construct([array $items [, string $file, int $line])`

 * `$items` - see [Trace](Trace.md)
 * `$file` and `$line` - the place where the exception occured.

This is the data that can be received using `$e->getTrace()`, `$e->getFile()` and `$e->getLine()`.

If no arguments are given, a point of the instance creation will be taken.

```php
/* File /test/index.php */
use axy\backtrace\ExceptionTrace;

function one()
{
    return two();
}

function two()
{
    return new ExceptionTrace(); // line 15
}

$trace = one();

echo 'File: '.$trace->file.\PHP_EOL;
echo 'Line: '.$trace->line.\PHP_EOL;
echo $trace;
```

The output:
```php
File: /test/index.php
Line: 15
#0 /test/index.php(10): two()
#1 /test/index.php(18): one()
#2 {main}
```

If you specify `$items`, but do not specify `$file` and `$line`, they are taken from the current call.

### Properties (read-only)

Inherited from [Trace](Trace.md):

 * `$items`
 * `$originalItems`

New:

 * `$file`
 * `$line`
 * `$originalFile`
 * `$originalLine`

`$original*` can not be modified.
The others can be modified by methods.

### `truncate*`

See [Trace::truncate()](truncate.md).

In additional, `$file` and `$line` are changed to point to the entry point into the library.

### `trimFilename(string $dir)`

In additional, trims the `$file`.
