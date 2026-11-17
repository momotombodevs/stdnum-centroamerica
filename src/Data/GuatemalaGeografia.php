<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Data;

/**
 * División político-administrativa de Guatemala: 22 departamentos, 340 municipios.
 * En el CUI, los dos últimos pares de dígitos son departamento (01-22) y municipio.
 * Que el municipio sea un correlativo 01..n dentro del departamento lo asumen las
 * implementaciones abiertas (cui-gt); RENAP no publica la tabla.
 *
 * Conteos contrastados el 2026-11-17 con la lista de municipios por departamento
 * (Wikipedia, total 340) y con cui-gt, que usa el mismo orden de departamentos pero
 * 32 en Huehuetenango: no incluye Petatán, el municipio 340, creado en 2015.
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
