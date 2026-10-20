<?php

namespace StdNumCentroamerica\Tests\CostaRica;

use StdNumCentroamerica\Contracts\DocumentInterface;
use StdNumCentroamerica\Documents\CostaRica\CedulaFisica;
use StdNumCentroamerica\Tests\DocumentTestCase;

class CedulaFisicaTest extends DocumentTestCase
{
    protected function document(): DocumentInterface
    {
        return new CedulaFisica();
    }

    public static function validos(): array
    {
        return [['1-0123-0456'], ['101230456']];
    }

    public static function invalidos(): array
    {
        return [['0-0123-0456'], ['1-0123-045'], ['A-0123-0456']];
    }

    public static function formatos(): array
    {
        return [
            '101230456' => '1-0123-0456',
        ];
    }
}
