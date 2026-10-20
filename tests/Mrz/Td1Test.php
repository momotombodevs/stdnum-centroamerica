<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Tests\Mrz;

use StdNumCentroamerica\Contracts\DocumentInterface;
use StdNumCentroamerica\Documents\Mrz\Td1;
use StdNumCentroamerica\Tests\DocumentTestCase;

class Td1Test extends DocumentTestCase
{
    private const SPECIMEN = "I<UTOD231458907<<<<<<<<<<<<<<<\n7408122F1204159UTO<<<<<<<<<<<6\nERIKSSON<<ANNA<MARIA<<<<<<<<<<";

    protected function document(): DocumentInterface
    {
        return new Td1();
    }

    public static function validos(): array
    {
        return [
            // Ejemplo de la especificación ICAO 9303 (Utopía)
            'ICAO, con saltos de línea' => [self::SPECIMEN],
            'ICAO, en una línea' => [str_replace("\n", '', self::SPECIMEN)],
            'tarjeta pasaporte (tipo P)' => ["P<UTOD231458907<<<<<<<<<<<<<<<\n7408122F1204159UTO<<<<<<<<<<<6\nERIKSSON<<ANNA<MARIA<<<<<<<<<<"],
        ];
    }

    public static function invalidos(): array
    {
        return [
            'vacío' => [''],
            'líneas incompletas' => [substr(self::SPECIMEN, 0, 40)],
            'dígito del número' => ["I<UTOD231458908<<<<<<<<<<<<<<<\n7408122F1204159UTO<<<<<<<<<<<6\nERIKSSON<<ANNA<MARIA<<<<<<<<<<"],
            'dígito de nacimiento' => ["I<UTOD231458907<<<<<<<<<<<<<<<\n7408123F1204159UTO<<<<<<<<<<<6\nERIKSSON<<ANNA<MARIA<<<<<<<<<<"],
            'dígito de vencimiento' => ["I<UTOD231458907<<<<<<<<<<<<<<<\n7408122F1204158UTO<<<<<<<<<<<6\nERIKSSON<<ANNA<MARIA<<<<<<<<<<"],
            'dígito compuesto' => ["I<UTOD231458907<<<<<<<<<<<<<<<\n7408122F1204159UTO<<<<<<<<<<<7\nERIKSSON<<ANNA<MARIA<<<<<<<<<<"],
            'sexo inválido' => ["I<UTOD231458907<<<<<<<<<<<<<<<\n7408122Q1204159UTO<<<<<<<<<<<6\nERIKSSON<<ANNA<MARIA<<<<<<<<<<"],
            'mes de nacimiento inválido' => ["I<UTOD231458907<<<<<<<<<<<<<<<\n7438122F1204159UTO<<<<<<<<<<<6\nERIKSSON<<ANNA<MARIA<<<<<<<<<<"],
            'tipo de documento inválido' => ["Q<UTOD231458907<<<<<<<<<<<<<<<\n7408122F1204159UTO<<<<<<<<<<<6\nERIKSSON<<ANNA<MARIA<<<<<<<<<<"],
        ];
    }

    public static function formatos(): array
    {
        return [
            str_replace("\n", '', self::SPECIMEN) => self::SPECIMEN,
        ];
    }
}
