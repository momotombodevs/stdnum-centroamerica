<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Documents\Panama;

final class CedulaData
{
    /**
     * @param int|null $province clave de provincia (1-9 oficial; 10-13 de fuentes de terceros); null en PE y N
     * @param string|null $suffix `PI` (indígena) o `AV` (anterior al Registro Civil); fuentes de terceros
     * @param string|null $volume tomo; null en la cédula de extranjero domiciliado
     * @param string $entry asiento (en la de extranjero domiciliado, el número de orden)
     */
    public function __construct(
        public readonly CedulaKind $kind,
        public readonly ?int $province,
        public readonly ?string $suffix,
        public readonly ?string $volume,
        public readonly string $entry,
    ) {
    }
}
