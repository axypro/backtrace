<?php
/**
 * @package axy\backtrace
 */

namespace axy\backtrace;

/**
 * The class of a calling trace
 *
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 *
 * @property-read array $items
 *                the current state of the backtrace
 * @property-read array $originalItems
 *                the original state of the backtrace
 */
class Trace implements \Countable, \IteratorAggregate, \ArrayAccess
{
    /**
     * The filter result: do not truncate
     *
     * @var mixed
     */
    const FILTER_SKIP = false;

    /**
     * The filter result: truncate, but leave this item
     *
     * @var int
     */
    const FILTER_LEAVE = 1;

    /**
     * The filter result: truncate together with this item
     *
     * @var int
     */
    const FILTER_LEFT = 2;

    /**
     * Constructor
     *
     * @param mixed $items [optional]
     *        a trace array or NULL (a current trace)
     */
    public function __construct(array $items = null)
    {
        if ($items === null) {
            $items = \debug_backtrace();
            \array_shift($items);
        }
        $this->props = [
            'items' => $items,
            'originalItems' => $items,
        ];
    }

    /**
     * Normalizes trace items
     */
    final public function normalize()
    {
        foreach ($this->props['items'] as &$item) {
            $item = \array_replace($this->defaultItem, $item);
        }
        unset($item);
    }

    /**
     * Truncates the trace by a limit
     *
     * @param int $limit
     * @return bool
     */
    final public function truncateByLimit($limit)
    {
        if (\count($this->props['items']) <= $limit) {
            return false;
        }
        $this->props['items'] = \array_slice($this->props['items'], 0, $limit);
        return true;
    }

    /**
     * Trim a file name by a dirname
     *
     * @param string $prefix
     * @return boolean
     */
    public function trimFilename($prefix)
    {
        $affected = false;
        $len = \strlen($prefix);
        foreach ($this->props['items'] as &$item) {
            if ((!empty($item['file'])) && (\strpos($item['file'], $prefix) === 0)) {
                $item['file'] = \substr($item['file'], $len);
                $affected = true;
            }
        }
        unset($item);
        return $affected;
    }

    /**
     * Truncate the trace by a options
     *
     * @param array $options
     *        the options (see $defaultOptions for list)
     * @return boolean
     *         point of truncate was found
     */
    public function truncate(array $options)
    {
        $options = \array_replace($this->defaultOptions, $options);
        $nItems = [];
        foreach (\array_reverse($this->props['items']) as $item) {
            $f = $this->filterItem($item, $options);
            if ($f) {
                if ($f !== self::FILTER_LEFT) {
                    $nItems[] = $item;
                }
                $this->props['items'] = \array_reverse($nItems);
                return true;
            }
            $nItems[] = $item;
        }
        return false;
    }

    /**
     * Truncate the trace by a filter
     *
     * @param callable $filter
     * @return boolean
     */
    final public function truncateByFilter($filter)
    {
        return $this->truncate(['filter' => $filter]);
    }

    /**
     * Truncate the trace by a namespace
     *
     * @param string $namespace
     * @return boolean
     */
    final public function truncateByNamespace($namespace)
    {
        return $this->truncate(['namespace' => $namespace]);
    }

    /**
     * Truncate the trace by a class
     *
     * @param string $class
     * @return boolean
     */
    final public function truncateByClass($class)
    {
        return $this->truncate(['class' => $class]);
    }

    /**
     * Truncate the trace by a file
     *
     * @param string $file
     * @return boolean
     */
    final public function truncateByFile($file)
    {
        return $this->truncate(['file' => $file]);
    }

    /**
     * Truncate the trace by a dir
     *
     * @param string $dir
     * @return boolean
     */
    final public function truncateByDir($dir)
    {
        return $this->truncate(['dir' => $dir]);
    }

    /**
     * Magic get
     *
     * @param string $key
     * @return mixed
     * @throw \LogicException
     *        a key is not found in the Trace
     */
    final public function __get($key)
    {
        if (!\array_key_exists($key, $this->props)) {
            throw new \LogicException('A field "'.$key.'" is not found in a Trace');
        }
        return $this->props[$key];
    }

    /**
     * Magic isset
     *
     * @param string $key
     * @return boolean
     */
    final public function __isset($key)
    {
        return \array_key_exists($key, $this->props);
    }

    /**
     * Magic set (forbidden)
     *
     * @param string $key
     * @param mixed $value
     * @throws \LogicException
     */
    final public function __set($key, $value)
    {
        throw new \LogicException('Trace is read-only');
    }

    /**
     * Magic unset (forbidden)
     *
     * @param string $key
     * @throws \LogicException
     */
    final public function __unset($key)
    {
        throw new \LogicException('Trace is read-only');
    }

    /**
     * {@inheritdoc}
     */
    final public function count()
    {
        return \count($this->props['items']);
    }

    /**
     * {@inheritdoc}
     */
    final public function getIterator()
    {
        return new \ArrayIterator($this->props['items']);
    }

    /**
     * {@inheritdoc}
     */
    final public function offsetExists($offset)
    {
        return isset($this->props['items'][$offset]);
    }

    /**
     * {@inheritdoc}
     */
    final public function offsetGet($offset)
    {
        if (!isset($this->props['items'][$offset])) {
            throw new \OutOfRangeException('Trace['.$offset.'] is not found');
        }
        return $this->props['items'][$offset];
    }

    /**
     * {@inheritdoc}
     * Forbidden
     * @throws \LogicException
     */
    final public function offsetSet($offset, $value)
    {
        throw new \LogicException('Trace is read-only');
    }

    /**
     * {@inheritdoc}
     * Forbidden
     * @throws \LogicException
     */
    final public function offsetUnset($offset)
    {
        throw new \LogicException('Trace is read-only');
    }

    /**
     * {@inheritdoc}
     */
    final public function __toString()
    {
        return helpers\Repr::trace($this->props['items']);
    }

    /**
     * Check a backtrace item for truncate
     *
     * @param array $item
     *        an item of the backtrace
     * @param array $options
     * @return mixed
     *         result as FILTER_* constant
     */
    protected function filterItem(array $item, array $options)
    {
        if ($options['filter']) {
            $f = \call_user_func($options['filter'], $item);
            if ($f) {
                return $f;
            }
        }
        if (!empty($item['class'])) {
            if ($options['namespace']) {
                if (\strpos($item['class'], $options['namespace'].'\\') === 0) {
                    return self::FILTER_LEAVE;
                }
            }
            if ($options['class']) {
                if ($item['class'] === $options['class']) {
                    return self::FILTER_LEAVE;
                }
            }
        }
        if (!empty($item['file'])) {
            if ($options['dir']) {
                if (\strpos($item['file'], $options['dir']) === 0) {
                    return self::FILTER_LEFT;
                }
            }
            if ($options['file']) {
                if ($item['file'] === $options['file']) {
                    return self::FILTER_LEFT;
                }
            }
        }
        return self::FILTER_SKIP;
    }

    /**
     * The list of all fields of a trace item (with default values)
     *
     * @var array
     */
    protected $defaultItem = [
        'function' => null,
        'line' => null,
        'file' => null,
        'class' => null,
        'object' => null,
        'type' => null,
        'args' => [],
    ];

    /**
     * The list of default options for truncate()
     *
     * @var array
     */
    protected $defaultOptions = [
        'filter' => null,
        'namespace' => null,
        'class' => null,
        'file' => null,
        'dir' => null,
    ];

    /**
     * The current state of the backtrace
     *
     * @var array
     */
    protected $items;

    /**
     * The list of magic properties
     *
     * @var array
     */
    protected $props = [];
}
