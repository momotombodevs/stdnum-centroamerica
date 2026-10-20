<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Documents\Mrz;

/**
 * Campos de una MRZ compacta, ya separados de la disposición de líneas de cada formato.
 *
 * @internal
 */
final class MrzFields
{
    /**
     * @param string $number número de documento completo, incluida la continuación si pasa de nueve caracteres
     * @param string $numberCheck su carácter de control tal como aparece
     * @param string $optional datos opcionales, sin la continuación del número ni su dígito
     * @param string|null $optionalCheck dígito de los datos opcionales (solo TD3)
     * @param string $compositeData texto que cubre el dígito compuesto, en el orden de la especificación
     */
    public function __construct(
        public readonly string $code,
        public readonly string $issuer,
        public readonly string $name,
        public readonly string $number,
        public readonly string $numberCheck,
        public readonly string $nationality,
        public readonly string $birth,
        public readonly string $birthCheck,
        public readonly string $sex,
        public readonly string $expiry,
        public readonly string $expiryCheck,
        public readonly string $optional,
        public readonly ?string $optionalCheck,
        public readonly string $compositeCheck,
        public readonly string $compositeData,
    ) {
    }
}
