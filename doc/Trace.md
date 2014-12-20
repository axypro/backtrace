# `Trace`: the call stack

An instance of the `axy\backtrace\Trace` class correspond to a trace.

### `__construct([array $items])`

The constructor.
Format of the $items (similar to [debug_backtrace](http://php.net/debug_backtrace)):

 * Numeric array where each element correspond to one call
 * A first (0th) element is a last (the current) call. A last is a first call.
 * Optional fields of the element: `function`, `line`, `file`, `class`, `object`, `type`, `args`.

If you do not specify an argument then the current stack is taken:

```php
use axy\backtrace\Trace;

function x()
{
    return y();
}

function y()
{
    return new Trace();
}

$trace = x();
print_r($trace->items);
```

Result:

```php
[
    0 => [
        'file' => '/test/index.php',
        'line' => 9,
        'function' => 'y',
        'args' => [],
    ],
    1 => [
        'file' => '/test/index.php',
        'line' => 17,
        'function' => 'x',
        'args' => [],
    ],
]
```

### Properties `$items` and `$originalItems`

Initially, both these properties contain the received stack.
Class methods can modify `items`.
`originalItems` is not changes.
These properties is read-only.

```php
$trace = new Trace();
$trace->truncateByLimit(5);
$trace->trimFilename('/var/lib/');

print_r($trace->items); // modified array
print_r($trace->originalItems); // original array
```

### `normalize(void):void`

Element of the array which returns debug_backtrace contain not all the fields.
For example, if it was a call to the function (not a method), there will be no properties `class`, `type`, `object`.

When working with such an array is required to check for keys:
```php
if (isset($item['class'])) {
    echo 'class: '.$item['class'];
}
```

The method fills in all the missing keys by default.
```php
$trace->normalize();
foreach ($trace->items as $item) {
    echo 'class: '.($item['class'] ?: 'NONE');
}
```

### `truncateByLimit(int $limit):void`

Leaves only the specified number of recent calls.
Removes all earlier.

### `trimFilename(string $dir):void`

Usually, all the files are in a certain directory. 
The directory path can be quite long.
Long file names interfere visually understand them.
Common prefixes do not carry any information.
They can be cut.

Before:
```php
#0 /test/the/long/path/to/root/func.php(5): getTrace()
#1 /test/the/long/path/to/root/index.php(6): func()
#2 /test/the/long/path/to/root/index.php(9): f(1)
#3 {main}
```

After `$trace->trimFilename(__DIR__.'/')`:
```php
#0 func.php(5): getTrace()
#1 index.php(6): func()
#2 index.php(9): f(1)
#3 {main}
```

The `$dir` argument is treated as a prefix.
His best to finish by slash.

### `truncate`-methods

These methods cut the stack to the right place.
See [a separate article](truncate.md).

### Magic

The class implements the follow interfaces: `ArrayAccess`, `Countable`, `Traversable`.
You can work with an instance as a numeric array of calls (read-only).

```php
echo $trace[1]['line']; // Same as $trace->items[1]['line']
```

### `__toString(void):string`

```php
echo $trace;
```

The output similar an exceptions output to the console.
