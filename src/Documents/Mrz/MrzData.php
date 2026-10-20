<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Documents\Mrz;

/** Datos de una MRZ válida. Las fechas son AAMMDD tal como aparecen: el siglo no se infiere. */
final class MrzData
{
    /**
     * @param string $documentCode tipo de documento, p. ej. `P` (pasaporte) o `I` (cédula)
     * @param string $issuingState código ICAO del emisor, p. ej. `GTM`
     * @param string $nationality código ICAO de la nacionalidad
     * @param string|null $sex `M`, `F` o `X`; null si no se especifica
     * @param string|null $birthDate AAMMDD, o null si no se especifica
     * @param string|null $expiryDate AAMMDD, o null si no se especifica
     * @param string $optionalData número personal u otros datos opcionales, sin relleno
     */
    public function __construct(
        public readonly string $documentCode,
        public readonly string $issuingState,
        public readonly string $surname,
        public readonly string $givenNames,
        public readonly string $documentNumber,
        public readonly string $nationality,
        public readonly ?string $birthDate,
        public readonly ?string $sex,
        public readonly ?string $expiryDate,
        public readonly string $optionalData,
    ) {
    }
}
