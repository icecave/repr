<?php
namespace Icecave\Repr;

/**
 * Generate informational string representations of any value.
 */
class Repr
{
    /**
     * Generate an informational string representation of any value.
     *
     * @param mixed $value The value for which a string reprsentation should be generator.
     *
     * @return string The string representation of $value.
     */
    public static function repr($value)
    {
        if (null === self::$generator) {
            self::install(new Generator);
        }

        return self::$generator->generate($value);
    }

    public static function install(Generator $generator)
    {
        self::$generator = $generator;
    }

    private static $generator;
}
