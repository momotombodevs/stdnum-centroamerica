<?php

namespace StdNumCentroamerica\Tests\CostaRica;

use StdNumCentroamerica\Contracts\DocumentInterface;
use StdNumCentroamerica\Documents\CostaRica\Nite;
use StdNumCentroamerica\Tests\DocumentTestCase;

class NiteTest extends DocumentTestCase
{
    protected function document(): DocumentInterface
    {
        return new Nite();
    }

    public static function validos(): array
    {
        return [['1234567890']];
    }

    public static function invalidos(): array
    {
        return [['123456789'], ['12345678901'], ['ABCDEFGHIJ']];
    }

    public static function formatos(): array
    {
        return [
            '1234567890' => '1234567890',
        ];
    }
}
