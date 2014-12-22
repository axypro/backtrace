# `truncate`-methods

Methods whose names begin with the prefix `truncate` cut stack at some point.
Usually, this is the entry point into a certain namespace.
This can be useful when [dealing with the exceptions](ExceptionTrace.md).

## Example

For example, we use third-party library to work with the database.
Somewhere inside of our code, we call to this library.

```php
$db->query('SELECT * FROM `test` WHERE `id`=?i')->el();
```

Here is the error.
Do not specify a value for the placeholder.

`query()` method calls to other library method.
That calls the next and so on.

At some point, an error occurs.
And you will see the following in the console:

```
DataNotEnough: Data elements (0) less than the placeholders in /test/my/db/Helpers/Template.php on line 126
 
Call Stack:
    1. {main}() /test/my/index.php:0
    2. callMyClass() /test/my/index.php:14
    3. MyClass->method() /test/my/index.php:11
    4. MyClass->selectDB() /test/my/MyClass.php:7
    5. go\DB\DB->query() /test/my/MyClass.php:20
    6. go\DB\DB->makeQuery() /test/my/db/DB.php:92
    7. go\DB\Helpers\Template->parse() /test/my/db/DB.php:312
    8. preg_replace_callback() /test/my/db/Helpers/Template.php:57
    9. go\DB\Helpers\Template->placeholderClb() /test/my/db/Helpers/Template.php:57
```

We can see that the error occurred in the file Template.php on the line 126.
But we do not give anything.
We need to know where we made a mistake.
It happened in file /test/myClass.php (line 20).
We have to read the error output and determine where ended our code and library code was started.

It would be convenient if the library in its exceptions would cut its calls.
Then we could get such a message:

```
DataNotEnough: Data elements (0) less than the placeholders in /test/my/MyClass.php on line 20
```

Immediately clear where the error.

### `truncate(array $options):boolean`

This method truncates the stack, as specified in the options.
The options is array with follow optional fields:

 * `namespace`
 * `class`
 * `dir`
 * `file`
 * `filter`

For each parameter there is single method.
For example `$trace->truncateByNamespace($ns)` is such `$trace->truncate(['namespace' => $ns])`.

The values of these parameters are described in the respective methods.

All methods returns a boolean.
TRUE if stack have cut.

### `truncateByNamespace(string $namespace):boolean`

Truncates the stack at the entry point in the namespace.

In the above example it was necessary to use:

```php
$trace->truncateByNamespace('go\DB');
echo $trace;
```

The result:
```
Call Stack:
1. {main}() /test/index.php:0
2. myfunc() /test/index.php:10
3. MyClass->sendQuery() /test/func/index.php:50
4. go\DB\DB->query() /test/MyClass.php:25
```

The entry point: /test/MyClass.php:25

### `truncateByClass(string $classname):boolean`

Searches for the first call to the class.

### `truncateByFile(string $filename):boolean`

Truncates the stack in the time of reference to the code defined in a given file.

### `truncateByDir(string $dirname):boolean`

Similarly, when referring to any file in the directory.
The `dirname` used as a prefix (specify a trailing slash).

### `truncateByFilter(callable $filter):boolean`

Launches user-defined function for each element of the stack, beginning with the earliest call.

```php
$trace->truncateByFilter(function (array $call) {
    if ((isset($call['function'])) && ($call['function'] === 'query')) {
        return Trace::FILTER_LEFT;
    }
    return false;
});
```

When the element is found, from which the stack should be cut, the function should return one of the following values:

 * `Trace::FILTER_LEFT` - truncate the stack including this element
 * `Trace::FILTER_LEAVE` - leave the current element, and cut next
