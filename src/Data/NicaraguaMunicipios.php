<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Data;

/**
 * Códigos de municipio de la cédula nicaragüense (3 primeros dígitos).
 *
 * Fuente: lista de terceros (gist "Validacion Cedula Nicaraguense", reproducida por
 * bnorvinjosue/cedula-nicaragua-validator); el CSE no publica la tabla. Consulta del
 * 2026-10-19. Contra la tabla oficial de INIDE (153 municipios), 149 de los 152 códigos
 * caen en el departamento correcto. Excepciones y huecos:
 * - falta Mulukukú (Costa Caribe Norte, creado en 2005);
 * - 492 figura como Wiwilí en el bloque de Nueva Segovia; corresponde a Wiwilí de Jinotega;
 * - 454 Waslala está en el bloque de Matagalpa pero es de la Costa Caribe Norte;
 * - los bloques de 40 códigos tienen huecos sin usar (p. ej. 282 en León).
 */
final class NicaraguaMunicipios
{
    public const CODES = [
        1, 2, 3, 4, 5, 6, 7, 8, 9, 41, 42, 43,
        44, 45, 46, 47, 48, 81, 82, 83, 84, 85, 86, 87,
        88, 89, 90, 91, 92, 93, 121, 122, 123, 124, 125, 126,
        127, 128, 129, 130, 161, 162, 163, 164, 165, 166, 201, 202,
        203, 204, 241, 242, 243, 244, 245, 246, 247, 281, 283, 284,
        285, 286, 287, 288, 289, 290, 291, 321, 322, 323, 324, 325,
        326, 327, 328, 329, 361, 362, 363, 364, 365, 366, 401, 402,
        403, 404, 405, 406, 407, 408, 409, 441, 442, 443, 444, 445,
        446, 447, 448, 449, 450, 451, 452, 453, 454, 481, 482, 483,
        484, 485, 486, 487, 488, 489, 490, 491, 492, 493, 521, 522,
        523, 524, 525, 526, 561, 562, 563, 564, 565, 566, 567, 568,
        569, 570, 601, 602, 603, 604, 605, 606, 607, 608, 610, 611,
        612, 615, 616, 619, 624, 626, 627, 628,
    ];

    /**
     * Código de los nacidos en el extranjero. Una sola fuente (README de
     * bnorvinjosue/cedula-nicaragua-validator, commit de abril de 2026, sin citar
     * norma); no aparece en la Ley 152 ni en el Manual del Registrador del CSE.
     */
    public const FOREIGN_CODE = 888;

    /** Los departamentos ocupan bloques de 40 códigos desde 001; el último termina en 640. */
    public const MAX_CODE = 640;
}
