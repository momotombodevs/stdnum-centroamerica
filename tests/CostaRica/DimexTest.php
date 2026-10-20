<?php

namespace StdNumCentroamerica\Tests\CostaRica;

use StdNumCentroamerica\Contracts\DocumentInterface;
use StdNumCentroamerica\Documents\CostaRica\Dimex;
use StdNumCentroamerica\Tests\DocumentTestCase;

class DimexTest extends DocumentTestCase
{
    protected function document(): DocumentInterface
    {
        return new Dimex();
    }

    public static function validos(): array
    {
        return [['155812345678'], ['15581234567']];
    }

    public static function invalidos(): array
    {
        return [['055812345678'], ['1558123456'], ['1558123456789']];
    }

    public static function formatos(): array
    {
        return [
            '155812345678' => '155812345678',
        ];
    }
}
