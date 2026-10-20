<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Tests\Guatemala;

use StdNumCentroamerica\Contracts\DocumentInterface;
use StdNumCentroamerica\Documents\Guatemala\Dpi;
use StdNumCentroamerica\Tests\DocumentTestCase;

class DpiTest extends DocumentTestCase
{
    protected function document(): DocumentInterface
    {
        return new Dpi();
    }

    public static function validos(): array
    {
        return [['1234567890101'], ['1234 56789 0101']];
    }

    public static function invalidos(): array
    {
        return [['1234567880101'], ['1234567890001'], ['1234567892301'], ['123']];
    }

    public static function formatos(): array
    {
        return [
            '1234567890101' => '1234 56789 0101',
        ];
    }
}
