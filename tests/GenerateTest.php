<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StdNumCentroamerica\Contracts\DocumentInterface;
use StdNumCentroamerica\Registry;
use StdNumCentroamerica\ValidationResult;

class GenerateTest extends TestCase
{
    /** @return array<string, array{string}> */
    public static function claves(): array
    {
        $keys = [];
        foreach (Registry::describe() as $info) {
            $keys[$info->key] = [$info->key];
        }

        return $keys;
    }

    #[DataProvider('claves')]
    public function test_los_numeros_generados_son_validos(string $key): void
    {
        $document = Registry::make($key);

        for ($i = 0; $i < 300; $i++) {
            $number = Registry::generate($key);
            $this->assertTrue($document->isValid($number), "{$key} generó {$number}");
        }
    }

    #[DataProvider('claves')]
    public function test_los_numeros_generados_varian(string $key): void
    {
        $numbers = [];
        for ($i = 0; $i < 30; $i++) {
            $numbers[Registry::generate($key)] = true;
        }

        $this->assertGreaterThan(1, count($numbers));
    }

    public function test_documento_sin_generador_lanza_excepcion(): void
    {
        $document = new class () implements DocumentInterface {
            public function isValid(string $number): bool
            {
                return true;
            }

            public function validate(string $number): ValidationResult
            {
                return new ValidationResult();
            }

            public function format(string $number): string
            {
                return $number;
            }

            public function compact(string $number): string
            {
                return $number;
            }
        };
        Registry::register('xx.sin_generador', $document::class);

        $this->expectException(InvalidArgumentException::class);
        Registry::generate('xx.sin_generador');
    }
}
