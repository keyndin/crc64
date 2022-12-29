<?php

use Keyndin\Crc64\Long;
use PHPUnit\Framework\TestCase;

class LongTest extends TestCase
{

    public function testFromInt(): void
    {
        self::assertEquals(42, Long::fromInt(42)->toInt());
        self::assertEquals(-42, Long::fromInt(-42)->toInt());
        self::assertEquals(34671, Long::fromInt(0x876f)->toInt());

    }

    public function testFromChar(): void
    {
        self::assertEquals(118, Long::fromString('v')->toInt());
        self::assertEquals(236, Long::fromString('v')->add('v')->toInt());
    }

    public function testFromValue(): void
    {
        self::assertEquals(1, Long::fromValue("0x001")->toInt());
        self::assertEquals(63, Long::fromValue(0x3f)->toInt());
        self::assertEquals(63, Long::fromValue("0x3f")->toInt());
    }

    public static function testAdd(): void
    {
        $long = Long::fromInt(1);
        self::assertEquals(4, Long::fromInt(1)->add(3)->toInt());
        self::assertEquals(8, Long::fromInt(1)->add(3)->add(4)->toInt());
        self::assertEquals(41, Long::fromInt(1)->add(3)->add(4)->add(33)->toInt());
    }

    public static function testSubtract(): void
    {
        self::assertEquals(0, Long::fromInt(3)->subtract(3)->toInt());
        self::assertEquals(1, Long::fromInt(4)->subtract(3)->toInt());
        self::assertEquals(-1, Long::fromInt(3)->subtract(4)->toInt());
    }

    public static function testNegativeNumbers(): void
    {
        self::assertEquals(-5, Long::fromInt(-5)->toInt());
        self::assertEquals(-87234523, Long::fromInt(-87234523)->toInt());
        self::assertEquals(-331, Long::fromInt(-331)->toInt());
        self::assertEquals(-234132432456, Long::fromInt(-234132432456)->toInt());
    }

    public static function testLshift(): void
    {
        self::assertEquals(20, Long::fromInt(5)->lshift(2)->toInt());
        self::assertEquals(7936, Long::fromInt(31)->lshift(8)->toInt());
        self::assertEquals(-467664896, Long::fromInt(-892)->lshift(19)->toInt());
        self::assertEquals(-2522015791327477760, Long::fromInt(221)->lshift(888)->toInt());
        self::assertEquals(-9223372036854775808, Long::fromInt(1)->lshift(63)->toInt());
        self::assertEquals(1, Long::fromInt(1)->lshift(64)->toInt());
    }

    public static function testRshift(): void
    {
        self::assertEquals(2, Long::fromInt(5)->rshift(1)->toInt());
        self::assertEquals(6, Long::fromInt(54)->rshift(3)->toInt());
        self::assertEquals(0, Long::fromInt(1)->rshift(63)->toInt());
        self::assertEquals(1, Long::fromInt(1)->rshift(64)->toInt());
    }

    public static function testAnd(): void
    {
        self::assertEquals(12, Long::fromInt(12)->and(12)->toInt());
        self::assertEquals(0, Long::fromInt(1)->and(2)->toInt());
        self::assertEquals(1, Long::fromInt(1)->and(3)->toInt());
        self::assertEquals(4, Long::fromInt(-12)->and(12)->toInt());
        self::assertEquals(32, Long::fromInt(32)->and(-12)->toInt());
        self::assertEquals(1181249, Long::fromInt(99878654514235123)->and(1236545)->toInt());
    }

    public static function testOr(): void
    {
        self::assertEquals(7, Long::fromInt(5)->or(2)->toInt());
        self::assertEquals(-1, Long::fromInt(-2)->or(5)->toInt());
        self::assertEquals(-5, Long::fromInt(-23)->or(41235)->toInt());
    }

    public static function testXor(): void
    {
        self::assertEquals(0, Long::fromInt(1)->xor(1)->toInt());
        self::assertEquals(435, Long::fromInt(123)->xor(456)->toInt());
        self::assertEquals(-92, Long::fromInt(-123)->xor(33)->toInt());
        self::assertEquals(-7350, Long::fromInt(-75)->xor(7423)->toInt());
        self::assertEquals(128, Long::fromInt(127)->xor(255)->toInt());
    }

    public static function testMaxValue(): void
    {
        self::assertEquals(-9223372036854775808, Long::fromInt(9223372036854775807)->add(1)->toInt());
    }

    public function testInvalidDatatype(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Long::fromInt(4)->add(new stdClass());
    }
}
