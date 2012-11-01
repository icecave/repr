<?php
namespace Icecave\Repr;

use ReflectionClass;

/**
 * Generate informational string representations of any value.
 */
class Generator
{
    /**
     * @param integer $maximumLength   The maximum length of strings within the result.
     * @param integer $maximumDepth    The maximum depth to represent for nested types.
     * @param integer $maximumElements The maximum number of elements to include in representations of container types.
     */
    public function __construct($maximumLength = 50, $maximumDepth = 3, $maximumElements = 3)
    {
        $this->maximumLength = $maximumLength;
        $this->maximumDepth = $maximumDepth;
        $this->maximumElements = $maximumElements;
    }

    /**
     * @param mixed   $value        The value to represent.
     * @param integer $currentDepth The current depth in the representation string.
     *
     * @return string A short human-readable string representation of the given value.
     */
    public function generate($value, $currentDepth = 0)
    {
        if (is_array($value)) {
            return $this->generateForArray($value, $currentDepth);
        } elseif (is_object($value)) {
            return $this->generateForObject($value, $currentDepth);
        } elseif (is_resource($value)) {
            return $this->generateForResource($value, $currentDepth);
        } elseif (is_string($value)) {
            return $this->generateForString($value, $currentDepth);
        } elseif (is_float($value)) {
            return $this->generateForFloat($value, $currentDepth);
        } else {
            return $this->generateForOther($value, $currentDepth);
        }
    }

    /**
     * @param array   $value
     * @param integer $currentDepth
     *
     * @return string
     */
    public function generateForArray($value, $currentDepth = 0)
    {
        $size   = count($value);
        $vector = array_keys($value) === range(0, $size - 1);
        $more   = null;

        if (0 === $size) {
            return '[]';
        } elseif ($this->maximumDepth === $currentDepth) {
            return sprintf('[<%d>]', $size);
        } elseif ($this->maximumElements < $size) {
            $value = array_slice($value, 0, $this->maximumElements);
            $more  = sprintf('<+%d>', $size - $this->maximumElements);
        }

        $elements = array();

        // Generate element representations for vector-like array ...
        if ($vector) {
            foreach ($value as $element) {
                $elements[] = $this->generate($element, $currentDepth + 1);
            }

        // Generate element representations for associative array ...
        } else {
            foreach ($value as $key => $element) {
                $elements[] = $this->generate($key) . ' => ' . $this->generate($element, $currentDepth + 1);
            }
        }

        if ($more) {
            $elements[] = $more;
        }

        return '[' . implode(', ', $elements) . ']';
    }

    /**
     * @param object  $value
     * @param integer $currentDepth
     *
     * @return string
     */
    public function generateForObject($value, $currentDepth = 0)
    {
        if ($value instanceof IRepresentable) {
            return $value->stringRepresentation($this, $currentDepth);
        }

        $reflector = new ReflectionClass($value);
        if ($reflector->hasMethod('__toString')) {
            $string = ' ' . $this->generateForString($value->__toString(), $currentDepth);
        } else {
            $string = '';
        }

        return sprintf(
            '<%s%s @ %s>',
            get_class($value),
            $string,
            spl_object_hash($value)
        );
    }

    /**
     * @param resource $value
     * @param integer  $currentDepth
     *
     * @return string
     */
    public function generateForResource($value, $currentDepth = 0)
    {
        $type = get_resource_type($value);
        if ('stream' === $type) {
            $metaData = stream_get_meta_data($value);
            $info = ' ' . $metaData['mode'];
        } else {
            $info = '';
        }

        return sprintf(
            '<resource: %s #%d%s>',
            $type,
            $value,
            $info
        );
    }

    public function generateForString($value, $currentDepth = 0)
    {
        $length = strlen($value);
        $open   = '"';
        $close  = '"';

        if ($length > $this->maximumLength) {
            $close = '...';
            $length = $this->maximumLength;
        }

        $repr = '';

        for ($index = 0; $index < $length; ++$index) {
            $ch = $value{$index};

            if ($ch === "\n") {
                $ch = '\n';
            } elseif ($ch === "\r") {
                $ch = '\r';
            } elseif ($ch === "\t") {
                $ch = '\t';
            } elseif ($ch === "\v") {
                $ch = '\v';
            } elseif (version_compare(PHP_VERSION, '5.4.0', '>=') && $ch === "\e") {
                $ch = '\e';
            } elseif ($ch === "\f") {
                $ch = '\f';
            } elseif ($ch === "\\") {
                $ch = '\\\\';
            } elseif ($ch === '$') {
                $ch = '\$';
            } elseif ($ch === '"') {
                $ch = '\"';
            } elseif (!ctype_print($ch)) {
                $ch = sprintf('\x%02x', ord($ch));
            }

            $repr .= $ch;
        }

        return $open . $repr . $close;
    }

    /**
     * @param scalar  $value
     * @param integer $currentDepth
     *
     * @return string
     */
    public function generateForFloat($value, $currentDepth = 0)
    {
        if (0.0 === fmod($value, 1.0)) {
            return $value . '.0';
        }

        return strval($value);
    }

    /**
     * @param scalar  $value
     * @param integer $currentDepth
     *
     * @return string
     */
    public function generateForOther($value, $currentDepth = 0)
    {
        return strtolower(var_export($value, true));
    }
}
