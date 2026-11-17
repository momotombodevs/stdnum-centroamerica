# Changelog

## [Unreleased]

### Documentación
- `gt.dpi`: el código de los nacidos en el extranjero (0101) y de naturalizados y extranjeros domiciliados (municipio de inscripción) tiene fuente oficial, el Acuerdo de Directorio 59-2014 del RENAP, art. 5.

## [0.5.0] - 2026-11-17

> Cambio incompatible: `gt.dpi` rechaza municipios que no existen en su departamento, `Registry::make()` lanza `InvalidArgumentException` con opciones desconocidas o posicionales, y `describe()` reporta `ni.cedula` como `Level::Format`.

### Agregado
- `gt.dpi` valida el municipio contra la cantidad real de municipios de su departamento (22 departamentos, 340 municipios, `Data\GuatemalaGeografia`); antes aceptaba cualquier municipio desde 01. `generate()` solo produce municipios que existen. Con `['strictMunicipality' => false]` vuelve a aceptar cualquier municipio desde 01, por si un DPI real (por ejemplo, de un nacido en el extranjero) usa un código fuera de la tabla.
- `compact()` y la normalización de Panamá aceptan las variantes Unicode de espacios y guiones que llegan al copiar de un PDF o de Word (U+00A0 no separable, U+2007, U+2009 fino, U+202F; U+200B ancho cero, U+FEFF BOM, U+00AD guion suave; U+2010 a U+2015 y U+2212 signo menos).

### Cambiado
- `Registry::make()` (y `isValid()`, `validate()`, `parse()` y `generate()`) lanza `InvalidArgumentException` con las opciones aceptadas cuando recibe una que el documento no tiene, o una posicional; antes PHP lanzaba `Error` o la asignaba por posición. Los constructores variádicos de documentos propios siguen aceptando cualquier opción.
- `Registry::describe()` reporta `ni.cedula` como `Level::Format`: desde 0.4.0 la letra de control no se comprueba por defecto.

### Documentación
- README: el ejemplo de `validate()` usaba la cédula `...0001A`, que es válida desde 0.4.0; ahora usa un DUI con verificador incorrecto. Tabla de opciones por documento (incluida la `Cedula` estricta para `ni.ruc`), límites del nivel formato, `format()` con entradas inválidas, `CF` en Guatemala, separadores aceptados, política de versiones y resumen en inglés. Las reglas de Laravel comprueban que el valor sea texto.
- README y CONTRIBUTING citaban `Registry::DOCUMENTS`, que no existe; ahora `Registry::catalog()`.
- `composer.json`: palabras clave y descripción en inglés para las búsquedas en Packagist.

## [0.4.0] - 2026-10-20

> Cambio incompatible: `ni.cedula` ya no comprueba la letra de control por defecto.

### Cambiado
- `strictLetter` pasa de `true` a `false` en `Nicaragua\Cedula`: la fórmula de la letra (módulo 23) sale de fuentes no oficiales y puede rechazar cédulas válidas. Con el default, solo se exige que el último carácter sea una letra. Para comprobarla, use `['strictLetter' => true]`. Afecta también a `ni.ruc` cuando contiene una cédula y a `Registry::parse('ni.cedula', ...)`.

## [0.3.0] - 2026-10-20

### Agregado
- `Registry::parse()` y la interfaz `ParsesNumbers`: leen los componentes de `ni.cedula` (municipio, fecha de nacimiento con `birthYearCandidates()`), `hn.identidad` (departamento, municipio, año de inscripción, códigos especiales), `gt.dpi` (número de registro, departamento, municipio) y `pa.cedula` (tipo, provincia, tomo, asiento).
- MRZ de ICAO Doc 9303: `mrz.td3` (pasaporte, 2×44), `mrz.td1` (cédula, 3×30) y `mrz.td2` (identidad, 2×36). Validan formato, componentes (tipo, sexo, fechas, caracteres) y los dígitos de control del número, nacimiento, vencimiento, datos opcionales y compuesto; aceptan la MRZ con o sin saltos de línea. `Registry::parse()` devuelve un `MrzData` con los campos. Verifican también el dígito propio de los números de documento de más de nueve caracteres en TD1 y TD2.
- `DocumentInfo::$country` admite `ICAO` para los estándares internacionales.
- README: sección "Uso con Laravel" (regla en línea, regla reutilizable con mensajes por motivo, normalizar el número y cruzar la fecha de nacimiento), sin necesidad de un plugin.

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
