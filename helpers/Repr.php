<?php
/**
 * @package axy\backtrace
 */

namespace axy\backtrace\helpers;

/**
 * The trace representation as a string
 *
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */
class Repr
{
    const MAXLEN = 15;

    /**
     * Represents a argument of a method as a string
     *
     * @param mixed $value
     * @return string
     */
    public static function arg($value)
    {
        switch (\gettype($value)) {
            case 'NULL':
                return 'NULL';
            case 'boolean':
                return $value ? 'true' : 'false';
            case 'array':
                return 'Array';
            case 'object':
                return 'Object('.\get_class($value).')';
            case 'string':
                return "'".self::cutString((string)$value)."'";
            default:
                return (string)$value;
        }
    }

    /**
     * Represents a method call as a string
     *
     * @param array $item
     *        a trace item
     * @return string
     */
    public static function method(array $item)
    {
        if (empty($item['function'])) {
            return '';
        }
        $result = $item['function'];
        if (!empty($item['class'])) {
            $result = $item['class'].(empty($item['type']) ? '->' : $item['type']).$result;
        }
        if (!empty($item['args'])) {
            $args = [];
            foreach ($item['args'] as $arg) {
                $args[] = self::arg($arg);
            }
            $result .= '('.\implode(', ', $args).')';
        } else {
            $result .= '()';
        }
        return $result;
    }

    /**
     * Represents a call point as a string
     *
     * @param array $item
     * @return string
     */
    public static function point(array $item)
    {
        if (empty($item['file'])) {
            return '[internal function]';
        }
        if (empty($item['line'])) {
            return $item['file'];
        } else {
            return $item['file'].'('.$item['line'].')';
        }
    }

    /**
     * Represents a trace item as a string
     *
     * @param array $item
     * @param int $number [optional]
     *        a number of the item in the trace
     * @return string
     */
    public static function item(array $item, $number = null)
    {
        if ($number !== null) {
            $number = '#'.$number.' ';
        }
        return $number.self::point($item).': '.self::method($item);
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
    public static function trace(array $items, $sep = \PHP_EOL)
    {
        $lines = [];
        foreach ($items as $number => $item) {
            $lines[] = self::item($item, $number);
        }
        $lines[] = '#'.(\count($items)).' {main}';
        return \implode($sep, $lines).$sep;
    }

    /**
     * Cut a string by maxlen
     *
     * @param string $str
     * @return string
     */
    private static function cutString($str)
    {
        static $mb;
        if (!$mb) {
            $mb = \function_exists('mb_strlen');
        }
        if ($mb) {
            $len = \mb_strlen($str, 'UTF-8');
        } else {
            $len = \strlen($str);
        }
        if ($len > self::MAXLEN) {
            if ($mb) {
                return \mb_substr($str, 0, self::MAXLEN, 'UTF-8').'...';
            } else {
                return \substr($str, 0, self::MAXLEN).'...';
            }
        }
        return $str;
    }
}
