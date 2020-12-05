<?php

namespace Goulash\Support\Thief;

use Closure;
use Goulash\Support\Thief\Exceptions\ThiefMissingProperty;
use Illuminate\Support\Collection;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class Thief extends ReflectionClass
{
    /**
     * @var object Original object instance
     */
    private $object;

    /**
     * @var Collection|ReflectionMethod[] Filtered collection of methods
     */
    private $methods;

    /**
     * Thief constructor.
     * @param object $object
     * @throws ReflectionException
     */
    public function __construct(object $object)
    {
        parent::__construct($object);
        $this->object = $object;

        $this->methods = collect($this->getMethods())->mapWithKeys(function (ReflectionMethod $method) {
            if ($method->isAbstract() || $method->isConstructor() || $method->isDestructor()) {
                return null;
            }

            return [$method->getName() => $method];
        })->filter();
    }

    /**
     * Make new instance of thief
     * @param object $object
     * @return self|null
     */
    public static function make(object $object): ?self
    {
        try {
            return new self($object);
        } catch(ReflectionException $e) {
            return null;
        }
    }

    /**
     * Retrieve constant value
     * @param string $name Constant name
     *
     * @return mixed Constant value
     *
     * @throws ThiefMissingProperty
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
     * @param string $property Property name
     *
     * @return mixed
     *
     * @throws ThiefMissingProperty
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
     * @param string $property Property name
     * @param string $value Property value
     * @return $this
     */
    public function set(string $property, string $value): self
    {
        $ref = &$this->getPropertyRef($property);
        $ref = $value;
        return $this;
    }

    public function __isset($property): bool
    {
        $var = $this->getPropertyRef($property);
        return isset($var);
    }

    public function __set($property, $value)
    {
        $this->set($property, $value);
    }

    public function __get($property)
    {
        return $this->getPropertyRef($property);
    }

    public function __call($name, $arguments)
    {
        if (! $this->hasMethod($name)) {
            throw new ThiefMissingProperty($this->name, $name, 'method');
        }

        return Closure::bind(function () use ($name, $arguments) {
            if ($this->methods->contains($name)) {
                return $this::$name(...$arguments);
            }
            return $this->$name(...$arguments);
        }, $this->object, $this->object)->__invoke();
    }
}