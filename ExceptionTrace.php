<?php
/**
 * @package axy\backtrace
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\backtrace;

/**
 * The class of an exception trace
 *
 * @property-read string $file
 *                the current state of the point filename
 * @property-read int $line
 *                the current state of the point code line
 * @property-read string $originalFile
 *                the original state of the point filename
 * @property-read int $originalLine
 *                the original state of the point code line
 */
class ExceptionTrace extends Trace
{
    /**
     * The constructor
     *
     * @param mixed $items [optional]
     *        a trace array or NULL (for the current trace)
     * @param string $file [optional]
     *        a filename of the exception point
     * @param int $line [optional]
     *        a code line of the exception point
     */
    public function __construct(array $items = null, $file = null, $line = null)
    {
        if ($items === null) {
            $items = debug_backtrace();
            $top = array_shift($items);
            if (($file === null) && (!empty($top['file']))) {
                $file = $top['file'];
            }
            if (($line === null) && (!empty($top['line']))) {
                $line = $top['line'];
            }
        }
        parent::__construct($items);
        if (($file === null) && (isset($items[0]))) {
            $file = $items[0]['file'];
        }
        if (($line === null) && (isset($items[0]))) {
            $line = $items[0]['line'];
        }
        $nProps = [
            'file' => $file,
            'line' => $line,
            'originalFile' => $file,
            'originalLine' => $line,
        ];
        $this->props = array_replace($this->props, $nProps);
    }

    /**
     * {@inheritdoc}
     */
    public function trimFilename($prefix)
    {
        $affected = parent::trimFilename($prefix);
        if (strpos($this->props['file'], $prefix) === 0) {
            $this->props['file'] = substr($this->props['file'], strlen($prefix));
            $affected = true;
        }
        return $affected;
    }

    /**
     * {@inheritdoc}
     */
    public function truncate(array $options)
    {
        $result = parent::truncate($options);
        if (!$result) {
            if (!empty($options['file'])) {
                if ($options['file'] === $this->props['file']) {
                    $result = true;
                }
            }
            if (!empty($options['dir'])) {
                if (strpos($this->props['file'], $options['dir']) === 0) {
                    $result = true;
                }
            }
        }
        if ($result) {
            $items = $this->props['items'];
            $this->props['file'] = empty($items[0]['file']) ? '' : $items[0]['file'];
            $this->props['line'] = empty($items[0]['line']) ? 0 : $items[0]['line'];
        }
        return $result;
    }
}
