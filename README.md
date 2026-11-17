# momotombo/stdnum-centroamerica

[![tests](https://github.com/momotombodevs/stdnum-centroamerica/actions/workflows/tests.yml/badge.svg)](https://github.com/momotombodevs/stdnum-centroamerica/actions/workflows/tests.yml)
[![Packagist](https://img.shields.io/packagist/v/momotombo/stdnum-centroamerica)](https://packagist.org/packages/momotombo/stdnum-centroamerica)
[![PHP](https://img.shields.io/packagist/dependency-v/momotombo/stdnum-centroamerica/php)](composer.json)
[![Licencia](https://img.shields.io/github/license/momotombodevs/stdnum-centroamerica)](LICENSE)

Validación, formato y compactado de documentos de identidad y fiscales de Centroamérica. Sin dependencias de producción, PHP >= 8.1.

> Validate, format and parse Central American national and tax IDs (Guatemala, El Salvador, Honduras, Nicaragua, Costa Rica, Panama) and ICAO 9303 MRZ. Docs are in Spanish; the API is the same for every document: `Registry::isValid('gt.nit', $number)`.

```
composer require momotombo/stdnum-centroamerica
```

## Uso

Cada documento tiene una clave `país.documento` (`ni.cedula`, `ni.ruc`, ...). Todos tienen `isValid()`, `validate()`, `format()` y `compact()`; los que codifican datos tienen `parse()`, y todos los incluidos tienen `generate()`.

```php
use StdNumCentroamerica\Registry;

// Validar
Registry::isValid('ni.cedula', '001-010190-0001N'); // true
Registry::isValid('ni.cedula', '001-010190-0001A'); // true: la letra no se comprueba por defecto
Registry::isValid('ni.ruc', 'J0310000000001');      // true: persona jurídica
Registry::isValid('ni.ruc', '0000300766011M');      // false: un RUC no puede iniciar con cuatro ceros

// Dar formato y compactar
Registry::make('ni.cedula')->format('0010101900001n');    // "001-010190-0001N"
Registry::make('ni.cedula')->compact('001-010190-0001n'); // "0010101900001N"
```

`validate()` devuelve además el motivo del fallo, útil para mostrar mensajes de error:

```php
use StdNumCentroamerica\ValidationError;

$result = Registry::validate('sv.dui', '12345678-5');
$result->isValid(); // false
$result->error;     // ValidationError::Checksum

Registry::validate('ni.cedula', '001-310290-0001N')->error; // ValidationError::Component: no existe el 31 de febrero
Registry::validate('gt.nit', 'CF')->error;                  // ValidationError::Format
```

| `ValidationError` | Significado |
|-------------------|-------------|
| `Format` | longitud, caracteres o estructura incorrectos |
| `Component` | un componente (fecha, departamento, municipio, prefijo) fuera de lo permitido |
| `Checksum` | el dígito o la letra de control no coincide |

La cédula no comprueba la letra por defecto, porque el CSE no publica la fórmula (módulo 23, de fuentes no oficiales). Se puede ajustar con opciones, que `Registry` pasa al constructor:

```php
// Comprobar la letra (la correcta de este número es N)
Registry::isValid('ni.cedula', '001-010190-0001A', ['strictLetter' => true]); // false

// Exigir que el código de municipio esté en la lista conocida (282 no figura en ella)
$number = '282-010190-0001A';
Registry::isValid('ni.cedula', $number);                                       // true
Registry::isValid('ni.cedula', $number, ['strictMunicipality' => true]);          // false
```

Las opciones de cada documento:

| Clave | Opción | Por defecto | Efecto |
|-------|--------|-------------|--------|
| `ni.cedula` | `strictLetter` | `false` | comprueba la letra de control (módulo 23, no oficial) |
| `ni.cedula` | `strictMunicipality` | `false` | exige un código de la lista conocida de municipios, que está incompleta |
| `ni.ruc` | `cedula` | `new Cedula()` | la `Cedula` con la que se validan los RUC de personas con cédula |
| `sv.nit` | `dui` | `new Dui()` | el `Dui` con el que se validan los NIT de 9 dígitos |

```php
use StdNumCentroamerica\Documents\Nicaragua\Cedula;

Registry::isValid('ni.ruc', '0010101900001A', ['cedula' => new Cedula(strictLetter: true)]); // false
```

Los demás documentos no aceptan opciones. Una opción que el documento no tiene (por ejemplo, `strictletter` en minúsculas) lanza `InvalidArgumentException` con la lista de las que sí acepta.

También se puede usar la clase directamente:

```php
use StdNumCentroamerica\Documents\Nicaragua\Cedula;

$cedula = new Cedula(strictMunicipality: true);
$cedula->isValid('001-010190-0001N'); // true
Cedula::checkLetter('0010101900001'); // "N"
```

```php
$mrz = "P<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<<<<<<<<<\nL898902C36UTO7408122F1204159ZE184226B<<<<<10";
Registry::isValid('mrz.td3', $mrz);       // true
Registry::parse('mrz.td3', $mrz)?->surname;  // "ERIKSSON"
```

`Registry::describe()` devuelve el país, el nombre y el alcance (`Level::Complete` o `Level::Format`) de cada documento incluido.

`Registry::parse($clave, $número)` lee los componentes que el número ya trae: devuelve un objeto de solo lectura, o `null` si el número no es válido. Está disponible en `ni.cedula`, `hn.identidad`, `gt.dpi`, `pa.cedula` y las tres MRZ (`mrz.td1`, `mrz.td2`, `mrz.td3`); en los demás lanza `InvalidArgumentException`.

```php
$cedula = Registry::parse('ni.cedula', '001-010190-0001N');
$cedula->municipalityCode;        // 1
$cedula->birthDay;                // 1
$cedula->birthMonth;              // 1
$cedula->birthYearTwoDigits;      // 90
$cedula->birthYearCandidates();   // [1990]: el número no dice el siglo, así que se ofrecen los años posibles (puede quedar vacío: el 29/02/01 no existe)

Registry::parse('hn.identidad', '0906-1990-00001')->municipalityCode; // 6
Registry::parse('gt.dpi', '1234 56789 0101')->departmentCode;          // 1
Registry::parse('pa.cedula', 'PE-5-687')->kind;                        // CedulaKind::BornAbroad
```

En Nicaragua, el municipio y la letra de control son de terceros (el CSE no los publica) y el código 888 tiene una sola fuente; en Panamá, las provincias 10 a 13 y los sufijos PI y AV tampoco son oficiales (ver [docs/fuentes.md](docs/fuentes.md)). Solo se leen códigos, no nombres: las tablas de municipios completas y respaldadas por una fuente oficial aún no están. Los componentes salen de la estructura del número y no se contrastan con ningún registro, y la fecha de nacimiento es un dato personal: trátela como tal.

`Registry::generate($clave)` produce un número sintético válido, útil para fixtures y datos de ejemplo (es aleatorio: puede coincidir por azar con un número real, así que no lo use como dato de personas):

```php
Registry::generate('gt.dpi'); // "2381590430101"
```

Una clave desconocida lanza `InvalidArgumentException`. `Registry::keys()` lista las claves disponibles y `Registry::register($clave, $clase)` agrega o reemplaza un documento (la clase debe implementar `DocumentInterface`).

## Documentos soportados

**completa** = valida formato y dígito verificador; **formato** = valida longitud, caracteres y rangos básicos.

| País | Documento | Clave | Nivel |
|------|-----------|-------|-------|
| Guatemala | NIT | `gt.nit` | completa |
| Guatemala | DPI (CUI) | `gt.dpi` | completa (verificador, departamento y municipio) |
| El Salvador | DUI | `sv.dui` | completa |
| El Salvador | NIT | `sv.nit` | completa (acepta también el DUI de 9 dígitos) |
| El Salvador | NRC | `sv.nrc` | formato |
| Honduras | Número de identidad | `hn.identidad` | formato (departamento y municipio válidos) |
| Honduras | RTN | `hn.rtn` | formato |
| Nicaragua | Cédula | `ni.cedula` | formato (fecha y municipio; letra de control solo con `strictLetter`) |
| Nicaragua | RUC | `ni.ruc` | formato |
| Costa Rica | Cédula física | `cr.cedula_fisica` | formato |
| Costa Rica | Cédula jurídica | `cr.cedula_juridica` | formato |
| Costa Rica | DIMEX | `cr.dimex` | formato |
| Costa Rica | NITE | `cr.nite` | formato |
| Panamá | Cédula | `pa.cedula` | formato |
| Panamá | RUC | `pa.ruc` | formato (no valida el DV) |
| Internacional | MRZ de pasaporte (TD3) | `mrz.td3` | completa (ICAO 9303) |
| Internacional | MRZ de cédula (TD1) | `mrz.td1` | completa (ICAO 9303) |
| Internacional | MRZ de identidad (TD2) | `mrz.td2` | completa (ICAO 9303) |

Notas:

- **Formato** no significa que el número exista: sin dígito verificador, cualquier combinación con la forma correcta pasa. `hn.rtn` acepta cualquier serie de 14 dígitos y `sv.nrc` acepta `0`. Tampoco **completa** lo garantiza: un número con el verificador correcto puede no estar asignado a nadie, y el paquete no consulta ningún registro.
- **Entrada**: se aceptan espacios y guiones, también los que llegan al copiar de un PDF o de Word (espacio no separable, espacio fino, guion largo, raya, signo menos, guion suave). Puntos, barras y guiones bajos se rechazan.
- `format()` y `compact()` no validan: con un número inválido devuelven el texto limpio en mayúsculas, sin garantía de forma (`format('abc')` da `"ABC"` en `sv.dui` y `"AB-C"` en `gt.nit`). Valide antes de formatear.
- En **Guatemala**, `CF` (consumidor final) no es un NIT y `gt.nit` lo rechaza. Si su formulario lo admite, acéptelo aparte: `$nit === 'CF' || Registry::isValid('gt.nit', $nit)`.

- Las **MRZ** (zona de lectura mecánica de pasaportes y cédulas) se reciben con o sin saltos de línea. `Registry::parse()` devuelve los datos de una MRZ válida; las fechas quedan en `AAMMDD`, porque el siglo no se puede inferir. No se valida el número de pasaporte de cada país, solo la MRZ, que es un estándar público. El paquete valida pero no guarda ni registra nada.

- En **Panamá** tomo y asiento tienen longitud variable, así que el número solo se interpreta con guiones. Use `format()` para la forma canónica (`8-926-1601`); `compact()` quita los guiones.
- En **Honduras**, `hn.identidad` acepta los códigos especiales de naturalizados (`0890`) y doble nacionalidad (`0880`).
- Las fuentes de cada regla, con la fecha de consulta, están en [docs/fuentes.md](docs/fuentes.md).

## Uso con Laravel

No hace falta un plugin: el paquete es PHP puro y se llama directamente desde un `FormRequest`, un controlador o un job. Después de `composer require`, no hay nada que registrar.

**Regla en línea**, para un caso puntual:

```php
use Closure;
use StdNumCentroamerica\Registry;

public function rules(): array
{
    return [
        'dpi' => ['required', function (string $attribute, mixed $value, Closure $fail) {
            if (! is_string($value) || ! Registry::isValid('gt.dpi', $value)) {
                $fail('El campo :attribute no es un DPI válido.');
            }
        }],
    ];
}
```

**Regla reutilizable**, con un mensaje distinto según el motivo (`app/Rules/DocumentoCentroamericano.php`, Laravel 10 o superior):

```php
namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use StdNumCentroamerica\Registry;
use StdNumCentroamerica\ValidationError;

class DocumentoCentroamericano implements ValidationRule
{
    /** @param array<string, mixed> $options opciones del documento, p. ej. ['strictLetter' => true] */
    public function __construct(private string $documento, private array $options = [])
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('El campo :attribute debe ser texto.');

            return;
        }

        $error = Registry::validate($this->documento, $value, $this->options)->error;

        if ($error !== null) {
            $fail(match ($error) {
                ValidationError::Format => 'El campo :attribute no tiene el formato de este documento.',
                ValidationError::Component => 'El campo :attribute tiene una fecha o un código que no existe.',
                ValidationError::Checksum => 'El dígito de control del campo :attribute no coincide.',
            });
        }
    }
}
```

```php
'dpi' => ['required', new DocumentoCentroamericano('gt.dpi')],
'cedula' => ['required', new DocumentoCentroamericano('ni.cedula')],
```

Laravel no ejecuta las reglas sobre valores vacíos: combínelas con `required` o `nullable`. Para que los mensajes salgan traducidos, use `lang/es/validation.php` y `trans()` en lugar de las cadenas del ejemplo.

**Guardar el número normalizado**, antes de validar:

```php
protected function prepareForValidation(): void
{
    if (is_string($this->dpi)) {
        $this->merge(['dpi' => Registry::make('gt.dpi')->compact($this->dpi)]);
    }
}
```

En Panamá use `format()` en lugar de `compact()`, porque los guiones forman parte del número.

**Cruzar la fecha de nacimiento** que escribe el usuario con la de la cédula de Nicaragua:

```php
$cedula = Registry::parse('ni.cedula', (string) $request->cedula);

$coincide = $cedula !== null
    && $cedula->birthDay === (int) $fecha->day
    && $cedula->birthMonth === (int) $fecha->month
    && in_array((int) $fecha->year, $cedula->birthYearCandidates(), true);
```

El número no trae el siglo, así que `birthYearCandidates()` devuelve los años posibles y la comprobación se hace contra ellos. Por esa razón no sirve para decidir la mayoría de edad: un nacido en 2010 también tiene el candidato 1910.

## Desarrollo

```
composer install
composer ci   # estilo (php-cs-fixer), análisis estático (phpstan nivel 8) y pruebas
```

`composer cs:fix` aplica el estilo; `composer test`, `composer analyse` y `composer cs` corren cada paso por separado.

Para agregar un documento: clase en `src/Documents/<Pais>/` que extienda `AbstractDocument` (con `PATTERN` si basta una expresión regular; las reglas adicionales van en `checkContent()`, que devuelve un `ValidationError` o `null`; sin `PATTERN`, sobrescriba `check()`; `prepare()` cambia la forma sobre la que se valida), implemente `GeneratesNumbers`, prueba en `tests/<Pais>/` que extienda `DocumentTestCase`, un `DocumentInfo` en `Registry::catalog()` y fila en la tabla de arriba y en `docs/fuentes.md`.

## Versiones

El paquete sigue [SemVer](https://semver.org/lang/es/) y está en 0.x: una versión menor (0.4 → 0.5) puede traer cambios incompatibles, que se marcan al inicio de su entrada en el [CHANGELOG](CHANGELOG.md). Cuentan como incompatibles los cambios en la API pública y también los que hacen que un número antes válido se rechace (una regla nueva, un valor por defecto más estricto). Fije la versión menor en `composer.json` (`^0.4`).

## Contribuir y seguridad

Vea [CONTRIBUTING.md](CONTRIBUTING.md) (flujo, commits y cómo citar fuentes) y [SECURITY.md](SECURITY.md).

## Licencia

MIT
