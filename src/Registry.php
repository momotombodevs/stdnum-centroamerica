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
use StdNumCentroamerica\Documents\Mrz;
use StdNumCentroamerica\Documents\Nicaragua;
use StdNumCentroamerica\Documents\Panama;

class Registry
{
    /** @var array<string, class-string<DocumentInterface>> */
    private static array $custom = [];

    /** @var array<string, DocumentInfo>|null */
    private static ?array $catalog = null;

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
        $class = self::$custom[$key] ?? self::catalog()[$key]->class
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
        return array_keys(self::$custom + self::catalog());
    }

    /**
     * Documentos incluidos en el paquete, con país, nombre y alcance de la validación.
     * No lista los registrados con {@see register()}.
     *
     * @return list<DocumentInfo>
     */
    public static function describe(): array
    {
        return array_values(self::catalog());
    }

    /** @return array<string, DocumentInfo> los documentos incluidos, por clave */
    private static function catalog(): array
    {
        return self::$catalog ??= self::index(
            new DocumentInfo('gt.nit', Guatemala\Nit::class, 'GT', 'NIT', Level::Complete),
            new DocumentInfo('gt.dpi', Guatemala\Dpi::class, 'GT', 'DPI (CUI)', Level::Complete),
            new DocumentInfo('sv.dui', ElSalvador\Dui::class, 'SV', 'DUI', Level::Complete),
            new DocumentInfo('sv.nit', ElSalvador\Nit::class, 'SV', 'NIT', Level::Complete),
            new DocumentInfo('sv.nrc', ElSalvador\Nrc::class, 'SV', 'NRC', Level::Format),
            new DocumentInfo('hn.identidad', Honduras\Identidad::class, 'HN', 'Número de identidad', Level::Format),
            new DocumentInfo('hn.rtn', Honduras\Rtn::class, 'HN', 'RTN', Level::Format),
            new DocumentInfo('ni.cedula', Nicaragua\Cedula::class, 'NI', 'Cédula', Level::Complete),
            new DocumentInfo('ni.ruc', Nicaragua\Ruc::class, 'NI', 'RUC', Level::Format),
            new DocumentInfo('cr.cedula_fisica', CostaRica\CedulaFisica::class, 'CR', 'Cédula física', Level::Format),
            new DocumentInfo('cr.cedula_juridica', CostaRica\CedulaJuridica::class, 'CR', 'Cédula jurídica', Level::Format),
            new DocumentInfo('cr.dimex', CostaRica\Dimex::class, 'CR', 'DIMEX', Level::Format),
            new DocumentInfo('cr.nite', CostaRica\Nite::class, 'CR', 'NITE', Level::Format),
            new DocumentInfo('pa.cedula', Panama\Cedula::class, 'PA', 'Cédula', Level::Format),
            new DocumentInfo('pa.ruc', Panama\Ruc::class, 'PA', 'RUC', Level::Format),
            new DocumentInfo('mrz.td3', Mrz\Td3::class, 'ICAO', 'MRZ de pasaporte (TD3)', Level::Complete),
            new DocumentInfo('mrz.td1', Mrz\Td1::class, 'ICAO', 'MRZ de cédula (TD1)', Level::Complete),
            new DocumentInfo('mrz.td2', Mrz\Td2::class, 'ICAO', 'MRZ de identidad (TD2)', Level::Complete),
        );
    }

    /** @return array<string, DocumentInfo> */
    private static function index(DocumentInfo ...$documents): array
    {
        $index = [];
        foreach ($documents as $document) {
            $index[$document->key] = $document;
        }

        return $index;
    }
}
