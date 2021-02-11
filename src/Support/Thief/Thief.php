<?php

namespace Goulash\Support\Thief;

use Closure;
use Goulash\Support\Thief\Exceptions\ThiefMissingProperty;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * Helper class for accessing inaccessible properties and methods
 * @package Goulash\Support\Thief
 */
class Thief extends ReflectionClass
{
    /**
     * Original object instance
     * @var object
     */
    private $object;

    /**
     * Filtered collection of methods
     *
     * Abstract, constructor, destructor methods are filtered
     *
     * @var ReflectionMethod[]
     */
    private $methods = [];

    /**
     * Thief constructor.
     *
     * Constructs new Thief instance. When fully qualified class name
     * is passed as parameter, new instance of that class is created.
     * Otherwise given instance is used to create reflection.
     *
     * @param object|string $objectOrClass Instance or fully qualified class name
     * @throws ReflectionException
     */
    public function __construct($objectOrClass)
    {
        if (is_string($objectOrClass)) {
            $objectOrClass = new $objectOrClass();
        }
        parent::__construct($objectOrClass);
        $this->object = $objectOrClass;

        foreach ($this->getMethods() as $method) {
            if ($method->isAbstract() || $method->isConstructor() || $method->isDestructor()) {
                $this->methods[] = null;
            } else {
                $this->methods[$method->getName()] = $method;
            }
        }
        $this->methods = array_filter($this->methods);
    }

    /**
     * Make new instance of thief
     *
     * Static function wrapper for making new instances of self
     *
     * @param object|string $objectOrClass Instance or fully qualified class name
     * @return self|null
     */
    public static function make($objectOrClass): ?self
    {
        try {
            return new self($objectOrClass);
        } catch(ReflectionException $e) {
            return null;
        }
    }

    /**
     * Retrieve constant value
     *
     * Retrieves object's constant's value if it exists
     *
     * @param string $name Constant's name
     *
     * @return mixed Stolen constant value
     * @throws ThiefMissingProperty Thrown when constant is missing
     */
    public function const(string $name)
    {
        if ($this->hasConstant($name)) {
            return $this->getConstant($name);
        }
        throw new ThiefMissingProperty($this->name, $name, 'constant');
    }

    /**
     * Retrieve object's property reference
     *
     * Retrieve reference to object's property if it exists.
     * This function works for normal properties as well as for
     * static properties
     *
     * @param string $property Property name
     *
     * @return mixed
     * @throws ThiefMissingProperty Thrown when property is missing
     */
    public function & getPropertyRef(string $property)
    {
        if (! $this->hasProperty($property)) {
            throw new ThiefMissingProperty($this->name, $property, 'property');
        }

        /** @noinspection PhpPassByRefInspection */
        $value = & Closure::bind(function & () use ($property) {
            if ($this->getProperty($property)->isStatic()) {
                return $this::${$property};
            }
            return $this->$property;
        }, $this->object, $this->object)->__invoke();

        return $value;
    }

    /**
     * Set object's property to new value
     *
     * This method is callable via magic method.
     * It's exposed just in case there's overlapping
     * with Thief's class properties
     *
     * @param string $property Property name
     * @param mixed $value New property value
     *
     * @return self
     * @throws ThiefMissingProperty Thrown when property is missing
     */
    public function set(string $property, $value): self
    {
        $ref = &$this->getPropertyRef($property);
        $ref = $value;
        return $this;
    }

    /**
     * Call object's method
     *
     * This method is callable via magic method.
     * It's exposed just in case there's overlapping
     * with Thief's class methods
     *
     * @param string $name Method's name
     * @param mixed ...$arguments Parameters passed to method
     * @return mixed
     * @throws ThiefMissingProperty Throw when method is missing
     */
    public function call(string $name, ...$arguments)
    {
        if (! array_key_exists($name, $this->methods)) {
            throw new ThiefMissingProperty($this->name, $name, 'method');
        }

        return Closure::bind(function () use ($name, $arguments) {
            if ($this->methods[$name]->isStatic()) {
                return $this::$name(...$arguments);
            }
            return $this->$name(...$arguments);
        }, $this->object, $this->object)->__invoke();
    }

    /**
     * @internal
     * @param $property
     * @return bool
     */
    public function __isset($property): bool
    {
        $var = $this->getPropertyRef($property);
        return isset($var);
    }

    /**
     * @internal
     * @param $property
     * @param $value
     */
    public function __set($property, $value)
    {
        $this->set($property, $value);
    }

    /**
     * @internal
     * @param $property
     * @return mixed
     */
    public function __get($property)
    {
        return $this->getPropertyRef($property);
    }

    /**
     * @internal
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return $this->call($name, ...$arguments);
    }
}