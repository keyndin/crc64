# CRC64 PHP

PHP implementation of CRC64 to be compatible with 
[this](https://github.com/MrBuddyCasino/crc-64) Java implementation.
All credits go to them, this is basically an exact copy of their behavior
adapted to PHP 8.1.

This package should calculate the exact same hash values as the above-mentioned Java package.

## Requirements

* PHP >= 8.1
* Composer
* PHPUnit >= 9.5.27 (to run unittests)


## Usage

After installing the package via composer you can simply use it like this:

```php
use Keyndin\Crc64\CRC64;
use Keyndin\Crc64\Format;
use Keyndin\Crc64\Polynomial;

$crc = CRC64::fromString("hashThisValue")
    ->setPolynomial(Polynomial::ISO)
    ->setFormat(Format::HEX_0)
    ->convert();

echo $crc;
// prints `0xf6dc92b5c5b4c6d1`
```