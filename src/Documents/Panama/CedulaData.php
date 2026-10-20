<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Documents\Panama;

/** Componentes de una cédula de Panamá, tal como aparecen en el número (ver {@see Cedula}). */
final class CedulaData
{
    /**
     * @param CedulaKind $kind tipo de cédula, según el prefijo
     * @param int|null $provinceCode clave de provincia (1-9 oficial; 10-13 de fuentes de terceros); null en PE y N
     * @param string|null $suffix `PI` (indígena) o `AV` (anterior al Registro Civil); fuentes de terceros
     * @param string|null $volume tomo; null en la cédula de extranjero domiciliado
     * @param string $entry asiento (en la de extranjero domiciliado, el número de orden)
     */
    public function __construct(
        public readonly CedulaKind $kind,
        public readonly ?int $provinceCode = null,
        public readonly ?string $suffix = null,
        public readonly ?string $volume = null,
        public readonly string $entry = '',
    ) {
    }
}
