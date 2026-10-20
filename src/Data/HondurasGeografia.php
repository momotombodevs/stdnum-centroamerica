<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Data;

/**
 * División político-administrativa de Honduras: 18 departamentos, 298 municipios.
 * El código de 4 dígitos es departamento (2) + municipio (2, correlativo 01..n).
 *
 * Conteos contrastados el 2026-10-19 con el INE (Censo 2013, tomos municipales): las
 * listas "División política y códigos municipales" de cada departamento y la numeración
 * de los 298 tomos (p. ej. Villeda Morales 09-06, San Francisco de Opalaca 10-17).
 * Base legal del código: Ley del RNP (Decreto 62-2004), arts. 99-100.
 */
final class HondurasGeografia
{
    /** @var array<int, int> departamento => cantidad de municipios */
    public const MUNICIPIOS_POR_DEPARTAMENTO = [
        1 => 8,    // Atlántida
        2 => 10,   // Colón
        3 => 21,   // Comayagua
        4 => 23,   // Copán
        5 => 12,   // Cortés
        6 => 16,   // Choluteca
        7 => 19,   // El Paraíso
        8 => 28,   // Francisco Morazán
        9 => 6,    // Gracias a Dios
        10 => 17,  // Intibucá
        11 => 4,   // Islas de la Bahía
        12 => 19,  // La Paz
        13 => 28,  // Lempira
        14 => 16,  // Ocotepeque
        15 => 23,  // Olancho
        16 => 28,  // Santa Bárbara
        17 => 9,   // Valle
        18 => 11,  // Yoro
    ];

    /**
     * Códigos especiales oficiales (Reglamento de la Ley del RNP, Acuerdo 73):
     * 0880 doble nacionalidad (art. 153) y 0890 naturalizados (art. 150).
     * El RNP puede crear otros "en casos debidamente justificados" (Ley, art. 100).
     */
    public const SPECIAL_CODES = ['0880', '0890'];
}
