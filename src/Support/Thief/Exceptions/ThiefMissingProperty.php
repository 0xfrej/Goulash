<?php


namespace Goulash\Support\Thief\Exceptions;


class ThiefMissingProperty extends \RuntimeException
{
    /**
     * ThiefMissingProperty constructor.
     * @param string $className
     * @param string $property
     * @param string $type
     */
    public function __construct(string $className, string $property, string $type)
    {
        parent::__construct("{$className} does not have {$type} named {$property}");
    }
}