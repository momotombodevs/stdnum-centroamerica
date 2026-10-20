<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StdNumCentroamerica\Registry;
use StdNumCentroamerica\ValidationError;

class ValidationResultTest extends TestCase
{
    /** @return array<string, array{string, string, ValidationError}> */
    public static function errores(): array
    {
        return [
            'GT NIT, longitud' => ['gt.nit', '', ValidationError::Format],
            'GT NIT, verificador' => ['gt.nit', '1234567-0', ValidationError::Checksum],
            'GT DPI, caracteres' => ['gt.dpi', '2000 0000 A 0101', ValidationError::Format],
            'GT DPI, departamento' => ['gt.dpi', '2000000000023', ValidationError::Component],
            'SV DUI, verificador' => ['sv.dui', '00000000-1', ValidationError::Checksum],
            'SV NIT, longitud' => ['sv.nit', '12345', ValidationError::Format],
            'SV NIT de 9, verificador' => ['sv.nit', '00000000-1', ValidationError::Checksum],
            'HN identidad, longitud' => ['hn.identidad', '0801-1990', ValidationError::Format],
            'HN identidad, municipio' => ['hn.identidad', '0899199000001', ValidationError::Component],
            'NI cédula, formato' => ['ni.cedula', '001-010190-00001', ValidationError::Format],
            'NI cédula, fecha' => ['ni.cedula', '001-320190-0001N', ValidationError::Component],
            'NI cédula, letra' => ['ni.cedula', '001-010190-0001A', ValidationError::Checksum],
            'NI RUC, prefijo 0000' => ['ni.ruc', '0000300766011M', ValidationError::Component],
            'NI RUC, formato' => ['ni.ruc', 'X', ValidationError::Format],
            'PA cédula, formato' => ['pa.cedula', 'ABC', ValidationError::Format],
            'PA RUC, formato' => ['pa.ruc', '1', ValidationError::Format],
            'CR DIMEX, formato' => ['cr.dimex', '0123', ValidationError::Format],
        ];
    }

    #[DataProvider('errores')]
    public function test_reporta_el_motivo_del_fallo(string $key, string $number, ValidationError $expected): void
    {
        $result = Registry::validate($key, $number);

        $this->assertFalse($result->isValid());
        $this->assertSame($expected, $result->error);
    }

    public function test_registry_validate_acepta_opciones(): void
    {
        $this->assertNull(Registry::validate('ni.cedula', '001-010190-0001A', ['strictLetter' => false])->error);
    }
}
