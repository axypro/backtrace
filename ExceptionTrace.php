<?php
/**
 * @package axy\backtrace
 */

namespace axy\backtrace;

/**
 * The class of an exception trace
 *
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 * @property-read string $file
 * @property-read int $line
 * @property-read string $originalFile
 * @property-read int $originalLine
 */
class ExceptionTrace extends Trace
{
    /**
     * Constructor
     *
     * @param mixed $items
     * @param string $file
     * @param int $line
     */
    public function __construct(array $items = null, $file = null, $line = null)
    {
        parent::__construct($items);
        if ($file === null) {
            $file = !empty($this->items[0]['file']) ? $this->items[0]['file'] : null;
            $line = !empty($this->items[0]['line']) ? $this->items[0]['line'] : null;
        }
        $this->file = $file;
        $this->line = $line;
        $this->originalFile = $file;
        $this->originalLine = $line;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $prefix
     * @return boolean
     */
    public function trimFilename($prefix)
    {
        $affected = parent::trimFilename($prefix);
        if (\strpos($this->file, $prefix) === 0) {
            $this->file = \substr($this->file, \strlen($prefix));
            $affected = true;
        }
        return $affected;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $key
     * @return mixed
     * @throws \LogicException
     */
    public function __get($key)
    {
        switch ($key) {
            case 'file':
                return $this->file;
            case 'line':
                return $this->line;
            case 'originalFile':
                return $this->originalFile;
            case 'originalLine':
                return $this->originalLine;
        }
        return parent::__get($key);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $key
     * @return boolean
     */
    public function __isset($key)
    {
        if (\in_array($key, ['file', 'line', 'originalFile', 'originalLine'])) {
            return true;
        }
        return parent::__isset($key);
    }

    /**
     * @var string
     */
    protected $file;

    /**
     * @var int
     */
    protected $line;

    /**
     * @var string
     */
    private $originalFile;

    /**
     * @var int
     */
    private $originalLine;
}
