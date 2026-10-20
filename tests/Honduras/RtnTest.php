<?php

namespace StdNumCentroamerica\Tests\Honduras;

use StdNumCentroamerica\Contracts\DocumentInterface;
use StdNumCentroamerica\Documents\Honduras\Rtn;
use StdNumCentroamerica\Tests\DocumentTestCase;

class RtnTest extends DocumentTestCase
{
    protected function document(): DocumentInterface
    {
        return new Rtn();
    }

    public static function validos(): array
    {
        return [['0801-1990-123456'], ['08011990123456']];
    }

    public static function invalidos(): array
    {
        return [['0801-1990-12345'], ['0801-1990-12345A']];
    }

    public static function formatos(): array
    {
        return [
            '0801-1990-123456' => '08011990123456',
        ];
    }
}
