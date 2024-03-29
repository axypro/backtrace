<?php

declare(strict_types=1);

namespace axy\backtrace\helpers;

/**
 * Representation of a backtrace as a string
 *
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */
class Represent
{
    /** The maximum length of a method argument */
    private const MAX_LEN = 15;

    /** Represents an argument of a method as a string */
    public static function arg(mixed $value): string
    {
        return match (gettype($value)) {
            'NULL' => 'NULL',
            'boolean' => $value ? 'true' : 'false',
            'array' => 'Array',
            'object' => 'Object(' . get_class($value) . ')',
            'string' => "'" . self::cutString((string)$value) . "'",
            default => (string)$value,
        };
    }

    /** Represents a method call as a string */
    public static function method(array $item): string
    {
        if (empty($item['function'])) {
            return '';
        }
        $method = $item['function'];
        if (!empty($item['class'])) {
            $type = (empty($item['type']) ? '->' : $item['type']);
            $class = $item['class'] . $type;
        } else {
            $class = '';
        }
        $args = $item['args'] ?? [];
        foreach ($args as &$arg) {
            $arg = self::arg($arg);
        }
        unset($arg);
        return $class . $method . '(' . implode(', ', $args) . ')';
    }

    /** Represents a call point as a string */
    public static function point(array $item): string
    {
        if (empty($item['file'])) {
            $result = '[internal function]';
        } else {
            $result = $item['file'];
            if (!empty($item['line'])) {
                $result .= "({$item['line']})";
            }
        }
        return $result;
    }

    /**
     * Represents a trace item as a string
     *
     * @param array $item
     *        a backtrace item
     * @param ?int $number [optional]
     *        a number of the item in the trace
     * @return string
     */
    public static function item(array $item, ?int $number = null): string
    {
        if ($number !== null) {
            $number = "#$number ";
        }
        return $number . self::point($item) . ': ' . self::method($item);
    }

    /**
     * Represents a trace as a string
     *
     * @param array $items
     *        a trace items list
     * @param string $sep
     *        a line separator
     * @return string
     */
    public static function trace(array $items, string $sep = PHP_EOL): string
    {
        $lines = [];
        foreach ($items as $number => $item) {
            $lines[] = self::item($item, $number);
        }
        $lines[] = '#' . (count($items)) . ' {main}';
        return implode($sep, $lines) . $sep;
    }

    /**
     * Cuts a string by the max length
     *
     * @param string $str
     * @return string
     */
    private static function cutString(string $str): string
    {
        static $mb;
        if ($mb === null) {
            $mb = function_exists('mb_strlen');
        }
        if ($mb) {
            $len = mb_strlen($str, 'UTF-8');
        } else {
            $len = strlen($str);
        }
        if ($len > self::MAX_LEN) {
            if ($mb) {
                return mb_substr($str, 0, self::MAX_LEN, 'UTF-8') . '...';
            } else {
                return substr($str, 0, self::MAX_LEN) . '...';
            }
        }
        return $str;
    }
}
