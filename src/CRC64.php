<?php

namespace Keyndin\Crc64;

use JetBrains\PhpStorm\Pure;

/**
 * Crc64 implementation in PHP
 */
class CRC64
{
    /** @var Format */
    private Format $format = Format::HEX;
    /** @var Polynomial */
    private Polynomial $polynomial = Polynomial::ISO;
    /** @var Long[][] */
    private ?array $table = null;
    /** @var ?Long */
    private ?Long $value = null;
    /** @var ?int[] */
    private ?array $bytes = null;
    /** @var bool */
    private bool $invertIn = true;
    /** @var bool */
    private bool $invertOut = true;

    /**
     * @param Long $value
     * @param array $bytes
     */
    private function __construct(Long $value, array $bytes = [])
    {
        $this->value = $value;
        $this->bytes = $bytes;
    }

    /**
     * @param Polynomial $polynomial
     * @return self
     */
    public function setPolynomial(Polynomial $polynomial): self
    {
        $this->polynomial = $polynomial;
        return $this;
    }

    /**
     * @param Format $format
     * @return self
     */
    public function setFormat(Format $format): self
    {
        $this->format = $format;
        return $this;
    }

    /**
     * Create nested table as described by Mark Adler:
     * http://stackoverflow.com/a/20579405/58962
     * @return void
     */
    private function generateTable(): void
    {
        $this->table = [];
        $poly = $this->polynomial->toInt();

        for ($n = 0; $n < 256; $n++) {
            $crc = Long::fromInt($n);
            for ($k = 0; $k < 8; $k++) {
                if ($crc->and(1)->equals(1)) {
                    $crc = $crc->rshift(1)->xor($poly);
                } else {
                    $crc = $crc->rshift(1);
                }
            }
            $this->table[0][$n] = $crc;
        }

        for ($n = 0; $n < 256; $n++) {
            $crc = $this->table[0][$n];
            for ($k = 1; $k < 8; $k++) {
                $crc = $this->table[0][$crc->and(0xff)->toInt()]->xor($crc->rshift(8));
                $this->table[$k][$n] = $crc;
            }
        }
    }

    /**
     * TODO: rework this!
     *
     * @return $this
     */
    public function convert(): self
    {
        if ($this->table === null) $this->generateTable();
        if ($this->invertIn) $this->value = $this->value->invert();

        $idx = 1;
        $len = sizeof($this->bytes);
        while ($len >= 8) {
            $this->value = $this->table[7][$this->value->and(0xff)->xor($this->bytes[$idx] & 0xff)->toInt()]
                ->xor($this->table[6][($this->value->rshift(8)->and(0xff)->xor(($this->bytes[$idx + 1] & 0xff))->toInt())])
                ->xor($this->table[5][($this->value->rshift(16)->and(0xff)->xor(($this->bytes[$idx + 2] & 0xff))->toInt())])
                ->xor($this->table[4][($this->value->rshift(24)->and(0xff)->xor(($this->bytes[$idx + 3] & 0xff))->toInt())])
                ->xor($this->table[3][($this->value->rshift(32)->and(0xff)->xor(($this->bytes[$idx + 4] & 0xff))->toInt())])
                ->xor($this->table[2][($this->value->rshift(40)->and(0xff)->xor(($this->bytes[$idx + 5] & 0xff))->toInt())])
                ->xor($this->table[1][($this->value->rshift(48)->and(0xff)->xor(($this->bytes[$idx + 6] & 0xff))->toInt())])
                ->xor($this->table[0][($this->value->rshift(56)->and(0xff)->xor(($this->bytes[$idx + 7] & 0xff))->toInt())]);
            $idx += 8;
            $len -= 8;
        }

        while ($len > 0) {
            $x = $this->value->toInt();
            $off = $this->value->xor($this->bytes[$idx])->and(0xff)->toInt();
            $this->value = $this->table[0][$off]->xor($this->value->rshift(8));
            $idx++;
            $len--;
        }
        if ($this->invertOut) $this->value = $this->value->invert();
        return $this;
    }

    #[Pure] public static function fromString(string $value): self
    {
        /** @var int[] $bytes */
        $bytes = unpack('C*', $value);
        $val = Long::fromInt(0);
        for ($i = 1; $i <= 4; $i++) {
            $val->lshift(8)->xor($bytes[$i] & 0xFF);
        }
        return new static($val, $bytes);
    }

    public function __toString(): string
    {
        return sprintf($this->format->value, $this->value->toInt());
    }
}