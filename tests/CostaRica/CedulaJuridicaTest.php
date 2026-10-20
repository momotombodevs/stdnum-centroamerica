<?php

namespace StdNumCentroamerica\Tests\CostaRica;

use StdNumCentroamerica\Contracts\DocumentInterface;
use StdNumCentroamerica\Documents\CostaRica\CedulaJuridica;
use StdNumCentroamerica\Tests\DocumentTestCase;

class CedulaJuridicaTest extends DocumentTestCase
{
    protected function document(): DocumentInterface
    {
        return new CedulaJuridica();
    }

    public static function validos(): array
    {
        return [['3-101-123456'], ['3101123456'], 'sin regla de cero inicial' => ['0-101-123456']];
    }

    public static function invalidos(): array
    {
        return [['3-101-12345'], ['3-101-1234567'], ['3-101-12345A']];
    }

    public static function formatos(): array
    {
        return [
            '3101123456' => '3-101-123456',
        ];
    }
}
