<?php

declare(strict_types=1);

namespace StdNumCentroamerica;

use InvalidArgumentException;
use StdNumCentroamerica\Contracts\DocumentInterface;
use StdNumCentroamerica\Contracts\GeneratesNumbers;
use StdNumCentroamerica\Documents\CostaRica;
use StdNumCentroamerica\Documents\ElSalvador;
use StdNumCentroamerica\Documents\Guatemala;
use StdNumCentroamerica\Documents\Honduras;
use StdNumCentroamerica\Documents\Nicaragua;
use StdNumCentroamerica\Documents\Panama;

class Registry
{
    /**
     * Clave => [clase, país ISO 3166-1, nombre, alcance de la validación].
     *
     * @var array<string, array{class-string<DocumentInterface>, string, string, Level}>
     */
    private const DOCUMENTS = [
        'gt.nit' => [Guatemala\Nit::class, 'GT', 'NIT', Level::Complete],
        'gt.dpi' => [Guatemala\Dpi::class, 'GT', 'DPI (CUI)', Level::Complete],
        'sv.dui' => [ElSalvador\Dui::class, 'SV', 'DUI', Level::Complete],
        'sv.nit' => [ElSalvador\Nit::class, 'SV', 'NIT', Level::Complete],
        'sv.nrc' => [ElSalvador\Nrc::class, 'SV', 'NRC', Level::Format],
        'hn.identidad' => [Honduras\Identidad::class, 'HN', 'Número de identidad', Level::Format],
        'hn.rtn' => [Honduras\Rtn::class, 'HN', 'RTN', Level::Format],
        'ni.cedula' => [Nicaragua\Cedula::class, 'NI', 'Cédula', Level::Complete],
        'ni.ruc' => [Nicaragua\Ruc::class, 'NI', 'RUC', Level::Format],
        'cr.cedula_fisica' => [CostaRica\CedulaFisica::class, 'CR', 'Cédula física', Level::Format],
        'cr.cedula_juridica' => [CostaRica\CedulaJuridica::class, 'CR', 'Cédula jurídica', Level::Format],
        'cr.dimex' => [CostaRica\Dimex::class, 'CR', 'DIMEX', Level::Format],
        'cr.nite' => [CostaRica\Nite::class, 'CR', 'NITE', Level::Format],
        'pa.cedula' => [Panama\Cedula::class, 'PA', 'Cédula', Level::Format],
        'pa.ruc' => [Panama\Ruc::class, 'PA', 'RUC', Level::Format],
    ];

    /** @var array<string, class-string<DocumentInterface>> */
    private static array $custom = [];

    /** Agrega o reemplaza un documento; tiene prioridad sobre los incluidos. */
    public static function register(string $key, string $class): void
    {
        if (!is_a($class, DocumentInterface::class, true)) {
            throw new InvalidArgumentException("{$class} no implementa " . DocumentInterface::class);
        }

        self::$custom[strtolower($key)] = $class;
    }

    /** @param array<string, mixed> $options argumentos con nombre del constructor del documento */
    public static function make(string $key, array $options = []): DocumentInterface
    {
        $key = strtolower($key);
        $class = self::$custom[$key] ?? self::DOCUMENTS[$key][0]
            ?? throw new InvalidArgumentException("Documento no soportado: {$key}");

        return new $class(...$options);
    }

    /** @param array<string, mixed> $options ver {@see make()} */
    public static function validate(string $key, string $number, array $options = []): ValidationResult
    {
        return self::make($key, $options)->validate($number);
    }

    /** @param array<string, mixed> $options ver {@see make()} */
    public static function isValid(string $key, string $number, array $options = []): bool
    {
        return self::make($key, $options)->isValid($number);
    }

    /**
     * Número sintético válido para pruebas y datos de ejemplo; es aleatorio y podría coincidir por azar con uno real.
     *
     * @param array<string, mixed> $options ver {@see make()}
     */
    public static function generate(string $key, array $options = []): string
    {
        $document = self::make($key, $options);

        return $document instanceof GeneratesNumbers
            ? $document->generate()
            : throw new InvalidArgumentException('El documento ' . $key . ' no genera números');
    }

    /** @return string[] */
    public static function keys(): array
    {
        return array_keys(self::$custom + self::DOCUMENTS);
    }

    /**
     * Documentos incluidos en el paquete, con país, nombre y alcance de la validación.
     * No lista los registrados con {@see register()}.
     *
     * @return list<DocumentInfo>
     */
    public static function describe(): array
    {
        $info = [];
        foreach (self::DOCUMENTS as $key => [, $country, $name, $level]) {
            $info[] = new DocumentInfo($key, $country, $name, $level);
        }

        return $info;
    }
}
