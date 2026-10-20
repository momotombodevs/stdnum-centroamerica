# Changelog

## [Unreleased]

### Agregado
- `Registry::parse()` y la interfaz `ParsesNumbers`: leen los componentes de `ni.cedula` (municipio, fecha de nacimiento con `birthYearCandidates()`), `hn.identidad` (departamento, municipio, año de inscripción, códigos especiales), `gt.dpi` (número de registro, departamento, municipio) y `pa.cedula` (tipo, provincia, tomo, asiento).

## [0.2.0] - 2026-10-20

> Cambio incompatible: `DocumentInterface` exige `validate()`.

### Agregado
- `validate()` en `DocumentInterface`, `AbstractDocument` y `Registry`: devuelve un `ValidationResult` con el `ValidationError` (`Format`, `Component` o `Checksum`) que explica el fallo. `isValid()` queda como atajo.
- PHPStan (nivel 8) y PHP-CS-Fixer (PSR-12) con los scripts `composer analyse`, `cs`, `cs:fix` y `ci`; job `quality` en CI, que ahora también corre en `develop` y `release/**`.
- `Registry::describe()`: lista los documentos incluidos (`DocumentInfo` con clave, clase, país, nombre y `Level`); una prueba lo contrasta con la tabla del README.
- Pruebas de espacios alrededor, idempotencia de `format()` y `compact()`, entradas inválidas y mutación de caracteres protegidos por el verificador.
- `Registry::generate()` y la interfaz `GeneratesNumbers`: números sintéticos válidos para los 15 documentos incluidos.
- Job `coverage` en CI (pcov, mínimo 90 % de líneas), con `composer coverage` y `tools/check-coverage.php`.
- `CONTRIBUTING.md`, `SECURITY.md` y badges en el README.

### Cambiado
- Todos los archivos declaran `strict_types=1`.
- Las clases con reglas propias implementan `checkContent()` (o `check()`) en lugar de sobrescribir `isValid()`; `prepare()` cambia la forma que se valida (Panamá conserva los guiones). Las clases propias registradas con `Registry::register()` deben implementar `validate()`, que `AbstractDocument` ya provee.
- `compact()` usa `str_replace`, que nunca devuelve `null`.

## [0.1.1] - 2026-10-19

- Nueva publicación de 0.1.0 sin cambios de código. El tag 0.1.0 se movió después de que Packagist lo registrara y Packagist conserva la referencia anterior.

## [0.1.0] - 2026-10-19

### Agregado
- Base: `DocumentInterface`, `AbstractDocument`, `Registry`.
- Guatemala: NIT, DPI.
- El Salvador: DUI, NIT, NRC.
- Honduras: identidad, RTN.
- Nicaragua: cédula, RUC.
- Costa Rica: cédula física, cédula jurídica, DIMEX, NITE.
- Panamá: cédula (`pa.cedula`) y RUC (`pa.ruc`), solo formato.

### Fuentes (2026-10-19)
- RUC de Nicaragua acepta prefijos N, R y E (DGI).
- `Rtn::format` ya no asume agrupación; devuelve el número compacto.
- Honduras: `Identidad` valida departamento (01-18), municipio según la cantidad real por departamento (298) y año de inscripción; acepta los códigos especiales oficiales 0880 y 0890.
- Nicaragua: `Cedula` con opciones `strictLetter` y `strictMunicipality`; `Ruc` rechaza números que inician con `0000` (DGI).
- Honduras: tabla de 298 municipios verificada contra los tomos municipales del INE (Censo 2013).
- Nicaragua: lista de códigos de municipio auditada contra la tabla oficial de INIDE; la Ley 152 (art. 31) no menciona la letra.
- Nicaragua: `Cedula` acepta el código 888 (nacidos en el extranjero; fuente única, no verificado).

### Revisión de código
- `AbstractDocument`: `PATTERN`, `isValid()` y `format()` por defecto, `groupLast()` y `weightedSum()`; las clases de Costa Rica, Honduras y El Salvador se reducen a su regla.
- `Registry`: mapa constante, `register()` valida la interfaz, `make()` e `isValid()` aceptan opciones del constructor (p. ej. `strictLetter` de `ni.cedula`).
- `ni.cedula`: usa `checkdate()` en lugar de `cal_days_in_month()`, que exigía `ext-calendar` sin declararlo.
- Panamá: `compact()` vuelve a quitar los separadores; la forma con guiones es `format()` (`Cedula::normalize()`).
- El Salvador: `sv.nit` valida el dígito verificador y acepta el DUI de 9 dígitos; `sv.nrc` acepta 1 a 8 dígitos (con cero inicial).
- Costa Rica: la cédula jurídica ya no exige un primer dígito distinto de cero (Hacienda solo pide 10 dígitos).
- Tests: `DocumentTestCase` concentra las pruebas comunes.
