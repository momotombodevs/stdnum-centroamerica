<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Tests\Mrz;

use StdNumCentroamerica\Contracts\DocumentInterface;
use StdNumCentroamerica\Documents\Mrz\Td2;
use StdNumCentroamerica\Tests\DocumentTestCase;

class Td2Test extends DocumentTestCase
{
    private const SPECIMEN = "I<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<\nD231458907UTO7408122F1204159<<<<<<<6";

    protected function document(): DocumentInterface
    {
        return new Td2();
    }

    public static function validos(): array
    {
        return [
            // Ejemplo de la especificación ICAO 9303 (Utopía)
            'ICAO, con saltos de línea' => [self::SPECIMEN],
            'ICAO, en una línea' => [str_replace("\n", '', self::SPECIMEN)],
        ];
    }

    public static function invalidos(): array
    {
        return [
            'vacío' => [''],
            'líneas incompletas' => [substr(self::SPECIMEN, 0, 40)],
            'dígito del número' => ["I<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<\nD231458908UTO7408122F1204159<<<<<<<6"],
            'dígito de nacimiento' => ["I<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<\nD231458907UTO7408123F1204159<<<<<<<6"],
            'dígito de vencimiento' => ["I<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<\nD231458907UTO7408122F1204158<<<<<<<6"],
            'dígito compuesto' => ["I<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<\nD231458907UTO7408122F1204159<<<<<<<7"],
            'sexo inválido' => ["I<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<\nD231458907UTO7408122Q1204159<<<<<<<6"],
            'tipo de documento inválido' => ["P<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<\nD231458907UTO7408122F1204159<<<<<<<6"],
        ];
    }

    public static function formatos(): array
    {
        return [
            str_replace("\n", '', self::SPECIMEN) => self::SPECIMEN,
        ];
    }
}
