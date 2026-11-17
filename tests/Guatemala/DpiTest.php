<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Tests\Guatemala;

use PHPUnit\Framework\Attributes\DataProvider;
use StdNumCentroamerica\Contracts\DocumentInterface;
use StdNumCentroamerica\Data\GuatemalaGeografia;
use StdNumCentroamerica\Documents\Guatemala\Dpi;
use StdNumCentroamerica\Tests\DocumentTestCase;
use StdNumCentroamerica\ValidationError;

class DpiTest extends DocumentTestCase
{
    protected function document(): DocumentInterface
    {
        return new Dpi();
    }

    public static function validos(): array
    {
        return [
            ['1234567890101'],
            ['1234 56789 0101'],
            'último de El Progreso' => ['1234567890208'],
            'Petatán, Huehuetenango' => ['1234567891333'],
            'último de Jutiapa' => ['1234567892217'],
        ];
    }

    public static function invalidos(): array
    {
        return [['1234567880101'], ['1234567890001'], ['1234567892301'], ['123']];
    }

    /** @return array<string, array{string}> */
    public static function municipiosInexistentes(): array
    {
        return [
            'municipio 00' => ['1234567890100'],
            'El Progreso tiene 8' => ['1234567890209'],
            'Huehuetenango tiene 33' => ['1234567891334'],
            'Izabal tiene 5' => ['1234567891806'],
            'Jutiapa tiene 17' => ['1234567892218'],
            'municipio 99' => ['1234567890199'],
            'departamento 00' => ['1234567890001'],
            'departamento 23' => ['1234567892301'],
        ];
    }

    #[DataProvider('municipiosInexistentes')]
    public function test_rechaza_municipios_que_no_existen_en_su_departamento(string $number): void
    {
        $this->assertSame(ValidationError::Component, $this->document()->validate($number)->error);
    }

    public function test_sin_strict_municipality_acepta_cualquier_municipio_del_departamento(): void
    {
        $lenient = new Dpi(strictMunicipality: false);

        foreach (['1234567890209', '1234567891334', '1234567890199', '1234567892218'] as $number) {
            $this->assertTrue($lenient->isValid($number), $number);
        }
        foreach (['1234567890100', '1234567890001', '1234567892301', '1234567889999'] as $number) {
            $this->assertSame(ValidationError::Component, $lenient->validate($number)->error, $number);
        }
        $this->assertSame(ValidationError::Checksum, $lenient->validate('1234567880199')->error);
    }

    public function test_la_tabla_cubre_22_departamentos_y_340_municipios(): void
    {
        $this->assertSame(range(1, 22), array_keys(GuatemalaGeografia::MUNICIPIOS_POR_DEPARTAMENTO));
        $this->assertSame(340, array_sum(GuatemalaGeografia::MUNICIPIOS_POR_DEPARTAMENTO));
    }

    public function test_genera_solo_municipios_que_existen(): void
    {
        $document = new Dpi();
        $departments = [];

        for ($i = 0; $i < 2000; $i++) {
            $data = $document->parse($document->generate());
            $this->assertNotNull($data);
            $this->assertLessThanOrEqual(
                GuatemalaGeografia::MUNICIPIOS_POR_DEPARTAMENTO[$data->departmentCode],
                $data->municipalityCode,
            );
            $departments[$data->departmentCode] = true;
        }

        $this->assertCount(22, $departments);
    }

    public static function formatos(): array
    {
        return [
            '1234567890101' => '1234 56789 0101',
        ];
    }
}
