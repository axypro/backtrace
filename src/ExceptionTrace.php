<?php

declare(strict_types=1);

namespace axy\backtrace;

/**
 * The class of an exception trace
 *
 * @link https://github.com/axypro/backtrace/blob/master/doc/ExceptionTrace.md documentation
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
     * @param ?array $items [optional]
     *        a trace array or NULL (for the current trace)
     * @param ?string $file [optional]
     *        a filename of the exception point
     * @param ?int $line [optional]
     *        a code line of the exception point
     */
    public function __construct(?array $items = null, ?string $file = null, ?int $line = null)
    {
        if ($items === null) {
            $items = $this->loadCurrentPoint(debug_backtrace(), $file, $line);
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

    public function trimFilename(string $prefix): bool
    {
        $affected = parent::trimFilename($prefix);
        if (str_starts_with($this->props['file'], $prefix)) {
            $this->props['file'] = substr($this->props['file'], strlen($prefix));
            $affected = true;
        }
        return $affected;
    }

    public function truncate(array $options): bool
    {
        $result = parent::truncate($options);
        if (!$result) {
            $result = $this->defineFLForNoResult($options);
        }
        if ($result) {
            $items = $this->props['items'];
            $this->props['file'] = empty($items[0]['file']) ? '' : $items[0]['file'];
            $this->props['line'] = empty($items[0]['line']) ? 0 : $items[0]['line'];
        }
        return $result;
    }

    private function loadCurrentPoint(array $items, ?string &$file, ?int &$line): array
    {
        $top = array_shift($items);
        if (($file === null) && (!empty($top['file']))) {
            $file = $top['file'];
        }
        if (($line === null) && (!empty($top['line']))) {
            $line = $top['line'];
        }
        return $items;
    }

    private function defineFLForNoResult(array $options): bool
    {
        if ((!empty($options['file'])) && ($options['file'] === $this->props['file'])) {
            return true;
        }
        if ((!empty($options['dir'])) && (str_starts_with($this->props['file'], $options['dir']))) {
            return true;
        }
        return false;
    }
}
