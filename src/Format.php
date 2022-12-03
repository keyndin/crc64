<?php

namespace Keyndin\Crc64;

enum Format: string
{
    case INT = "%d";
    case BIN = "%b";
    case HEX = "%x";
    case HEX_UPPER = "%X";
    case HEX_0 = "0x%x";
    case HEX_0_UPPER = "0x%X";
}