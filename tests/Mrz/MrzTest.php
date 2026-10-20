<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Tests\Mrz;

use PHPUnit\Framework\TestCase;
use StdNumCentroamerica\Documents\Mrz\AbstractMrz;
use StdNumCentroamerica\Documents\Mrz\Td1;
use StdNumCentroamerica\Documents\Mrz\Td2;
use StdNumCentroamerica\Documents\Mrz\Td3;
use StdNumCentroamerica\ValidationError;

class MrzTest extends TestCase
{
    private const TD3 = "P<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<<<<<<<<<\nL898902C36UTO7408122F1204159ZE184226B<<<<<10";
    private const TD1 = "I<UTOD231458907<<<<<<<<<<<<<<<\n7408122F1204159UTO<<<<<<<<<<<6\nERIKSSON<<ANNA<MARIA<<<<<<<<<<";
    private const TD2 = "I<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<\nD231458907UTO7408122F1204159<<<<<<<6";

    public function test_el_digito_de_control_sigue_la_especificacion(): void
    {
        // Ejemplo de ICAO 9303: L898902C3 -> 6
        $this->assertSame(6, AbstractMrz::checkDigit('L898902C3'));
        $this->assertSame(2, AbstractMrz::checkDigit('740812'));
        $this->assertSame(0, AbstractMrz::checkDigit('<<<<<<'));
    }

    public function test_parse_del_pasaporte(): void
    {
        $data = (new Td3())->parse(self::TD3);

        $this->assertNotNull($data);
        $this->assertSame('P', $data->documentCode);
        $this->assertSame('UTO', $data->issuingState);
        $this->assertSame('ERIKSSON', $data->surname);
        $this->assertSame('ANNA MARIA', $data->givenNames);
        $this->assertSame('L898902C3', $data->documentNumber);
        $this->assertSame('UTO', $data->nationality);
        $this->assertSame('740812', $data->birthDate);
        $this->assertSame('F', $data->sex);
        $this->assertSame('120415', $data->expiryDate);
        $this->assertSame('ZE184226B', $data->optionalData);
    }

    public function test_parse_de_las_cedulas_td1_y_td2(): void
    {
        $td1 = (new Td1())->parse(self::TD1);
        $this->assertSame('D23145890', $td1?->documentNumber);
        $this->assertSame('ERIKSSON', $td1->surname);
        $this->assertSame('ANNA MARIA', $td1->givenNames);
        $this->assertSame('120415', $td1->expiryDate);

        $td2 = (new Td2())->parse(self::TD2);
        $this->assertSame('D23145890', $td2?->documentNumber);
        $this->assertSame('ERIKSSON', $td2->surname);
        $this->assertSame('740812', $td2->birthDate);
    }

    public function test_parse_de_una_mrz_invalida_devuelve_null(): void
    {
        $this->assertNull((new Td3())->parse(str_replace('C36', 'C37', self::TD3)));
        $this->assertNull((new Td3())->parse(''));
    }

    public function test_motivos_de_error(): void
    {
        $td3 = new Td3();

        $this->assertSame(ValidationError::Format, $td3->validate('P<UTO')->error);
        $this->assertSame(ValidationError::Checksum, $td3->validate(str_replace('C36', 'C37', self::TD3))->error);
        $this->assertSame(ValidationError::Component, $td3->validate(str_replace('F1204', 'Q1204', self::TD3))->error);
        // El sexo no entra en el compuesto: el cambio solo lo detecta la regla de componentes.
        $this->assertSame(ValidationError::Component, $td3->validate(str_replace('UTO7408122F', 'UTO7413122F', self::TD3))->error);
    }

    public function test_fecha_desconocida_se_acepta_con_digito_en_blanco_o_cero(): void
    {
        foreach (['<', '0'] as $check) {
            $l1 = 'P<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<<<<<<<<<';
            $l2 = 'L898902C36UTO' . '<<<<<<' . $check . 'F' . '120415' . '9' . 'ZE184226B<<<<<' . '1';
            $composite = AbstractMrz::checkDigit(substr($l2, 0, 10) . substr($l2, 13, 7) . substr($l2, 21, 22));
            $mrz = $l1 . $l2 . $composite;

            $this->assertTrue((new Td3())->isValid($mrz), $check);
            $this->assertNull((new Td3())->parse($mrz)?->birthDate);
        }
    }

