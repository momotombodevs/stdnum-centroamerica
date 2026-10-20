# momotombo/stdnum-centroamerica

[![tests](https://github.com/momotombodevs/stdnum-centroamerica/actions/workflows/tests.yml/badge.svg)](https://github.com/momotombodevs/stdnum-centroamerica/actions/workflows/tests.yml)
[![Packagist](https://img.shields.io/packagist/v/momotombo/stdnum-centroamerica)](https://packagist.org/packages/momotombo/stdnum-centroamerica)
[![PHP](https://img.shields.io/packagist/dependency-v/momotombo/stdnum-centroamerica/php)](composer.json)
[![Licencia](https://img.shields.io/github/license/momotombodevs/stdnum-centroamerica)](LICENSE)

Validación, formato y compactado de documentos de identidad y fiscales de Centroamérica. Sin dependencias de producción, PHP >= 8.1.

```
composer require momotombo/stdnum-centroamerica
```

## Uso

Cada documento tiene una clave `país.documento` (`ni.cedula`, `ni.ruc`, ...) y tres operaciones: `isValid()`, `format()` y `compact()`.

```php
use StdNumCentroamerica\Registry;

// Validar
Registry::isValid('ni.cedula', '001-010190-0001N'); // true
Registry::isValid('ni.cedula', '001-010190-0001A'); // false: la letra de control es N
Registry::isValid('ni.ruc', 'J0310000000001');      // true: persona jurídica
Registry::isValid('ni.ruc', '0000300766011M');      // false: un RUC no puede iniciar con cuatro ceros

// Dar formato y compactar
Registry::make('ni.cedula')->format('0010101900001n');    // "001-010190-0001N"
Registry::make('ni.cedula')->compact('001-010190-0001n'); // "0010101900001N"
```

`validate()` devuelve además el motivo del fallo, útil para mostrar mensajes de error:

```php
use StdNumCentroamerica\ValidationError;

$result = Registry::validate('ni.cedula', '001-010190-0001A');
$result->isValid(); // false
$result->error;     // ValidationError::Checksum
```

| `ValidationError` | Significado |
|-------------------|-------------|
| `Format` | longitud, caracteres o estructura incorrectos |
| `Component` | un componente (fecha, departamento, municipio, prefijo) fuera de lo permitido |
| `Checksum` | el dígito o la letra de control no coincide |

La cédula valida la letra por defecto. Se puede ajustar con opciones, que `Registry` pasa al constructor:

```php
// Sin comprobar la letra
Registry::isValid('ni.cedula', '001-010190-0001A', ['strictLetter' => false]); // true

// Exigir que el código de municipio esté en la lista conocida (282 no figura en ella)
$number = '282-010190-0001A';
Registry::isValid('ni.cedula', $number, ['strictLetter' => false]);                                // true
Registry::isValid('ni.cedula', $number, ['strictLetter' => false, 'strictMunicipality' => true]);  // false
```

También se puede usar la clase directamente:

```php
use StdNumCentroamerica\Documents\Nicaragua\Cedula;

$cedula = new Cedula(strictMunicipality: true);
$cedula->isValid('001-010190-0001N'); // true
Cedula::checkLetter('0010101900001'); // "N"
```

`Registry::describe()` devuelve el país, el nombre y el alcance (`Level::Complete` o `Level::Format`) de cada documento incluido.

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
| Guatemala | DPI (CUI) | `gt.dpi` | completa (verificador y departamento) |
| El Salvador | DUI | `sv.dui` | completa |
| El Salvador | NIT | `sv.nit` | completa (acepta también el DUI de 9 dígitos) |
| El Salvador | NRC | `sv.nrc` | formato |
| Honduras | Número de identidad | `hn.identidad` | formato (departamento y municipio válidos) |
| Honduras | RTN | `hn.rtn` | formato |
| Nicaragua | Cédula | `ni.cedula` | completa (letra de control) |
| Nicaragua | RUC | `ni.ruc` | formato |
| Costa Rica | Cédula física | `cr.cedula_fisica` | formato |
| Costa Rica | Cédula jurídica | `cr.cedula_juridica` | formato |
| Costa Rica | DIMEX | `cr.dimex` | formato |
| Costa Rica | NITE | `cr.nite` | formato |
| Panamá | Cédula | `pa.cedula` | formato |
| Panamá | RUC | `pa.ruc` | formato (no valida el DV) |

Notas:

- En **Panamá** tomo y asiento tienen longitud variable, así que el número solo se interpreta con guiones. Use `format()` para la forma canónica (`8-926-1601`); `compact()` quita los guiones.
- En **Honduras**, `hn.identidad` acepta los códigos especiales de naturalizados (`0890`) y doble nacionalidad (`0880`).
- Las fuentes de cada regla, con la fecha de consulta, están en [docs/fuentes.md](docs/fuentes.md).

## Desarrollo

```
composer install
composer ci   # estilo (php-cs-fixer), análisis estático (phpstan nivel 8) y pruebas
```

`composer cs:fix` aplica el estilo; `composer test`, `composer analyse` y `composer cs` corren cada paso por separado.

Para agregar un documento: clase en `src/Documents/<Pais>/` que extienda `AbstractDocument` (con `PATTERN` si basta una expresión regular; las reglas adicionales van en `checkContent()`, que devuelve un `ValidationError` o `null`; sin `PATTERN`, sobrescriba `check()`; `prepare()` cambia la forma sobre la que se valida), implemente `GeneratesNumbers`, prueba en `tests/<Pais>/` que extienda `DocumentTestCase`, clave en `Registry::DOCUMENTS` y fila en la tabla de arriba y en `docs/fuentes.md`.

## Contribuir y seguridad

Vea [CONTRIBUTING.md](CONTRIBUTING.md) (flujo, commits y cómo citar fuentes) y [SECURITY.md](SECURITY.md).

## Licencia

MIT
