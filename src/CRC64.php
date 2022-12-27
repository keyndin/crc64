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
    /** @var int[] */
    private ?array $table = null;
    /** @var ?Long */
    private ?Long $value = null;
    /** @var ?int[] */
    private ?array $bytes = null;
    private bool $invertIn = true;

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
                if ($crc->and(1)->equals(1) & 1) $crc->rshift(1)->xor($poly);
                else $crc = $crc->rshift(1);
            }
            $this->table[0][$n] = $crc;
        }

        for ($n = 0; $n < 255; $n++) {
            $crc = $this->table[0][$n];
            for ($k = 0; $k < 8; $k++) {
                $off = $crc->and(0xff);
                $crc = $this->table[0][$off->toInt()]->and(($crc->rshift(8)));
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
        if ($this->invertIn) $this->value->invert();

        $idx = 1;
        $len = sizeof($this->bytes);
        while ($len >= 8) {
            $this->value = $this->table[7][$this->value & 0xff ^ ($this->bytes[$idx] & 0xff)]
                ^ $this->table[6][($this->value >> 8) & 0xff ^ ($this->bytes[$idx + 1] & 0xff)]
                ^ $this->table[5][($this->value >> 16) & 0xff ^ ($this->bytes[$idx + 2] & 0xff)]
                ^ $this->table[4][($this->value >> 24) & 0xff ^ ($this->bytes[$idx + 3] & 0xff)]
                ^ $this->table[3][($this->value >> 32) & 0xff ^ ($this->bytes[$idx + 4] & 0xff)]
                ^ $this->table[2][($this->value >> 40) & 0xff ^ ($this->bytes[$idx + 5] & 0xff)]
                ^ $this->table[1][($this->value >> 48) & 0xff ^ ($this->bytes[$idx + 6] & 0xff)]
                ^ $this->table[0][($this->value >> 56) & 0xff ^ ($this->bytes[$idx + 7] & 0xff)];
            $idx += 8;
            $len -= 8;
        }

        while ($len > 0) {
            $off = $this->value->xor($this->bytes[$idx])->and(0xff);
            $this->value = $this->table[0][$this->value->xor($this->bytes[$idx] & 0xff)->toInt()]->xor($this->value->rshift(8));
            $idx++;
            $len--;
        }
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