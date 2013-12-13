<?php
/**
 * @package axy\backtrace
 */

namespace axy\backtrace;

/**
 * The class of a calling trace
 *
 * @author Grigoriev Oleg <go.vasac@gmail.com>
 * @property-read array $items
 * @property-read array $originalItems
 */
class Trace
{
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
    public function normalize()
    {
        foreach ($this->items as &$item) {
            $item = \array_replace($this->normalItem, $item);
        }
        unset($item);
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
    public function __set($key, $value)
    {
        throw new \LogicException('Trace is read-only');
    }

    /**
     * Magic unset (forbidden)
     *
     * @param string $key
     * @throws \LogicException
     */
    public function __unset($key)
    {
        throw new \LogicException('Trace is read-only');
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
    protected $items;

    /**
     * @var array
     */
    private $originalItems;
}
