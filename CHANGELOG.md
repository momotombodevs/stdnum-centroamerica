# Changelog

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
