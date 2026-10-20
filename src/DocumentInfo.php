<?php

declare(strict_types=1);

namespace StdNumCentroamerica;

use StdNumCentroamerica\Contracts\DocumentInterface;

final class DocumentInfo
{
    /**
     * @param string $key clave `país.documento`, p. ej. `gt.nit`
     * @param class-string<DocumentInterface> $class clase que implementa el documento
     * @param string $country código ISO 3166-1 alfa-2
     * @param string $name nombre del documento, p. ej. `NIT`
     */
    public function __construct(
        public readonly string $key,
        public readonly string $class,
        public readonly string $country,
        public readonly string $name,
        public readonly Level $level,
    ) {
    }
}
