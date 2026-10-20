<?php

declare(strict_types=1);

namespace StdNumCentroamerica;

use InvalidArgumentException;
use StdNumCentroamerica\Contracts\DocumentInterface;
use StdNumCentroamerica\Documents\CostaRica;
use StdNumCentroamerica\Documents\ElSalvador;
use StdNumCentroamerica\Documents\Guatemala;
use StdNumCentroamerica\Documents\Honduras;
use StdNumCentroamerica\Documents\Nicaragua;
use StdNumCentroamerica\Documents\Panama;

class Registry
{
    private const DOCUMENTS = [
        'gt.nit' => Guatemala\Nit::class,
        'gt.dpi' => Guatemala\Dpi::class,
        'sv.dui' => ElSalvador\Dui::class,
        'sv.nit' => ElSalvador\Nit::class,
        'sv.nrc' => ElSalvador\Nrc::class,
        'hn.identidad' => Honduras\Identidad::class,
        'hn.rtn' => Honduras\Rtn::class,
        'ni.cedula' => Nicaragua\Cedula::class,
        'ni.ruc' => Nicaragua\Ruc::class,
        'cr.cedula_fisica' => CostaRica\CedulaFisica::class,
        'cr.cedula_juridica' => CostaRica\CedulaJuridica::class,
        'cr.dimex' => CostaRica\Dimex::class,
        'cr.nite' => CostaRica\Nite::class,
        'pa.cedula' => Panama\Cedula::class,
        'pa.ruc' => Panama\Ruc::class,
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
        $class = self::$custom[$key] ?? self::DOCUMENTS[$key]
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

    /** @return string[] */
    public static function keys(): array
    {
        return array_keys(self::$custom + self::DOCUMENTS);
    }
}
