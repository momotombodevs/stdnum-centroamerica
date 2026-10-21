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
    private const TD3 = 'P<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<<<<<<<<<L898902C36UTO7408122F1204159ZE184226B<<<<<10';
    private const TD1 = 'I<UTOD231458907<<<<<<<<<<<<<<<7408122F1204159UTO<<<<<<<<<<<6ERIKSSON<<ANNA<MARIA<<<<<<<<<<';
    private const TD2 = 'I<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<D231458907UTO7408122F1204159<<<<<<<6';

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
            // MRZ de ICAO (compactas): dígitos de nacimiento, vencimiento y los de control propios.
            'mrz.td3, nacimiento' => ['mrz.td3', self::TD3, [57, 58, 59, 60, 61, 62, 63], self::DIGITS],
            'mrz.td3, vencimiento' => ['mrz.td3', self::TD3, [65, 66, 67, 68, 69, 70, 71], self::DIGITS],
            'mrz.td3, dígitos de control' => ['mrz.td3', self::TD3, [53, 63, 71, 86, 87], self::DIGITS],
            'mrz.td1, nacimiento y vencimiento' => ['mrz.td1', self::TD1, [30, 31, 32, 33, 34, 35, 36, 38, 39, 40, 41, 42, 43, 44, 59], self::DIGITS],
            'mrz.td2, nacimiento y vencimiento' => ['mrz.td2', self::TD2, [49, 50, 51, 52, 53, 54, 55, 57, 58, 59, 60, 61, 62, 63, 71], self::DIGITS],
            'ni.cedula, letra' => ['ni.cedula', '0010101900001N', [13], 'ABCDEFGHJKLMNPQRSTUVWXY'],
        ];
    }

    /** @param list<int> $positions */
    #[DataProvider('protegidos')]
    public function test_cualquier_cambio_invalida_el_numero(string $key, string $number, array $positions, string $alphabet): void
    {
        // La letra de la cédula nicaragüense solo se comprueba en modo estricto.
        $options = $key === 'ni.cedula' ? ['strictLetter' => true] : [];

        $this->assertTrue(Registry::isValid($key, $number, $options), 'el número base debe ser válido');

        foreach ($positions as $position) {
            foreach (str_split($alphabet) as $replacement) {
                if ($replacement === $number[$position]) {
                    continue;
                }

                $mutated = substr_replace($number, $replacement, $position, 1);
                $this->assertFalse(Registry::isValid($key, $mutated, $options), "{$key} aceptó {$mutated}");
            }
        }
    }
}
