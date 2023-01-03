<?php

namespace Keyndin\Crc64;

/**
 * Printable integer formats in PHP, made to be compatible with
 * Roman Nikitchenko & Michael Böckling's Java CRC64 implementation:
 * https://github.com/MrBuddyCasino/crc-64
 *
 * @author Florian Lang <f.lang@mailbox.org>
 */
class Format
{
    public const INT = "%d";
    public const BIN = "%b";
    public const HEX = "%x";
    public const HEX_UPPER = "%X";
    public const HEX_0 = "0x%x";
    public const HEX_0_UPPER = "0x%X";
}