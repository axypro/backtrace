<?php
/**
 * @package axy\backtrace
 */

namespace axy\backtrace;

/**
 * The class of a calling trace
 *
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 * @property-read array $items
 * @property-read array $originalItems
 */
class Trace implements \Countable, \IteratorAggregate, \ArrayAccess
{

    const FILTER_SKIP = false;
    const FILTER_LEAVE = 1;
    const FILTER_LEFT = 2;

    /**
     * Constructor
     *
     * @param mixed $items [optional]
     *        a trace array, a trace instance or NULL (a current trace)
     */
    public function __construct($items = null)
    {
        if ($items === null) {
            $items = \debug_backtrace();
            \array_shift($items);
        }
        if (\is_array($items)) {
            $this->items = $items;
            $this->originalItems = $items;
        } elseif ($items instanceof self) {
            $this->cloneProperties($items);
        } else {
            throw new \InvalidArgumentException('Trace constructor allow array, Trace of NULL');
        }
    }

    /**
     * Normalizes trace items
     */
    final public function normalize()
    {
        foreach ($this->items as &$item) {
            $item = \array_replace($this->normalItem, $item);
        }
        unset($item);
    }

    /**
     * Truncates the trace by a limit
     *
     * @param int $limit
     */
    final public function truncateByLimit($limit)
    {
        if (\count($this->items) <= $limit) {
            return false;
        }
        $this->items = \array_slice($this->items, 0, $limit);
        return true;
    }

    /**
     * Truncate the trace by a options
     *
     * The options list:
     * "filter"
     * "namespace"
     * "class"
     * "file"
     * "dir"
     *
     * @param array $options
     * @return boolean
     */
    public function truncate(array $options)
    {
        $options = \array_replace($this->normalOptions, $options);
        $nitems = [];
        foreach (\array_reverse($this->items) as $item) {
            $f = $this->filterItem($item, $options);
            if ($f) {
                if ($f !== self::FILTER_LEFT) {
                    $nitems[] = $item;
                }
                $this->items = \array_reverse($nitems);
                return true;
            }
            $nitems[] = $item;
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
    public function __get($key)
    {
        switch ($key) {
            case 'items':
                return $this->items;
            case 'originalItems':
                return $this->originalItems;
        }
        throw new \LogicException('A field "'.$key.'" is not found in a Trace');
    }

    /**
     * Magic isset
     *
     * @param string $key
     * @return boolean
     */
    public function __isset($key)
    {
        return \in_array($key, ['items', 'originalItems']);
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
     *
     * @return int
     */
    final public function count()
    {
        return \count($this->items);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Traversable
     */
    final public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $offset
     * @return boolean
     */
    final public function offsetExists($offset)
    {
        return isset($this->items[$offset]);
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $offset
     * @return mixed
     * @throws \OutOfRangeException
     */
    final public function offsetGet($offset)
    {
        if (!isset($this->items[$offset])) {
            throw new \OutOfRangeException('Trace['.$offset.'] is not found');
        }
        return $this->items[$offset];
    }

    /**
     * {@inheritdoc}
     * Forbidden
     *
     * @param mixed $offset
     * @param mixed $value
     * @throws \LogicException
     */
    final public function offsetSet($offset, $value)
    {
        throw new \LogicException('Trace is read-only');
    }

    /**
     * {@inheritdoc}
     * Forbidden
     *
     * @param mixed $offset
     * @throws \LogicException
     */
    final public function offsetUnset($offset)
    {
        throw new \LogicException('Trace is read-only');
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    final public function __toString()
    {
        return helpers\Repr::trace($this->items);
    }

    /**
     * @param array $item
     * @return mixed
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
     * Cloning properties
     *
     * @param \axy\backtrace\Trace $instance
     */
    protected function cloneProperties(Trace $instance)
    {
        $this->items = $instance->items;
        $this->originalItems = $instance->originalItems;
    }

    /**
     * @var array
     */
    protected $normalItem = [
        'function' => null,
        'line' => null,
        'file' => null,
        'class' => null,
        'object' => null,
        'type' => null,
        'args' => [],
    ];

    /**
     * @var array
     */
    protected $normalOptions = [
        'filter' => null,
        'namespace' => null,
        'class' => null,
        'file' => null,
        'dir' => null,
    ];

    /**
     * @var array
     */
    protected $items;

    /**
     * @var array
     */
    private $originalItems;
}
