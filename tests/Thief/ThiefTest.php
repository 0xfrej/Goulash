<?php
namespace Goulash\Tests\Thief;

include __DIR__.'/DummyClass.php';

use Goulash\Thief\Thief;
use PHPUnit\Framework\TestCase;

class ThiefTest extends TestCase
{
    protected $thiefInstance;

    public function setUp() : void
    {
        parent::setUp();

        $this->thiefInstance = Thief::make(DummyClass::class);
    }

    public function test__get()
    {
        self::assertSame('a', $this->thiefInstance->a);
        self::assertSame('b', $this->thiefInstance->b);
        self::assertSame('c', $this->thiefInstance->c);

        // test if static props can be retrieved
        self::assertSame('staticA', $this->thiefInstance->staticA);
        self::assertSame('staticB', $this->thiefInstance->staticB);
        self::assertSame('staticC', $this->thiefInstance->staticC);
    }

    public function test__call()
    {
        self::assertSame('fA', $this->thiefInstance->fA());
        self::assertSame('fB', $this->thiefInstance->fB());
        self::assertSame('fC', $this->thiefInstance->fC());

        self::assertSame('fStaticA', $this->thiefInstance->fStaticA());
        self::assertSame('fStaticB', $this->thiefInstance->fStaticB());
        self::assertSame('fStaticC', $this->thiefInstance->fStaticC());
    }

    public function test__isset()
    {
        self::assertTrue(isset($this->thiefInstance->a));
        self::assertTrue(isset($this->thiefInstance->b));
        self::assertTrue(isset($this->thiefInstance->c));

        self::assertTrue(isset($this->thiefInstance->staticA));
        self::assertTrue(isset($this->thiefInstance->staticB));
        self::assertTrue(isset($this->thiefInstance->staticC));
    }

    public function test__set()
    {
        $this->thiefInstance->a = '1';
        $this->thiefInstance->b = '2';
        $this->thiefInstance->c = '3';
        $this->thiefInstance->staticA = 'static1';
        $this->thiefInstance->staticB = 'static2';
        $this->thiefInstance->staticC = 'static3';

        self::assertSame('1', $this->thiefInstance->a);
        self::assertSame('2', $this->thiefInstance->b);
        self::assertSame('3', $this->thiefInstance->c);

        // test if static props can be set
        self::assertSame('static1', $this->thiefInstance->staticA);
        self::assertSame('static2', $this->thiefInstance->staticB);
        self::assertSame('static3', $this->thiefInstance->staticC);
    }

    public function test__construct()
    {
        // TODO: Add Thief constructor test
        self::markTestIncomplete();
    }

    public function testConst()
    {
        self::assertSame('constA', $this->thiefInstance->const('a'));
        self::assertSame('constB', $this->thiefInstance->const('b'));
        self::assertSame('constC', $this->thiefInstance->const('c'));
    }

    public function testBindAndCall()
    {
        $expected = $this->thiefInstance->c;

        $result = $this->thiefInstance->bindAndCall(function() {
            return $this->c;
        });

        self::assertSame($expected, $result);
    }

    public function testBindAndCallRef()
    {
        $result = &$this->thiefInstance->bindAndCallRef(function & () {
            return $this->c;
        });

        $result = 42;

        self::assertSame(42, $this->thiefInstance->c);
    }
}
