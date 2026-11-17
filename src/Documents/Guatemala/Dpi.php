<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Documents\Guatemala;

use StdNumCentroamerica\Contracts\GeneratesNumbers;
use StdNumCentroamerica\Contracts\ParsesNumbers;
use StdNumCentroamerica\Data\GuatemalaGeografia;
use StdNumCentroamerica\Documents\AbstractDocument;
use StdNumCentroamerica\ValidationError;

/**
 * DPI / CUI (RENAP): 8 dígitos + verificador + departamento (2) + municipio (2).
 * Nivel: completa en el verificador (pesos 2..9, módulo 11; fórmula de código abierto),
 * el departamento (01-22) y el municipio, dentro de la cantidad real de su departamento
 * (ver {@see GuatemalaGeografia}). Los nacidos en el extranjero llevan 0101 y los
 * naturalizados y extranjeros domiciliados el municipio donde se inscribieron (Acuerdo de
 * Directorio 59-2014, art. 5). $strictMunicipality = false acepta cualquier municipio
 * desde 01, por si la numeración del RENAP difiriera de la del INE.
 *
 * @implements ParsesNumbers<DpiData>
 */
class Dpi extends AbstractDocument implements GeneratesNumbers, ParsesNumbers
{
    protected const PATTERN = '/^\d{13}$/';

    public function __construct(private readonly bool $strictMunicipality = true)
    {
    }

    protected function checkContent(string $compact): ?ValidationError
    {
        $department = (int) substr($compact, 9, 2);
        $municipality = (int) substr($compact, 11, 2);
        $municipalities = GuatemalaGeografia::MUNICIPIOS_POR_DEPARTAMENTO[$department] ?? 0;
        $highest = $this->strictMunicipality || $municipalities === 0 ? $municipalities : 99;

        if ($municipality < 1 || $municipality > $highest) {
            return ValidationError::Component;
        }

        return self::checkDigit($compact) === (int) $compact[8] ? null : ValidationError::Checksum;
    }

    public function generate(): string
    {
        do {
            $body = self::randomDigits(8);
        } while (self::checkDigit($body) === 10);

        $department = random_int(1, count(GuatemalaGeografia::MUNICIPIOS_POR_DEPARTAMENTO));
        $municipality = random_int(1, GuatemalaGeografia::MUNICIPIOS_POR_DEPARTAMENTO[$department]);

        return $body . self::checkDigit($body) . sprintf('%02d%02d', $department, $municipality);
    }

    public function parse(string $number): ?DpiData
    {
        $compact = $this->validated($number);
        if ($compact === null) {
            return null;
        }

        return new DpiData(
            serial: substr($compact, 0, 8),
            departmentCode: (int) substr($compact, 9, 2),
            municipalityCode: (int) substr($compact, 11, 2),
        );
    }

    public function format(string $number): string
    {
        return $this->group($number, [4, 5, 4], ' ');
    }

    /** Verificador de los 8 primeros dígitos; 10 no es un dígito y nunca aparece en un CUI. */
    private static function checkDigit(string $digits): int
    {
        return self::weightedSum($digits, range(2, 9)) % 11;
    }
}
