<?php

namespace Keyndin\Crc64;

use GMP;
use JetBrains\PhpStorm\Pure;

/**
 * Crc64 implementation in PHP
 */
class CRC64
{
    /** @var int[] */
    private array $bytes;
    /** @var Format */
    private Format $format = Format::HEX;
    /** @var Polynomial */
    private Polynomial $polynomial = Polynomial::ISO;
    /** @var int[] */
    private ?array $table = null;
    /** @var ?GMP */
    private ?GMP $value = null;

    /**
     * @param int[] $bytes
     */
    private function __construct(array $bytes)
    {
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
        $poly = $this->polynomial->toInt();

        for ($i = 0; $i < 256; $i++)
        {
            for ($part = gmp_init($i), $bit = 0; $bit < 8; $bit++) {
                if (($part & 1) == "1") {
                    $part = gmp_xor((($part >> 1) & ~(0x8 << 60)), $poly);
                } else {
                    $part = ($part >> 1) & ~(0x8 << 60);
                }
            }

            $this->table[$i] = $part;
        }
    }

    public function convert(): self
    {
        if ($this->table === null) $this->generateTable();

        $this->value = gmp_init(0);

        for ($i = 1; $i <= sizeof($this->bytes); $i++) {
            $off = gmp_intval(($this->value ^ $this->bytes[$i]) & 0xff);
            $this->value = gmp_init($this->table[$off] ^ (($this->value >> 8) & ~(0xff << 56)));
        }


        return $this;
    }

    #[Pure] public static function fromString(string $value): self
    {
        return new static(unpack('C*', $value));
    }

    public function __toString(): string
    {
        return sprintf($this->format->value, $this->value);
    }
}