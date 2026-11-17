<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Data;

/**
 * División político-administrativa de Guatemala: 22 departamentos, 340 municipios.
 * En el CUI, los dos últimos pares de dígitos son el departamento (01-22) y el municipio
 * de nacimiento; la Ley del RENAP (Decreto 90-2005) deja los códigos al Directorio, que no
 * publica la tabla. Se asume que son los códigos geográficos del INE, como hacen las
 * implementaciones abiertas (cui-gt). Todo CUI lleva un municipio real: los nacidos en el
 * extranjero, el de Guatemala (0101); naturalizados y extranjeros domiciliados, el de su
 * inscripción (Acuerdo de Directorio 59-2014, art. 5).
 *
 * Contrastado el 2026-11-17: los códigos del INE (Censo 2018, vía City Population) van
 * del 01 al último de cada departamento sin huecos, suman 340 e incluyen Sipacate (0514,
 * 2015) y Petatán (1333, 2015). Wikipedia da los mismos conteos; cui-gt tiene 32 en
 * Huehuetenango porque omite Petatán.
 */
final class GuatemalaGeografia
{
    /** @var array<int, int> departamento => cantidad de municipios */
    public const MUNICIPIOS_POR_DEPARTAMENTO = [
        1 => 17,   // Guatemala
        2 => 8,    // El Progreso
        3 => 16,   // Sacatepéquez
        4 => 16,   // Chimaltenango
        5 => 14,   // Escuintla
        6 => 14,   // Santa Rosa
        7 => 19,   // Sololá
        8 => 8,    // Totonicapán
        9 => 24,   // Quetzaltenango
        10 => 21,  // Suchitepéquez
        11 => 9,   // Retalhuleu
        12 => 30,  // San Marcos
        13 => 33,  // Huehuetenango
        14 => 21,  // Quiché
        15 => 8,   // Baja Verapaz
        16 => 17,  // Alta Verapaz
        17 => 14,  // Petén
        18 => 5,   // Izabal
        19 => 11,  // Zacapa
        20 => 11,  // Chiquimula
        21 => 7,   // Jalapa
        22 => 17,  // Jutiapa
    ];
}
