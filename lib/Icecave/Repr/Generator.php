<?php
namespace Icecave\Repr;

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
    public function __construct($maximumLength = 100, $maximumDepth = 5, $maximumElements = 3)
    {
        $this->maximumLength = $maximumLength;
        $this->maximumDepth = $maximumDepth;
        $this->maximumElements = $maximumElements;
    }

    /**
     * @param mixed $value The value to represent.
     * @param integer $currentDepth The current depth in the representation string.
     *
     * @return string A short human-readable string representation of the given value.
     */
    public function generate($value, $currentDepth = 0)
    {
        if (is_null($value)) {
            return $this->generateForNull($value, $currentDepth);
        } elseif (is_array($value)) {
            return $this->generateForArray($value, $currentDepth);
        } elseif (is_object($value)) {
            return $this->generateForObject($value, $currentDepth);
        } elseif (is_resource($value)) {
            return $this->generateForResource($value, $currentDepth);
        } else {
            return $this->generateForScalar($value, $currentDepth);
        }
    }

    /**
     * @param null $value
     * @param integer $currentDepth
     *
     * @return string
     */
    public function generateForNull($value, $currentDepth = 0)
    {
        return '<null>';
    }

    /**
     * @param array $value
     * @param integer $currentDepth
     *
     * @return string
     */
    public function generateForArray($value, $currentDepth = 0)
    {
        $size = count($value);
        if (0 === $size || $this->maximumDepth === $currentDepth) {
            return sprintf('<array %d>', $size);
        }

        $elements  = array();
        $isAssoc   = array_keys($value) !== range(0, $size - 1);
        $remaining = $this->maximumElements;

        foreach ($value as $key => $element) {
            if (0 === $remaining--) {
                $elements[] = '...';
                break;
            }

            $repr = $this->generate($elements, $currentDepth + 1);
            if ($isAssoc) {
                $repr = $key . ' => ' . $repr;
            }

            $elements[] = $repr;
        }

        return sprintf(
            '<array %d [%s]>'
            $size,
            implode(', ', $elements)
        );
    }

    /**
     * @param object $value
     * @param integer $currentDepth
     *
     * @return string
     */
    public function generateForObject($value, $currentDepth = 0) {
        if ($value instanceof IRepresentable) {
            return $value->repr($this, $currentDepth);
        }

        $reflector = new ReflectionClass($value);
        if ($reflector->hasMethod('__toString')) {
            $string = ' ' . $this->trimToLength($value->__toString());
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
     * @param integer $currentDepth
     *
     * @return string
     */
    public function generateForResource($value, $currentDepth = 0) {
        $type = get_resource_type($value);
        if ('stream' === $type) {
            $metaData = stream_get_meta_data($value);
            return sprintf('<stream resource #%d %s>', $value, $metaData['mode']);
        }
        return sprintf('<%s resource #%d>', $type, $value);
    }

    /**
     * @param scalar $value
     * @param integer $currentDepth
     *
     * @return string
     */
    public function generateForScalar($value, $currentDepth = 0) {
        $repr = var_export($value, true);
        if (is_string($value)) {
            $repr = str_replace(
                array("\r", "\n"),
                array('\r', '\n'),
                $value
            );
        }

        return sprintf(
            '<%s %s>',
            gettype($value),
            $this->trimToLength($repr)
        );
    }

    /**
     * @param string $string
     * @param string $ellipsis
     *
     * @return string
     */
    public function trimToLength($string, $ellipsis = '...') {
        if (strlen($string) > $this->maximumLength) {
            return substr(
                $string,
                0,
                $this->maximumLength - strlen($ellipsis)
            ) . $ellipsis;
        }
        return $string;
    }
}
