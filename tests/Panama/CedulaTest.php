<?php

namespace StdNumCentroamerica\Tests\Panama;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StdNumCentroamerica\Documents\Panama\Cedula;

class CedulaTest extends TestCase
{
    #[DataProvider('validos')]
    public function test_acepta_numeros_validos(string $number): void
    {
        $this->assertTrue((new Cedula())->isValid($number));
    }

    #[DataProvider('invalidos')]
    public function test_rechaza_numeros_invalidos(string $number): void
    {
        $this->assertFalse((new Cedula())->isValid($number));
    }

    public function test_formatea_con_guiones_y_compacta_sin_ellos(): void
    {
        $doc = new Cedula();

        $this->assertSame('8-926-1601', $doc->format(' 8 926  1601 '));
        $this->assertSame('PE-5-687', $doc->format('pe-5-687'));
        $this->assertSame('8-926-1601', $doc->format('8--926-1601'));
        // compact() quita los separadores, como en el resto del paquete (pierde tomo/asiento).
        $this->assertSame('89261601', $doc->compact('8-926-1601'));
    }

    public static function validos(): array
    {
        return [
            // Ejemplos del Decreto 53 de 1958 según la página del Tribunal Electoral
            'nacimiento' => ['8-926-1601'],
            'nacido en el extranjero' => ['PE-5-687'],
            'naturalizado' => ['N-19-473'],
            'extranjero domiciliado' => ['E-8-12345'],
            'provincia 1' => ['1-1-1'],
            'provincia 9' => ['9-100-12345'],
            'minúsculas y espacios' => ['pe 5 687'],
            // No oficial: claves 10-13 y sufijos PI / AV
            'comarca' => ['12-12-345'],
            'indígena' => ['3-PI-12-345'],
            'anterior al registro civil' => ['8-AV-12-345'],
        ];
    }

    public static function invalidos(): array
    {
        return [
            'provincia 0' => ['0-926-1601'],
            'provincia 14' => ['14-926-1601'],
            'sin asiento' => ['8-926'],
            'letras en tomo' => ['8-A26-1601'],
            'prefijo desconocido' => ['XX-5-687'],
            'PE con provincia' => ['PE-8-5-687'],
            'N sin asiento' => ['N-19'],
            'sin separadores' => ['89261601'],
            'vacío' => [''],
        ];
    }
}
