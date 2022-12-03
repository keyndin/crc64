<?php

namespace Keyndin\Crc64;

require_once "crc64_morfi.php";

use PHPUnit\Framework\TestCase;

class CRC64Test extends TestCase
{
    public function testFromString(): void
    {
        $crc = CRC64::fromString("foobar")->convert()->setFormat(Format::HEX);
        self::assertEquals(crc64("foobar"), strval($crc));
    }
}
