<?php

use Keyndin\Crc64\CRC64;
use Keyndin\Crc64\Format;
use PHPUnit\Framework\TestCase;

class CRC64Test extends TestCase
{
    public function testFromString(): void
    {
        $crc = CRC64::fromString("foobar")->convert()->setFormat(Format::HEX);
        self::assertEquals("asdooo2", strval($crc));
    }
}
