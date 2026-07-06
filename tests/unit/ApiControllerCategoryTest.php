<?php

use App\Controllers\ApiController;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ApiControllerCategoryTest extends TestCase
{
    public static function categoryProvider(): array
    {
        return [
            "AI list becomes one precise category" => [
                "Fiksi, Aksi, Petualangan",
                "Fiksi Petualangan",
            ],
            "generic book wording is removed" => [
                "Buku Cerita Anak Islami",
                "Cerita Anak Islami",
            ],
            "common English output is translated" => [
                "Science Fiction",
                "Fiksi Ilmiah",
            ],
            "concise Indonesian category stays unchanged" => [
                "Biografi",
                "Biografi",
            ],
            "category conjunction is removed" => [
                "Seni dan Budaya",
                "Seni Budaya",
            ],
        ];
    }

    #[DataProvider("categoryProvider")]
    public function testCategoryNormalization(
        string $rawCategory,
        string $expectedCategory
    ): void {
        $reflection = new ReflectionClass(ApiController::class);
        $controller = $reflection->newInstanceWithoutConstructor();
        $method = $reflection->getMethod("normalizeCategory");

        $this->assertSame(
            $expectedCategory,
            $method->invoke($controller, $rawCategory)
        );
    }
}
