<?php

namespace Goulash\Tests\Thief;

class DummyClass
{
    public const a = 'constA';
    protected const b = 'constB';
    private const c = 'constC';

    public $a = 'a';
    public static $staticA;

    protected $b = 'b';
    protected static $staticB;

    private $c = 'c';
    private static $staticC;

    public function __construct()
    {
        static::$staticA = 'staticA';
        static::$staticB = 'staticB';
        static::$staticC = 'staticC';
    }

    public function fA()
    {
        return 'fA';
    }

    public static function fStaticA()
    {
        return 'fStaticA';
    }

    protected function fB()
    {
        return 'fB';
    }

    protected static function fStaticB()
    {
        return 'fStaticB';
    }

    private function fC()
    {
        return 'fC';
    }

    private static function fStaticC()
    {
        return 'fStaticC';
    }
}
