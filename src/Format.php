<?php

namespace Keyndin\Crc64;

/**
 * Printable integer formats in PHP, made to be compatible with
 * Roman Nikitchenko & Michael Böckling's Java CRC64 implementation:
 * https://github.com/MrBuddyCasino/crc-64
 *
 * @author Florian Lang <f.lang@mailbox.org
 */
enum Format: string
{
    case INT = "%d";
    case BIN = "%b";
    case HEX = "%x";
    case HEX_UPPER = "%X";
    case HEX_0 = "0x%x";
    case HEX_0_UPPER = "0x%X";
}