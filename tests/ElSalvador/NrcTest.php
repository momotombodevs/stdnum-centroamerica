<?php

namespace StdNumCentroamerica\Tests\ElSalvador;

use StdNumCentroamerica\Contracts\DocumentInterface;
use StdNumCentroamerica\Documents\ElSalvador\Nrc;
use StdNumCentroamerica\Tests\DocumentTestCase;

class NrcTest extends DocumentTestCase
{
    protected function document(): DocumentInterface
    {
        return new Nrc();
    }

    public static function validos(): array
    {
        return [['123456-7'], ['1234567'], ['01234567'], ['1'], ['12345678']];
    }

    public static function invalidos(): array
    {
        return [['123456789'], ['12A456'], ['ABC'], ['']];
    }

    public static function formatos(): array
    {
        return [
            '1234567' => '123456-7',
        ];
    }
}
