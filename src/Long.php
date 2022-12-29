<?php

namespace Keyndin\Crc64;

use InvalidArgumentException;

class Long
{
    private static int $exp = 64;
    /* @var bool[] */
    private array $value = [];

    protected function __construct()
    {
        for ($i = 0; $i < self::$exp; $i++) {
            $this->value[$i] = false;
        }
    }

    /**
     * Return integer representation
     * @return int
     * @throws InvalidArgumentException
     */
    public function toInt(): int
    {
        $val = 0;
        for ($i = 0; $i < self::$exp - 1; $i++) {
            $val += ($this->value[$i] xor $this->value[self::$exp - 1]) << $i;
        }
        return $this->value[self::$exp - 1] ? -1 * ($val + 1) : $val;
    }

    /**
     * Bitwise And operator
     *
     * @param $val
     * @return $this
     */
    public function and($val): self
    {
        $val = Long::getFromType($val);
        $res = new Long();
        for ($i = 0; $i < self::$exp; $i++) {
            $res->value[$i] = $this->value[$i] && $val->value[$i];
        }
        return $res;
    }

    /**
     * Bitwise Or operator
     *
     * @param $val
     * @return $this
     */
    public function or($val): self
    {
        $val = Long::getFromType($val);
        $res = new Long();
        for ($i = 0; $i < self::$exp; $i++) {
            $res->value[$i] = $this->value[$i] || $val->value[$i];
        }
        return $res;
    }

    /**
     * Bitwise Xor operator
     *
     * @param $val
     * @return $this
     */
    public function xor($val): self
    {
        $val = Long::getFromType($val);
        $res = new Long();
        for ($i = 0; $i < self::$exp; $i++) {
            $res->value[$i] = ($this->value[$i] xor $val->value[$i]);
        }
        return $res;
    }

    /**
     * Add a numeric value (either an integer, string or Long) to a Long
     *
     * @param $val
     * @return $this
     * @throws InvalidArgumentException
     */
    public function add($val): self
    {
        $val = Long::getFromType($val);
        $res = new Long();
        $carry = false;
        for ($i = 0; $i < self::$exp; $i++) {
            $n_carry = ($this->value[$i] && $val->value[$i])
                || ($carry && $this->value[$i])
                || ($carry && $val->value[$i]);
            $res->value[$i] = ($this->value[$i] + $val->value[$i] + $carry) == 1
                || (($this->value[$i] + $val->value[$i] + $carry) == 3);
            $carry = $n_carry;
        }
        return $res;
    }

    /**
     * Subtract a numeric value (either an integer, string or Long) to a Long
     *
     * @throws InvalidArgumentException
     */
    public function subtract($val): self
    {
        $val = Long::getFromType($val)->inverse();
        return $this->add($val);
    }

    /**
     * Invert all bits
     *
     * @return $this
     */
    public function invert(): self
    {
        $res = new Long();
        for ($i = 0; $i < self::$exp; $i++) {
            $res->value[$i] = !$this->value[$i];
        }
        return $res;
    }

    /**
     * Convert to two's complement, effectively making a positive number negative and vice versa
     *
     * @throws InvalidArgumentException
     */
    public function inverse(): self
    {
        return $this->invert()->add(1);
    }

    /**
     * Check whether a numeric value (either an integer, string or Long) are equal
     *
     * @param $val
     * @return bool
     */
    public function equals($val): bool
    {
        $val = self::getFromType($val);
        return $this->value === $val->value;
    }

    /**
     * Bitwise left shift
     *
     * @param int $val
     * @return $this
     */
    public function lshift(int $val): self
    {
        $val = 0x3F & $val;
        $res = new Long();
        for ($i = self::$exp - 1; $i >= 0; $i--) {
            $n = $i - $val;
            $res->value[$i] = $n >= 0 && $this->value[$n];
        }
        return $res;
    }

    /**
     * Bitwise right shift
     *
     * @param int $val
     * @return $this
     */
    public function rshift(int $val): self
    {
        $val = 0x3F & $val;
        $res = new Long();
        for ($i = 0; $i < self::$exp; $i++) {
            $n = $i + $val;
            $res->value[$i] = $n <= self::$exp - 1 && $this->value[$n];
        }
        return $res;
    }

    /**
     * New Long from integer value
     *
     * @param int $val
     * @return static
     * @throws InvalidArgumentException
     */
    public static function fromInt(int $val): self
    {
        $self = new static();
        $bin_val = [];
        $neg = $val < 0;
        while ($val != 0) {
            $bin_val[] = $val % 2 != 0;
            $val = intdiv($val, 2);
        }
        for ($i = 0; $i < sizeof($bin_val) && $i < self::$exp - 1; $i++) {
            $self->value[$i] = $bin_val[$i];
        }
        if ($neg) return $self->inverse();
        return $self;
    }

    /**
     * New Long from string value
     * TODO: Rework this! Lshift by one byte per character?
     *
     * @param string $val
     * @return static
     */
    public static function fromString(string $val): self
    {
        $self = new static();
        foreach (str_split($val) as $char) {
            $self = $self->add(ord($char));
        }
        return $self;
    }

    /**
     * Convert either string or integer value to Long
     *
     * @param $val
     * @return static
     */
    public static function fromValue($val): self
    {
        return self::getFromType($val);
    }

    /**
     * Convert a numeric value to Long
     *
     * @throws InvalidArgumentException
     */
    protected static function getFromType($val): self
    {
        if (is_numeric($val)) {
            $val = intval($val);
        } elseif (is_string($val)
            && (trim($val, '0..9A..Fa..f') == '' || trim($val, '0..9A..Fa..f') == 'x')) {
            $val = hexdec($val);
        }
        if (is_int($val)) return Long::fromInt(intval($val));
        if (is_string($val)) return Long::fromString($val);
        if (gettype($val) === 'object' && $val::class === self::class) return $val;
        throw new InvalidArgumentException(
            sprintf(
                'Unsupported Datatype conversion, expecting value to be of ' .
                'either `string`, `int`, or `%s`, received `%s` instead.',
                self::class,
                gettype($val) != 'object' ? gettype($val) : $val::class
            )
        );
    }

    /**
     * Return formatted Long number
     *
     * @param Format $format
     * @return string
     */
    public function format(Format $format): string
    {
        return sprintf($format->value, $this->toInt());
    }

    /**
     * Get string representation of Long
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->format(Format::INT);
    }
}