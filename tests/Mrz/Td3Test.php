<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Tests\Mrz;

use StdNumCentroamerica\Contracts\DocumentInterface;
use StdNumCentroamerica\Documents\Mrz\Td3;
use StdNumCentroamerica\Tests\DocumentTestCase;

class Td3Test extends DocumentTestCase
{
    private const SPECIMEN = "P<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<<<<<<<<<\nL898902C36UTO7408122F1204159ZE184226B<<<<<10";

    protected function document(): DocumentInterface
    {
        return new Td3();
    }

    public static function validos(): array
    {
        return [
            // Ejemplo de la especificación ICAO 9303 (Utopía)
            'ICAO, con saltos de línea' => [self::SPECIMEN],
            'ICAO, en una línea' => [str_replace("\n", '', self::SPECIMEN)],
            'ICAO, en minúsculas' => [strtolower(self::SPECIMEN)],
            'sexo no especificado' => ["P<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<<<<<<<<<\nL898902C36UTO7408122<1204159ZE184226B<<<<<10"],
        ];
    }

    public static function invalidos(): array
    {
        return [
            'vacío' => [''],
            'líneas incompletas' => [substr(self::SPECIMEN, 0, 40)],
            'dígito del número' => ["P<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<<<<<<<<<\nL898902C37UTO7408122F1204159ZE184226B<<<<<10"],
            'dígito de nacimiento' => ["P<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<<<<<<<<<\nL898902C36UTO7408123F1204159ZE184226B<<<<<10"],
            'dígito de vencimiento' => ["P<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<<<<<<<<<\nL898902C36UTO7408122F1204158ZE184226B<<<<<10"],
            'dígito de datos opcionales' => ["P<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<<<<<<<<<\nL898902C36UTO7408122F1204159ZE184226B<<<<<20"],
            'dígito compuesto' => ["P<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<<<<<<<<<\nL898902C36UTO7408122F1204159ZE184226B<<<<<11"],
            'sexo inválido' => ["P<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<<<<<<<<<\nL898902C36UTO7408122Q1204159ZE184226B<<<<<10"],
            'tipo de documento inválido' => ["I<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<<<<<<<<<\nL898902C36UTO7408122F1204159ZE184226B<<<<<10"],
            'mes de nacimiento inválido' => ["P<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<<<<<<<<<\nL898902C36UTO7438122F1204159ZE184226B<<<<<10"],
        ];
    }

    public static function formatos(): array
    {
        return [
            str_replace("\n", '', self::SPECIMEN) => self::SPECIMEN,
        ];
    }
}
