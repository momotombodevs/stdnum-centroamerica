<?php

namespace StdNumCentroamerica\Tests\ElSalvador;

use StdNumCentroamerica\Contracts\DocumentInterface;
use StdNumCentroamerica\Documents\ElSalvador\Dui;
use StdNumCentroamerica\Tests\DocumentTestCase;

class DuiTest extends DocumentTestCase
{
    protected function document(): DocumentInterface
    {
        return new Dui();
    }

    public static function validos(): array
    {
        return [['12345678-4'], ['123456784']];
    }

    public static function invalidos(): array
    {
        return [['12345678-5'], ['1234567'], ['ABCDEFGH-4']];
    }

    public static function formatos(): array
    {
        return [
            '123456784' => '12345678-4',
        ];
    }
}
