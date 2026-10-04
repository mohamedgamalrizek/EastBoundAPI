<?php

namespace Tests\Unit;

use App\Services\Contact\PhoneNumber;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PhoneNumberTest extends TestCase
{
    #[DataProvider('shapes')]
    public function test_every_shape_normalises_to_one_value(string $input, string $expected): void
    {
        $this->assertSame($expected, PhoneNumber::e164($input, '880'));
    }

    public static function shapes(): array
    {
        return [
            'international with plus' => ['+8801711000111', '+8801711000111'],
            'spaced and dashed'       => ['+880 1711-000 111', '+8801711000111'],
            'dialled with 00'         => ['008801711000111', '+8801711000111'],
            'national with trunk 0'   => ['01711000111', '+8801711000111'],
            'country code, no plus'   => ['8801711000111', '+8801711000111'],
            'bare subscriber number'  => ['1711000111', '+8801711000111'],
            'parenthesised'           => ['(01711) 000111', '+8801711000111'],
        ];
    }

    public function test_a_different_country_is_respected(): void
    {
        $this->assertSame('+919876543210', PhoneNumber::e164('09876543210', '91'));
        // An explicit + always wins over the default country.
        $this->assertSame('+441234567890', PhoneNumber::e164('+441234567890', '880'));
    }

    public function test_blank_stays_blank(): void
    {
        $this->assertSame('', PhoneNumber::e164(null, '880'));
        $this->assertSame('', PhoneNumber::e164('   ', '880'));
        $this->assertSame('', PhoneNumber::e164('---', '880'));
    }

    public function test_lookup_matches_the_legacy_shapes_too(): void
    {
        // Rows written before normalisation must still be findable, or an
        // upgrade would lock existing customers out.
        $variants = PhoneNumber::lookupVariants('+8801711000111', '880');

        foreach (['+8801711000111', '8801711000111', '01711000111', '1711000111'] as $stored) {
            $this->assertContains($stored, $variants, "Should still find a row stored as {$stored}.");
        }
    }
}
