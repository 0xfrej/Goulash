<?php

namespace Functor;

use Goulash\Functor\Functor;
use PHPUnit\Framework\TestCase;

function concreteFunction($a1, $a2 = false, $a3 = null)
{
    return $a1 . $a2 . $a3;
}

class ConcreteClass {
    public function __construct($a)
    {
    }

    public function test($a) {
        return $a;
    }

    public static function testStatic($a) {
        return $a;
    }

    protected function testProtected($a) {
        return $a;
    }
}

class FunctorTest extends TestCase
{
    public function testMapConstructor()
    {
        self::assertInstanceOf(ConcreteClass::class, Functor::mapConstructor(ConcreteClass::class, [1]));

        $results = Functor::mapConstructor(ConcreteClass::class, [[1], [2]], true);
        foreach ($results as $result) {
            self::assertInstanceOf(ConcreteClass::class, $result);
        }
    }

    public function testMapMethod()
    {
        self::assertSame(1, Functor::mapMethod(ConcreteClass::class, 'testStatic', [1]));

        $o = new ConcreteClass(1);
        self::assertSame(1, Functor::mapMethod($o, 'test', [1]));

        $this->expectException(\ReflectionException::class);
        Functor::mapMethod($o, 'testProtected', [1]);
    }

    public function testMapFunction()
    {
        $bags = [
            [
                'expected' => concreteFunction(3, true),
                'bag'      => [
                    3,
                    true,
                ]
            ],
            [
                'expected' => concreteFunction('tr', false, 'ue'),
                'bag'      => [
                    'a1' => 'tr',
                    'a3' => 'ue'
                ]
            ]
        ];

        $expected = [];
        $arrayOfBags = [];

        foreach ($bags as $bag) {
            self::assertSame($bag['expected'], Functor::mapFunction('Functor\concreteFunction', $bag['bag']));

            // prepare expected results for next assertion
            $expected[]  = $bag['expected'];
            $arrayOfBags[] = $bag['bag'];
        }

        self::assertSame($expected, Functor::mapFunction('Functor\concreteFunction', $arrayOfBags, true));
    }
}
