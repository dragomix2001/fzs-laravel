<?php

declare(strict_types=1);

namespace Tests\Unit\Coverage;

use App\DTOs\Concerns\NormalizesRequestValues;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NormalizesRequestValuesTraitTest extends TestCase
{
    #[Test]
    public function all_trait_normalizers_are_covered(): void
    {
        $helper = new class
        {
            use NormalizesRequestValues;

            public function nullableIntPublic(mixed $value): ?int
            {
                return self::nullableInt($value);
            }

            public function nullableFloatPublic(mixed $value): ?float
            {
                return self::nullableFloat($value);
            }

            public function nullableStringPublic(mixed $value): ?string
            {
                return self::nullableString($value);
            }

            public function nullableDatePublic(mixed $value): ?\DateTimeInterface
            {
                return self::nullableDate($value);
            }

            public function normalizeArrayPublic(mixed $value): array
            {
                return self::normalizeArray($value);
            }
        };

        $this->assertNull($helper->nullableIntPublic(''));
        $this->assertSame(10, $helper->nullableIntPublic('10'));

        $this->assertNull($helper->nullableFloatPublic(null));
        $this->assertSame(12.5, $helper->nullableFloatPublic('12,5'));

        $this->assertNull($helper->nullableStringPublic(''));
        $this->assertSame('abc', $helper->nullableStringPublic('abc'));

        $this->assertNull($helper->nullableDatePublic(''));
        $this->assertSame('2026-05-01', $helper->nullableDatePublic('01.05.2026.')?->format('Y-m-d'));

        $this->assertSame([], $helper->normalizeArrayPublic('not-array'));
        $this->assertSame([1, 2], $helper->normalizeArrayPublic([1, null, '', 2]));
    }
}
