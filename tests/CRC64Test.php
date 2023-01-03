<?php

use Keyndin\Crc64\CRC64;
use Keyndin\Crc64\Format;
use PHPUnit\Framework\TestCase;

class CRC64Test extends TestCase
{
    public function testFromString(): void
    {
        self::assertEquals(6001682485122215966,
            CRC64::fromString("foobar")
                ->setFormat(Format::INT)
                ->getValue()
        );
        self::assertEquals(-2523457986391399615,
            CRC64::fromString("myfancyaddress@mail.com")
                ->setFormat(Format::INT)
                ->getValue()
        );
    }

    public function testGetGetValue(): void
    {
        self::assertEquals(
            4486629689757182440,
            CRC64::fromString("testme")->getValue()
        );
    }

    public function testGetBytes(): void
    {
        self::assertEquals(
            [2, -33, -98, -106, 39, 46, 3, -49],
            CRC64::fromString("Yet Another Test Value")->getBytes()
        );
        self::assertEquals(
            [59, -12, 5, -52, -17, 91, 11, -7],
            CRC64::fromString("Can You Hear Me Now?!")->getBytes()
        );
    }
}
