<?php

namespace Keyndin\Crc64;

enum Polynomial: string
{
    case ECMA = "0xC96C5795D7870F42";
    case ISO = "-3932672073523589310";

    public function toInt(): Long
    {
        return Long::fromInt($this->value);
    }
}
