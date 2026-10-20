# Contribuir

## Flujo de trabajo (gitflow)

- `main`: versiones publicadas, cada una con su tag `vX.Y.Z`.
- `develop`: integración. Las ramas parten de aquí y las PR apuntan aquí.
- `feature/<tema>`: trabajo nuevo. `release/X.Y.Z` y `hotfix/X.Y.Z` solo las crean los mantenedores.

## Commits

En inglés, con [Conventional Commits](https://www.conventionalcommits.org/): `feat:`, `fix:`, `refactor:`, `test:`, `build:`, `ci:`, `docs:`, `chore:`. El asunto describe la intención en imperativo y corto; el cuerpo, solo si hace falta explicar el porqué. La documentación y los comentarios del código van en español.

## Antes de abrir la PR

```
composer install
composer ci
```

`composer ci` corre el estilo (`php-cs-fixer`), el análisis estático (`phpstan`, nivel 8) y las pruebas. `composer cs:fix` corrige el estilo. El CI repite lo mismo en PHP 8.1 a 8.5.

## Agregar o corregir una regla de validación

1. Cite la fuente. Una regla sin fuente verificable no se acepta: indique el documento oficial, o la implementación abierta y por qué es confiable. Si la regla no es oficial, dígalo en el comentario de la clase.
2. Agregue la fuente y la fecha de consulta a [docs/fuentes.md](docs/fuentes.md).
3. Las pruebas incluyen números reales o publicados por la institución (válidos) y variantes inválidas; evite números inventados que casualmente cumplen la fórmula.
4. Para un documento nuevo: clase en `src/Documents/<Pais>/` que extienda `AbstractDocument` (con `PATTERN`, y las reglas extra en `checkContent()`; si no basta un `PATTERN`, como el RUC de Panamá, sobrescriba `check()`; si el número se valida con otra forma que la compacta, como los guiones de Panamá, sobrescriba `prepare()`), implemente `GeneratesNumbers` (`generate()` debe producir números que pasen `isValid()`; `GenerateTest` lo comprueba; los rangos que elija, como años o municipios, son decisión del generador y no una regla de validación, así que no necesitan fuente), y `ParsesNumbers` si el número codifica datos (con su caso en `tests/ParseTest.php`), prueba que extienda `DocumentTestCase`, entrada en `Registry::DOCUMENTS` y fila en la tabla del README (una prueba comprueba que coinciden).
5. Anote el cambio en `CHANGELOG.md` bajo `[Unreleased]`.

## Datos personales

No incluya en pruebas, issues ni PR números de documentos de personas reales. Use los de ejemplo de las instituciones o números generados.
