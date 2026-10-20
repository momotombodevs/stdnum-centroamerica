<?php

namespace StdNumCentroamerica\Tests\Guatemala;

use StdNumCentroamerica\Contracts\DocumentInterface;
use StdNumCentroamerica\Documents\Guatemala\Nit;
use StdNumCentroamerica\Tests\DocumentTestCase;

class NitTest extends DocumentTestCase
{
    protected function document(): DocumentInterface
    {
        return new Nit();
    }

    public static function validos(): array
    {
        return [['1234567-9'], ['123456789'], ['3602978-5'], ['12345678-9']];
    }

    public static function invalidos(): array
    {
        return [['1234567-8'], ['1234567'], ['ABC'], ['']];
    }

    public static function formatos(): array
    {
        return [
            '12345679' => '1234567-9',
        ];
    }
}
