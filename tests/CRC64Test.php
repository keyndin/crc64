<?php

use Keyndin\Crc64\CRC64;
use Keyndin\Crc64\Format;
use PHPUnit\Framework\TestCase;

class CRC64Test extends TestCase
{
    public function testFromString(): void
    {
        self::assertEquals("6001682485122215966",
            strval(
                CRC64::fromString("foobar")
                    ->convert()
                    ->setFormat(Format::INT)
            )
        );
        self::assertEquals("-2523457986391399615",
            strval(
                CRC64::fromString("myfancyaddress@mail.com")
                    ->convert()
                    ->setFormat(Format::INT)
            )
        );
    }
}
