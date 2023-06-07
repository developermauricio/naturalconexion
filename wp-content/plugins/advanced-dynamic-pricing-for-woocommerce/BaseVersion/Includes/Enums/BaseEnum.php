<?php

namespace ADP\BaseVersion\Includes\Enums;

use ADP\BaseVersion\Includes\Enums\Exceptions\BadMethodCallException;
use ADP\BaseVersion\Includes\Enums\Exceptions\UnexpectedValueException;
use ReflectionClass;
use ReflectionException;
use function array_key_exists;
use function array_keys;
use function array_search;
use function get_class;
use function in_array;

abstract class BaseEnum
{
    /**
     * Enum value
     *
     * @var mixed
     */
    protected $value;

    /**
     * Store existing constants in a static cache per object.
     *
     * @var array
     */
    protected static $cache = array();

    const __default = null;

    /**
     * Creates a new value of some type
     *
     * @param mixed $value
     *
     * @throws UnexpectedValueException|ReflectionException if incompatible type is given.
     */
    public function __construct($value = null)
    {
        if ($value instanceof static) {
            $value = $value->getValue();
        }

        if ($value === null) {
            $value = static::__default;
        }

        if ( ! $this->isValid($value)) {
            throw new UnexpectedValueException("Value '$value' is not part of the enum " . static::class);
        }

        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Returns the enum key (i.e. the constant name).
     *
     * @return mixed
     * @throws ReflectionException
     */
    public function getKey()
    {
        return static::search($this->value);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->value;
    }

    /**
     * Determines if Enum should be considered equal with the variable passed as a parameter.
     * Returns false if an argument is an object of different class or not an object.
     *
     * @param self $variable
     *
     * @return bool
     */
    public function equals($variable)
    {
        return $variable instanceof self && $this->getValue() === $variable->getValue() && static::class === get_class($variable);
    }

    /**
     * Returns the names (keys) of all constants in the Enum class
     *
     * @return array
     * @throws ReflectionException
     */
    public static function keys()
    {
        return array_keys(static::toArray());
    }

    /**
     * Returns instances of the Enum class of all Enum constants
     *
     * @return static[] Constant name in key, Enum instance in value
     * @throws UnexpectedValueException|ReflectionException
     */
    public static function values()
    {
        $values = array();

        foreach (static::toArray() as $key => $value) {
            $values[$key] = new static($value);
        }

        return $values;
    }

    /**
     * Returns all possible values as an array
     *
     * @return array Constant name in key, constant value in value
     * @throws ReflectionException
     */
    public static function toArray()
    {
        $class = static::class;

        if ( ! isset(static::$cache[$class])) {
            $reflection = new ReflectionClass($class);
            $constants  = $reflection->getConstants();
            unset($constants['__default']);
            static::$cache[$class] = $constants;
        }

        return static::$cache[$class];
    }

    /**
     * Check if is valid enum value
     *
     * @param $value
     *
     * @return bool
     * @throws ReflectionException
     */
    public static function isValid($value)
    {
        return in_array($value, static::toArray(), true);
    }

    /**
     * Check if is valid enum key
     *
     * @param $key
     *
     * @return bool
     * @throws ReflectionException
     */
    public static function isValidKey($key)
    {
        $array = static::toArray();

        return isset($array[$key]) || array_key_exists($key, $array);
    }

    /**
     * Return key for value
     *
     * @param $value
     *
     * @return mixed
     * @throws ReflectionException
     */
    public static function search($value)
    {
        return array_search($value, static::toArray(), true);
    }

    /**
     * Returns a value when called statically like so: MyEnum::SOME_VALUE() given SOME_VALUE is a class constant
     *
     * @param string $name
     * @param array $arguments
     *
     * @return static
     * @throws BadMethodCallException
     * @throws ReflectionException
     * @throws UnexpectedValueException
     */
    public static function __callStatic($name, $arguments)
    {
        $array = static::toArray();
        if (isset($array[$name]) || array_key_exists($name, $array)) {
            return new static($array[$name]);
        }

        throw new BadMethodCallException("No static method or enum constant '$name' in class " . static::class);
    }

}
