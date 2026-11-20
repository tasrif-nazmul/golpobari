<?php

namespace Tests\Unit;

use App\Support\Bn;
use App\Support\Phone;
use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
{
    public function test_phone_numbers_are_normalised_to_local_format(): void
    {
        $this->assertSame('01811223344', Phone::normalize('+880 1811-223344'));
        $this->assertSame('01811223344', Phone::normalize('8801811223344'));
        $this->assertSame('01811223344', Phone::normalize('০১৮১১২২৩৩৪৪'));
        $this->assertMatchesRegularExpression(Phone::BD_MOBILE, Phone::normalize('01711-000000'));
        $this->assertDoesNotMatchRegularExpression(Phone::BD_MOBILE, Phone::normalize('01211000000'));
    }

    public function test_bangla_digits(): void
    {
        $this->assertSame('২০২৬', Bn::digits(2026));
    }
}