    public function test_una_fecha_desconocida_con_otro_digito_es_un_error_de_componente(): void
    {
        $l2 = 'L898902C36UTO' . '<<<<<<' . '5' . 'F' . '120415' . '9' . 'ZE184226B<<<<<' . '1';
        $mrz = 'P<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<<<<<<<<<' . $l2 . '0';

        $this->assertSame(ValidationError::Component, (new Td3())->validate($mrz)->error);
    }

    public function test_nombre_sin_apellido_es_un_error_de_componente(): void
    {
        $this->assertSame(
            ValidationError::Component,
            (new Td3())->validate(str_replace('ERIKSSON<<ANNA<MARIA<<<<<<<<<<<<<<<<<<<', str_repeat('<', 39), self::TD3))->error,
        );
    }

    /** MRZ TD1 con un número de 12 caracteres cuyo dígito de control es $check. */
    private static function longTd1(string $check): string
    {
        $number = 'ABC123456789';
        $l1 = str_pad('I<UTO' . substr($number, 0, 9) . '<' . substr($number, 9) . $check, 30, '<');
        $l2 = '7408122F1204159UTO' . str_repeat('<', 11);
        $composite = AbstractMrz::checkDigit(substr($l1, 5, 25) . substr($l2, 0, 7) . substr($l2, 8, 7) . substr($l2, 18, 11));

        return $l1 . $l2 . $composite . str_pad('ERIKSSON<<ANNA', 30, '<');
    }

    public function test_numero_de_documento_de_mas_de_nueve_caracteres_en_td1(): void
    {
        $mrz = self::longTd1((string) AbstractMrz::checkDigit('ABC123456789'));

        $td1 = new Td1();
        $this->assertTrue($td1->isValid($mrz));
        $data = $td1->parse($mrz);
        $this->assertSame('ABC123456789', $data?->documentNumber);
        $this->assertSame('', $data->optionalData); // la continuación no se repite en los datos opcionales

        // El compuesto cubre la continuación: alterarla se detecta.
        $this->assertFalse($td1->isValid(substr_replace($mrz, '0', 16, 1)));
    }

    public function test_el_digito_del_numero_largo_se_verifica_aunque_el_compuesto_sea_coherente(): void
    {
        $right = AbstractMrz::checkDigit('ABC123456789');
        $wrong = (string) (($right + 1) % 10);

        $this->assertSame(ValidationError::Checksum, (new Td1())->validate(self::longTd1($wrong))->error);
    }

    public function test_un_numero_de_nueve_caracteres_no_puede_omitir_su_digito(): void
    {
        $l1 = 'I<UTOD23145890<<<<<<<<<<<<<<<<';
        $l2 = '7408122F1204159UTO<<<<<<<<<<<';
        $composite = AbstractMrz::checkDigit(substr($l1, 5, 25) . substr($l2, 0, 7) . substr($l2, 8, 7) . substr($l2, 18, 11));

        $this->assertSame(
            ValidationError::Checksum,
            (new Td1())->validate($l1 . $l2 . $composite . str_pad('ERIKSSON<<ANNA', 30, '<'))->error,
        );
    }

    public function test_numero_de_documento_de_mas_de_nueve_caracteres_en_td2(): void
    {
        $number = 'ABC123456789';
        $l1 = str_pad('I<UTOERIKSSON<<ANNA', 36, '<');
        $l2 = 'ABC123456' . '<' . 'UTO' . '740812' . '2' . 'F' . '120415' . '9'
            . '789' . AbstractMrz::checkDigit($number) . '<<<';
        $composite = AbstractMrz::checkDigit(substr($l2, 0, 10) . substr($l2, 13, 7) . substr($l2, 21, 14));
        $mrz = $l1 . $l2 . $composite;

        $this->assertTrue((new Td2())->isValid($mrz));
        $this->assertSame($number, (new Td2())->parse($mrz)?->documentNumber);
    }

    public function test_format_reparte_las_lineas(): void
    {
        $this->assertSame(self::TD1, (new Td1())->format(str_replace("\n", '', self::TD1)));
        $this->assertSame(self::TD2, (new Td2())->format(self::TD2));
    }
}
