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
    public function and($val): Long
    {
        $val = Long::getFromType($val);
        for ($i = 0; $i < self::$exp; $i++) {
            $this->value[$i] &= $val->value[$i];
        }
        return $this;
    }

    /**
     * Bitwise Or operator
     *
     * @param $val
     * @return $this
     */
    public function or($val): Long
    {
        $val = Long::getFromType($val);
        for ($i = 0; $i < self::$exp; $i++) {
            $this->value[$i] |= $val->value[$i];
        }
        return $this;
    }

    /**
     * Bitwise Xor operator
     *
     * @param $val
     * @return $this
     */
    public function xor($val): Long
    {
        $val = Long::getFromType($val);
        for ($i = 0; $i < self::$exp; $i++) {
            $this->value[$i] = ($this->value[$i] xor $val->value[$i]);
        }
        return $this;
    }

    /**
     * Add a numeric value (either an integer, string or Long) to a Long
     *
     * @param $val
     * @return $this
     * @throws InvalidArgumentException
     */
    public function add($val): Long
    {
        $val = Long::getFromType($val);
        $carry = false;
        for ($i = 0; $i < self::$exp; $i++) {
            $n_carry = ($this->value[$i] && $val->value[$i])
                || ($carry && $this->value[$i])
                || ($carry && $val->value[$i]);
            $this->value[$i] = ($this->value[$i] + $val->value[$i] + $carry) == 1
                || (($this->value[$i] + $val->value[$i] + $carry) == 3);
            $carry = $n_carry;
        }
        return $this;
    }

    /**
     * Subtract a numeric value (either an integer, string or Long) to a Long
     *
     * @throws InvalidArgumentException
     */
    public function subtract($val): Long
    {
        $val = Long::getFromType($val)->inverse();
        return $this->add($val);
    }

    /**
     * Invert all bits
     *
     * @return $this
     */
    public function invert(): Long
    {
        for ($i = 0; $i < self::$exp; $i++) {
            $this->value[$i] = !$this->value[$i];
        }
        return $this;
    }

    /**
     * Convert to two's complement, effectively making a positive number negative and vice versa
     *
     * @throws InvalidArgumentException
     */
    public function inverse(): Long
    {
        $this->invert();
        return $this->add(1);
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
    public function lshift(int $val): Long
    {
        // TODO: rework this!
        for ($i = self::$exp - 1; $i >= 0; $i--) {
            $n = $i - $val >= 0 ? $i - $val : $i - ($val % 63) + 64;
            $this->value[$i] = $this->value[$n];
        }
        return $this;
    }

    /**
     * Bitwise right shift
     *
     * @param int $val
     * @return $this
     */
    public function rshift(int $val): Long
    {
        // TODO: rework this!
        for ($i = 0; $i < self::$exp; $i++) {
            if (($n = $i + $val) < self::$exp) {
                $this->value[$i] = $this->value[$n];
            } else {
                $this->value[$i] = false;
            }
        }
        return $this;
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
        if ($neg) $self->inverse();
        return $self;
    }

    /**
     * New Long from string value
     *
     * @param string $val
     * @return static
     */
    public static function fromString(string $val): self
    {
        $self = new static();
        foreach (str_split($val) as $char) {
            $self->add(ord($char));
        }
        return $self;
    }

    /**
     * Convert a numeric value to Long
     *
     * @throws InvalidArgumentException
     */
    protected static function getFromType($val): self
    {
        if (is_int($val)) return Long::fromInt($val);
        if (is_string($val)) return Long::fromString($val);
        if ($val::class === 'Keyndin\Crc64\Long') return $val;
        throw new InvalidArgumentException(sprintf(
            'Unsupported Datatype conversion, expecting value to be of 
            either `string`, `int` or `Keyndin\Crc64\Long`, got %s',
            $val::class));
    }

    /**
     * Get string representation of Long
     *
     * @return string
     */
    public function __toString()
    {
        return strval($this->toInt());
    }
}