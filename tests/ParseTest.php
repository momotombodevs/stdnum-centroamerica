<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use StdNumCentroamerica\Contracts\ParsesNumbers;
use StdNumCentroamerica\Documents\Guatemala\DpiData;
use StdNumCentroamerica\Documents\Honduras\IdentidadData;
use StdNumCentroamerica\Documents\Nicaragua\Cedula as NiCedula;
use StdNumCentroamerica\Documents\Nicaragua\CedulaData as NiCedulaData;
use StdNumCentroamerica\Documents\Panama\CedulaData as PaCedulaData;
use StdNumCentroamerica\Documents\Panama\CedulaKind;
use StdNumCentroamerica\Registry;

class ParseTest extends TestCase
{
    public function test_cedula_de_nicaragua(): void
    {
        $data = Registry::parse('ni.cedula', '001-010190-0001n');

        $this->assertInstanceOf(NiCedulaData::class, $data);
        $this->assertSame(1, $data->municipalityCode);
        $this->assertSame(1, $data->birthDay);
        $this->assertSame(1, $data->birthMonth);
        $this->assertSame(90, $data->birthYearTwoDigits);
        $this->assertSame('0001', $data->sequence);
        $this->assertSame('N', $data->letter);
        $this->assertFalse($data->isBornAbroad());
    }

    public function test_el_siglo_de_la_cedula_se_ofrece_como_candidatos(): void
    {
        $data = Registry::parse('ni.cedula', '001-010190-0001N');
        $this->assertInstanceOf(NiCedulaData::class, $data);
        // 2090 aún no ocurre
        $this->assertSame([1990], $data->birthYearCandidates('20261020'));
        $this->assertSame([1990, 2090], $data->birthYearCandidates('21001231'));

        $recent = Registry::parse('ni.cedula', '001-150620-0001A', ['strictLetter' => false]);
        $this->assertInstanceOf(NiCedulaData::class, $recent);
        $this->assertSame([1920, 2020], $recent->birthYearCandidates('20261020'));
    }

    public function test_un_candidato_con_fecha_futura_del_mismo_ano_se_descarta(): void
    {
        $recent = Registry::parse('ni.cedula', '001-150620-0001A', ['strictLetter' => false]);

        $this->assertInstanceOf(NiCedulaData::class, $recent);
        $this->assertSame([1920], $recent->birthYearCandidates('20200101')); // 15/06/2020 aún no llega
        $this->assertSame([1920, 2020], $recent->birthYearCandidates('20200615'));
    }

    public function test_los_candidatos_pueden_quedar_vacios(): void
    {
        // El 29/02/01 no existe ni en 1901 ni en 2001
        $data = Registry::parse('ni.cedula', '001-290201-0001A', ['strictLetter' => false]);

        $this->assertInstanceOf(NiCedulaData::class, $data);
        $this->assertSame([], $data->birthYearCandidates('20261020'));
    }

    public function test_la_cedula_respeta_strict_municipality(): void
    {
        $options = ['strictLetter' => false, 'strictMunicipality' => true];

        $this->assertNotNull(Registry::parse('ni.cedula', '282-010190-0001A', ['strictLetter' => false]));
        $this->assertNull(Registry::parse('ni.cedula', '282-010190-0001A', $options));
    }

    public function test_el_29_de_febrero_descarta_los_anos_no_bisiestos(): void
    {
        $data = Registry::parse('ni.cedula', '001-290200-0001A', ['strictLetter' => false]);

        $this->assertInstanceOf(NiCedulaData::class, $data);
        $this->assertSame([2000], $data->birthYearCandidates('20261020')); // 1900 no fue bisiesto
    }

    public function test_cedula_de_nicaragua_de_nacido_en_el_extranjero(): void
    {
        $digits = '8880101900001';
        $data = Registry::parse('ni.cedula', $digits . NiCedula::checkLetter($digits));

        $this->assertInstanceOf(NiCedulaData::class, $data);
        $this->assertSame(888, $data->municipalityCode);
        $this->assertTrue($data->isBornAbroad());
    }

    public function test_identidad_de_honduras(): void
    {
        $data = Registry::parse('hn.identidad', '0906-1990-00001');

        $this->assertInstanceOf(IdentidadData::class, $data);
        $this->assertSame(9, $data->departmentCode);
        $this->assertSame(6, $data->municipalityCode);
        $this->assertSame(1990, $data->registrationYear);
        $this->assertSame('00001', $data->sequence);
        $this->assertNull($data->specialCode);
    }

    public function test_identidad_de_honduras_con_codigo_especial(): void
    {
        $data = Registry::parse('hn.identidad', '0890-2010-00012');

        $this->assertInstanceOf(IdentidadData::class, $data);
        $this->assertSame('0890', $data->specialCode);
        $this->assertNull($data->departmentCode);
        $this->assertNull($data->municipalityCode);
        $this->assertSame(2010, $data->registrationYear);
    }

    public function test_dpi_de_guatemala(): void
    {
        $data = Registry::parse('gt.dpi', '1234 56789 0101');

        $this->assertInstanceOf(DpiData::class, $data);
        $this->assertSame('12345678', $data->serial);
        $this->assertSame(1, $data->departmentCode);
        $this->assertSame(1, $data->municipalityCode);
    }

    public function test_cedula_de_panama(): void
    {
        $birth = Registry::parse('pa.cedula', '8-926-1601');
        $this->assertEquals(new PaCedulaData(CedulaKind::Birth, 8, null, '926', '1601'), $birth);

        $abroad = Registry::parse('pa.cedula', 'pe 5 687');
        $this->assertEquals(new PaCedulaData(CedulaKind::BornAbroad, null, null, '5', '687'), $abroad);

        $naturalized = Registry::parse('pa.cedula', 'N-19-473');
        $this->assertEquals(new PaCedulaData(CedulaKind::Naturalized, null, null, '19', '473'), $naturalized);

        $resident = Registry::parse('pa.cedula', 'E-8-12345');
        $this->assertEquals(new PaCedulaData(CedulaKind::ForeignResident, 8, null, null, '12345'), $resident);

        $suffix = Registry::parse('pa.cedula', '3-PI-12-345');
        $this->assertEquals(new PaCedulaData(CedulaKind::Birth, 3, 'PI', '12', '345'), $suffix);
    }

    public function test_un_numero_invalido_devuelve_null(): void
    {
        $this->assertNull(Registry::parse('ni.cedula', '001-010190-0001A'));
        $this->assertNull(Registry::parse('hn.identidad', '0899-1990-00001'));
        $this->assertNull(Registry::parse('gt.dpi', '1234567880101'));
        $this->assertNull(Registry::parse('pa.cedula', 'ABC'));
    }

    public function test_documento_sin_componentes_lanza_excepcion(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Registry::parse('gt.nit', '1234567-9');
    }

    public function test_los_numeros_generados_se_pueden_leer(): void
    {
        foreach (Registry::describe() as $info) {
            if (!Registry::make($info->key) instanceof ParsesNumbers) {
                continue;
            }

            for ($i = 0; $i < 100; $i++) {
                $this->assertNotNull(Registry::parse($info->key, Registry::generate($info->key)), $info->key);
            }
        }
    }
}
