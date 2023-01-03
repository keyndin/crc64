<?php

namespace Keyndin\Crc64;

/**
 * CRC64 Polynomials, made to be compatible with
 * Roman Nikitchenko & Michael Böckling's Java CRC64 implementation:
 * https://github.com/MrBuddyCasino/crc-64
 *
 * @author Florian Lang <f.lang@mailbox.org>
 */
class Polynomial
{
    public const ECMA = "0xC96C5795D7870F42";
    public const ISO = "-3932672073523589310";
}
