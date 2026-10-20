<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StdNumCentroamerica\Registry;

/** Cambiar un carácter protegido por el verificador debe invalidar el número. */
class ChecksumTest extends TestCase
{
    private const DIGITS = '0123456789';

    /**
     * El módulo 11 detecta cualquier cambio de un solo dígito (11 es primo y los pesos
     * están entre 2 y 9); el verificador es único, así que cualquier otro valor falla.
     *
     * @return array<string, array{string, string, list<int>, string}>
     */
    public static function protegidos(): array
    {
        return [
            'gt.nit, cuerpo' => ['gt.nit', '36029785', [0, 1, 2, 3, 4, 5, 6], self::DIGITS],
            'gt.nit, verificador' => ['gt.nit', '36029785', [7], self::DIGITS . 'K'],
            'gt.dpi, cuerpo' => ['gt.dpi', '1234567890101', [0, 1, 2, 3, 4, 5, 6, 7], self::DIGITS],
            'gt.dpi, verificador' => ['gt.dpi', '1234567890101', [8], self::DIGITS],
            'sv.dui, verificador' => ['sv.dui', '123456784', [8], self::DIGITS],
            'sv.nit, verificador' => ['sv.nit', '06141303861364', [13], self::DIGITS],
            'sv.nit antiguo, verificador' => ['sv.nit', '07162002610014', [13], self::DIGITS],
            'ni.cedula, letra' => ['ni.cedula', '0010101900001N', [13], 'ABCDEFGHJKLMNPQRSTUVWXY'],
        ];
    }

    /** @param list<int> $positions */
    #[DataProvider('protegidos')]
    public function test_cualquier_cambio_invalida_el_numero(string $key, string $number, array $positions, string $alphabet): void
    {
        $this->assertTrue(Registry::isValid($key, $number), 'el número base debe ser válido');

        foreach ($positions as $position) {
            foreach (str_split($alphabet) as $replacement) {
                if ($replacement === $number[$position]) {
                    continue;
                }

                $mutated = substr_replace($number, $replacement, $position, 1);
                $this->assertFalse(Registry::isValid($key, $mutated), "{$key} aceptó {$mutated}");
            }
        }
    }
}
