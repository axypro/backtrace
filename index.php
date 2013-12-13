<?php
/**
 * Tracing in php
 *
 * @package axy\backtrace
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 * @license https://raw.github.com/axypro/backtrace/master/LICENSE MIT
 * @uses PHP5.4+
 */

namespace axy\backtrace;

if (!\is_file(__DIR__.'/vendor/autoload.php')) {
    throw new \LogicException('Please: ./composer.phar install --dev');
}

require_once(__DIR__.'/vendor/autoload.php');
