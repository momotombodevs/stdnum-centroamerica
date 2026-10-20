<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StdNumCentroamerica\Contracts\DocumentInterface;
use StdNumCentroamerica\ValidationError;

abstract class DocumentTestCase extends TestCase
{
    abstract protected function document(): DocumentInterface;

    /** @return array<int|string, array{string}> */
    abstract public static function validos(): array;

    /** @return array<int|string, array{string}> */
    abstract public static function invalidos(): array;

    /** @return array<int|string, string> número de entrada => formato esperado */
    abstract public static function formatos(): array;

    #[DataProvider('validos')]
    public function test_acepta_numeros_validos(string $number): void
    {
        $this->assertTrue($this->document()->isValid($number));
    }

    #[DataProvider('invalidos')]
    public function test_rechaza_numeros_invalidos(string $number): void
    {
        $this->assertFalse($this->document()->isValid($number));
    }

    #[DataProvider('validos')]
    public function test_validate_no_reporta_error_en_validos(string $number): void
    {
        $result = $this->document()->validate($number);

        $this->assertTrue($result->isValid());
        $this->assertNull($result->error);
    }

    #[DataProvider('invalidos')]
    public function test_validate_reporta_un_error_en_invalidos(string $number): void
    {
        $result = $this->document()->validate($number);

        $this->assertFalse($result->isValid());
        $this->assertInstanceOf(ValidationError::class, $result->error);
    }

    public function test_formatea_y_compacta(): void
    {
        $document = $this->document();

        foreach (static::formatos() as $input => $expected) {
            $this->assertSame($expected, $document->format((string) $input));
            $this->assertSame($document->compact((string) $input), $document->compact($expected));
        }
    }
}
